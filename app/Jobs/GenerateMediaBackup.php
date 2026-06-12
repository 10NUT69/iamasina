<?php

namespace App\Jobs;

use App\Models\BackupExport;
use App\Support\ManualMediaBackupArchiver;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class GenerateMediaBackup implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 7200;

    public int $uniqueFor = 7500;

    public bool $failOnTimeout = true;

    public function __construct(public int $backupExportId)
    {
        $this->onConnection('database_backups');
        $this->onQueue('backups');
    }

    public function uniqueId(): string
    {
        return BackupExport::ACTIVE_MEDIA_KEY;
    }

    public function handle(ManualMediaBackupArchiver $archiver): void
    {
        $export = BackupExport::query()
            ->where('type', BackupExport::TYPE_MEDIA)
            ->find($this->backupExportId);

        if (! $export || $export->status === BackupExport::STATUS_DELETED) {
            return;
        }

        $fileName = $this->filename();
        $relativePath = ManualMediaBackupArchiver::archiveRelativePath($fileName);
        $finalPath = ManualMediaBackupArchiver::resolveExportPath($relativePath);

        if (! $finalPath) {
            $this->markFailed($export, 'backup_export_path_not_allowed');

            return;
        }

        $partPath = $finalPath . '.part';

        $export->forceFill([
            'status' => BackupExport::STATUS_PROCESSING,
            'filename' => $fileName,
            'relative_path' => $relativePath,
            'size' => null,
            'started_at' => $export->started_at ?: now(),
            'completed_at' => null,
            'error_message' => null,
            'active_key' => BackupExport::ACTIVE_MEDIA_KEY,
        ])->save();

        try {
            @unlink($finalPath);
            @unlink($partPath);

            $archiver->create($partPath, $this->timeout);

            if (! is_file($partPath) || filesize($partPath) < 1) {
                throw new \RuntimeException('zip_create_failed');
            }

            if (! @rename($partPath, $finalPath)) {
                throw new \RuntimeException('zip_finalize_failed');
            }

            clearstatcache(true, $finalPath);

            $export->forceFill([
                'status' => BackupExport::STATUS_COMPLETED,
                'size' => filesize($finalPath) ?: 0,
                'completed_at' => now(),
                'error_message' => null,
                'active_key' => null,
            ])->save();

            Log::info('Manual media backup export completed.', [
                'backup_export_id' => $export->id,
                'file' => $fileName,
                'bytes' => $export->size,
            ]);
        } catch (Throwable $e) {
            @unlink($partPath);

            Log::error('Manual media backup export failed.', [
                'backup_export_id' => $export->id,
                'file' => $fileName,
                'exception' => $e,
            ]);

            $this->markFailed($export, $e->getMessage());
        }
    }

    public function failed(Throwable $exception): void
    {
        $export = BackupExport::query()
            ->where('type', BackupExport::TYPE_MEDIA)
            ->find($this->backupExportId);

        if (! $export || $export->status === BackupExport::STATUS_COMPLETED || $export->status === BackupExport::STATUS_DELETED) {
            return;
        }

        if ($export->relative_path) {
            $finalPath = ManualMediaBackupArchiver::resolveExportPath($export->relative_path);

            if ($finalPath) {
                @unlink($finalPath . '.part');
            }
        }

        Log::error('Manual media backup export job failed.', [
            'backup_export_id' => $export->id,
            'exception' => $exception,
        ]);

        $this->markFailed($export, $exception->getMessage());
    }

    private function filename(): string
    {
        return 'iaauto-media-' . now()->format('Y-m-d_H-i-s') . '.zip';
    }

    private function markFailed(BackupExport $export, string $message): void
    {
        $export->forceFill([
            'status' => BackupExport::STATUS_FAILED,
            'size' => null,
            'completed_at' => null,
            'error_message' => $this->publicErrorMessage($message),
            'active_key' => null,
        ])->save();
    }

    private function publicErrorMessage(string $message): string
    {
        return match ($message) {
            'zip_create_unavailable' => 'ZIP nu este disponibil pe server pentru generarea arhivei.',
            'zip_cli_failed' => 'Comanda zip a eșuat. Verifică logurile serverului.',
            'zip_create_failed' => 'Arhiva ZIP nu a putut fi creată.',
            'zip_add_file_failed' => 'Un fișier media nu a putut fi adăugat în arhivă.',
            'zip_finalize_failed' => 'Arhiva ZIP nu a putut fi finalizată.',
            'backup_export_path_not_allowed' => 'Calea arhivei nu este permisă.',
            default => 'Generarea arhivei media a eșuat. Verifică logurile serverului.',
        };
    }
}
