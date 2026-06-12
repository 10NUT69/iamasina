<?php

namespace Tests\Feature;

use App\Jobs\GenerateMediaBackup;
use App\Models\BackupExport;
use App\Models\User;
use App\Support\ManualMediaBackupArchiver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Str;
use RuntimeException;
use Tests\TestCase;

class AdminMediaBackupExportTest extends TestCase
{
    use RefreshDatabase;

    private string $exportDirectory;

    protected function setUp(): void
    {
        parent::setUp();

        $root = storage_path('framework/testing/media-backups/' . Str::random(12));
        $this->exportDirectory = $root . DIRECTORY_SEPARATOR . 'exports';

        config()->set('backups.force_media_export_available', true);
        config()->set('backups.media_export_directory', $this->exportDirectory);
        config()->set('backups.media_directories', [
            'services' => $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'services',
            'dealers' => $root . DIRECTORY_SEPARATOR . 'public' . DIRECTORY_SEPARATOR . 'dealers',
        ]);
    }

    protected function tearDown(): void
    {
        if (isset($this->exportDirectory)) {
            File::deleteDirectory(dirname($this->exportDirectory));
        }

        parent::tearDown();
    }

    public function test_admin_can_start_media_export_and_dispatches_job(): void
    {
        Queue::fake();

        $response = $this->actingAs($this->adminUser())
            ->post(route('admin.backups.media.export'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $export = BackupExport::query()->first();

        $this->assertNotNull($export);
        $this->assertSame(BackupExport::TYPE_MEDIA, $export->type);
        $this->assertSame(BackupExport::STATUS_PENDING, $export->status);
        $this->assertSame(BackupExport::ACTIVE_MEDIA_KEY, $export->active_key);
        $this->assertNotNull($export->started_at);

        Queue::assertPushed(GenerateMediaBackup::class, function (GenerateMediaBackup $job) use ($export): bool {
            return $job->backupExportId === $export->id
                && $job->connection === 'database_backups'
                && $job->queue === 'backups';
        });
    }

    public function test_second_active_media_export_is_refused(): void
    {
        Queue::fake();

        BackupExport::create([
            'type' => BackupExport::TYPE_MEDIA,
            'status' => BackupExport::STATUS_PROCESSING,
            'started_at' => now(),
            'active_key' => BackupExport::ACTIVE_MEDIA_KEY,
        ]);

        $response = $this->actingAs($this->adminUser())
            ->post(route('admin.backups.media.export'));

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertSame(1, BackupExport::query()->count());
        Queue::assertNotPushed(GenerateMediaBackup::class);
    }

    public function test_backup_page_displays_pending_status(): void
    {
        BackupExport::create([
            'type' => BackupExport::TYPE_MEDIA,
            'status' => BackupExport::STATUS_PENDING,
            'started_at' => Carbon::parse('2026-06-12 18:30:00'),
            'active_key' => BackupExport::ACTIVE_MEDIA_KEY,
        ]);

        $this->actingAs($this->adminUser())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Arhiva media se generează în fundal.')
            ->assertSee('Actualizează starea')
            ->assertDontSee('Generează arhiva media');
    }

    public function test_backup_page_displays_completed_archive_details(): void
    {
        $export = $this->completedExport('iaauto-media-test.zip', 'zip-content');

        $this->actingAs($this->adminUser())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Arhiva media este disponibilă.')
            ->assertSee($export->filename)
            ->assertSee('Descarcă arhiva')
            ->assertSee('Șterge arhiva');
    }

    public function test_backup_page_displays_failed_status_and_retry_button(): void
    {
        BackupExport::create([
            'type' => BackupExport::TYPE_MEDIA,
            'status' => BackupExport::STATUS_FAILED,
            'error_message' => 'Arhiva ZIP nu a putut fi creată.',
        ]);

        $this->actingAs($this->adminUser())
            ->get(route('admin.backups.index'))
            ->assertOk()
            ->assertSee('Generarea arhivei media a eșuat.')
            ->assertSee('Arhiva ZIP nu a putut fi creată.')
            ->assertSee('Încearcă din nou');
    }

    public function test_completed_archive_can_be_downloaded(): void
    {
        $export = $this->completedExport('iaauto-media-download.zip', 'zip-content');

        $this->actingAs($this->adminUser())
            ->get(route('admin.backups.media.download', $export))
            ->assertOk()
            ->assertDownload($export->filename);
    }

    public function test_incomplete_archive_cannot_be_downloaded(): void
    {
        $export = BackupExport::create([
            'type' => BackupExport::TYPE_MEDIA,
            'status' => BackupExport::STATUS_PROCESSING,
            'filename' => 'iaauto-media-processing.zip',
            'relative_path' => ManualMediaBackupArchiver::archiveRelativePath('iaauto-media-processing.zip'),
            'started_at' => now(),
            'active_key' => BackupExport::ACTIVE_MEDIA_KEY,
        ]);

        File::put(ManualMediaBackupArchiver::exportDirectory() . DIRECTORY_SEPARATOR . $export->filename, 'zip-content');

        $this->actingAs($this->adminUser())
            ->get(route('admin.backups.media.download', $export))
            ->assertNotFound();
    }

    public function test_completed_archive_can_be_deleted(): void
    {
        $export = $this->completedExport('iaauto-media-delete.zip', 'zip-content');
        $path = ManualMediaBackupArchiver::resolveExportPath($export->relative_path);

        $this->actingAs($this->adminUser())
            ->delete(route('admin.backups.media.destroy', $export))
            ->assertRedirect()
            ->assertSessionHas('success');

        $this->assertFileDoesNotExist($path);

        $export->refresh();
        $this->assertSame(BackupExport::STATUS_DELETED, $export->status);
        $this->assertNull($export->filename);
        $this->assertNull($export->relative_path);
        $this->assertNull($export->size);
    }

    public function test_job_failure_removes_part_file_and_marks_export_failed(): void
    {
        $export = BackupExport::create([
            'type' => BackupExport::TYPE_MEDIA,
            'status' => BackupExport::STATUS_PENDING,
            'started_at' => now(),
            'active_key' => BackupExport::ACTIVE_MEDIA_KEY,
        ]);

        $archiver = new class extends ManualMediaBackupArchiver {
            public function create(string $zipPath, int $timeoutSeconds = 7200): void
            {
                File::ensureDirectoryExists(dirname($zipPath));
                File::put($zipPath, 'partial');

                throw new RuntimeException('zip_add_file_failed');
            }
        };

        (new GenerateMediaBackup($export->id))->handle($archiver);

        $export->refresh();
        $partPath = ManualMediaBackupArchiver::resolveExportPath($export->relative_path) . '.part';

        $this->assertSame(BackupExport::STATUS_FAILED, $export->status);
        $this->assertNull($export->active_key);
        $this->assertSame('Un fișier media nu a putut fi adăugat în arhivă.', $export->error_message);
        $this->assertFileDoesNotExist($partPath);
    }

    public function test_cleanup_command_removes_expired_archives_and_stale_part_files(): void
    {
        $expiredExport = $this->completedExport(
            'iaauto-media-expired.zip',
            'zip-content',
            Carbon::now()->subHours(25),
        );

        $partPath = ManualMediaBackupArchiver::exportDirectory() . DIRECTORY_SEPARATOR . 'iaauto-media-stale.zip.part';
        File::put($partPath, 'partial');
        touch($partPath, now()->subHours(7)->getTimestamp());

        $this->artisan('backups:cleanup-media-exports')
            ->assertExitCode(0);

        $this->assertFileDoesNotExist($partPath);
        $this->assertFileDoesNotExist(ManualMediaBackupArchiver::resolveExportPath($expiredExport->relative_path));

        $expiredExport->refresh();
        $this->assertSame(BackupExport::STATUS_DELETED, $expiredExport->status);
        $this->assertNull($expiredExport->relative_path);
    }

    public function test_only_admins_can_access_media_backup_export(): void
    {
        Queue::fake();

        $this->actingAs(User::factory()->create(['is_admin' => false]))
            ->post(route('admin.backups.media.export'))
            ->assertNotFound();

        $this->assertSame(0, BackupExport::query()->count());
        Queue::assertNotPushed(GenerateMediaBackup::class);
    }

    private function adminUser(): User
    {
        return User::factory()->create([
            'is_admin' => true,
            'is_active' => true,
        ]);
    }

    private function completedExport(
        string $filename,
        string $contents,
        ?Carbon $completedAt = null,
    ): BackupExport {
        File::ensureDirectoryExists(ManualMediaBackupArchiver::exportDirectory());
        File::put(ManualMediaBackupArchiver::exportDirectory() . DIRECTORY_SEPARATOR . $filename, $contents);

        return BackupExport::create([
            'type' => BackupExport::TYPE_MEDIA,
            'status' => BackupExport::STATUS_COMPLETED,
            'filename' => $filename,
            'relative_path' => ManualMediaBackupArchiver::archiveRelativePath($filename),
            'size' => strlen($contents),
            'started_at' => ($completedAt ?: now())->copy()->subMinute(),
            'completed_at' => $completedAt ?: now(),
        ]);
    }
}
