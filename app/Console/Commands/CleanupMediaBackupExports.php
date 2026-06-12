<?php

namespace App\Console\Commands;

use App\Models\BackupExport;
use App\Support\ManualMediaBackupArchiver;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class CleanupMediaBackupExports extends Command
{
    protected $signature = 'backups:cleanup-media-exports {--dry-run : Afiseaza ce s-ar sterge, fara modificari.}';

    protected $description = 'Sterge arhivele manuale media expirate si fisierele partiale ramase.';

    public function handle(): int
    {
        $dryRun = (bool) $this->option('dry-run');
        $stats = [
            'completed_deleted' => 0,
            'active_failed' => 0,
            'parts_deleted' => 0,
        ];

        $this->deleteExpiredCompletedExports($dryRun, $stats);
        $this->failStaleActiveExports($dryRun, $stats);
        $this->deleteStalePartFiles($dryRun, $stats);

        $this->info(sprintf(
            'Media backup cleanup%s: %d completed deleted, %d stale active marked failed, %d part files deleted.',
            $dryRun ? ' DRY RUN' : '',
            $stats['completed_deleted'],
            $stats['active_failed'],
            $stats['parts_deleted'],
        ));

        return self::SUCCESS;
    }

    private function deleteExpiredCompletedExports(bool $dryRun, array &$stats): void
    {
        BackupExport::query()
            ->media()
            ->where('status', BackupExport::STATUS_COMPLETED)
            ->whereNotNull('completed_at')
            ->where('completed_at', '<', now()->subHours(24))
            ->chunkById(100, function ($exports) use ($dryRun, &$stats): void {
                foreach ($exports as $export) {
                    $path = $export->relative_path
                        ? ManualMediaBackupArchiver::resolveExportPath($export->relative_path)
                        : null;

                    if (! $dryRun && $path && is_file($path)) {
                        @unlink($path);
                    }

                    if (! $dryRun) {
                        $export->forceFill([
                            'status' => BackupExport::STATUS_DELETED,
                            'filename' => null,
                            'relative_path' => null,
                            'size' => null,
                            'active_key' => null,
                            'error_message' => null,
                        ])->save();
                    }

                    $stats['completed_deleted']++;
                }
            });
    }

    private function failStaleActiveExports(bool $dryRun, array &$stats): void
    {
        $cutoff = now()->subHours(6);

        BackupExport::query()
            ->media()
            ->active()
            ->where(function ($query) use ($cutoff): void {
                $query->where('started_at', '<', $cutoff)
                    ->orWhere(function ($query) use ($cutoff): void {
                        $query->whereNull('started_at')
                            ->where('updated_at', '<', $cutoff);
                    });
            })
            ->chunkById(100, function ($exports) use ($dryRun, &$stats): void {
                foreach ($exports as $export) {
                    if ($export->relative_path) {
                        $path = ManualMediaBackupArchiver::resolveExportPath($export->relative_path);

                        if (! $dryRun && $path && is_file($path . '.part')) {
                            @unlink($path . '.part');
                        }
                    }

                    if (! $dryRun) {
                        $export->forceFill([
                            'status' => BackupExport::STATUS_FAILED,
                            'size' => null,
                            'completed_at' => null,
                            'error_message' => 'Generarea arhivei media a expirat si a fost oprita de curatarea automata.',
                            'active_key' => null,
                        ])->save();
                    }

                    $stats['active_failed']++;
                }
            });
    }

    private function deleteStalePartFiles(bool $dryRun, array &$stats): void
    {
        $directory = ManualMediaBackupArchiver::exportDirectory();
        $cutoffTimestamp = now()->subHours(6)->getTimestamp();

        foreach (File::files($directory) as $file) {
            if (! str_ends_with($file->getFilename(), '.zip.part') || $file->getMTime() >= $cutoffTimestamp) {
                continue;
            }

            if (! $dryRun) {
                @unlink($file->getPathname());
            }

            $stats['parts_deleted']++;
        }
    }
}
