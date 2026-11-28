<?php

namespace App\Jobs;

use App\Models\DealerFeedSource;
use App\Services\DealerFeedCheck;
use App\Services\DealerFeedException;
use App\Services\DealerFeedImport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\DB;
use Throwable;

class ImportDealerFeed implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 180;
    public bool $failOnTimeout = true;

    public function __construct(public int $sourceId, public int $runId, public string $fingerprint) {}

    public function handle(DealerFeedImport $import): void
    {
        try {
            $import->prepare($this->sourceId, $this->runId, $this->fingerprint);
        } catch (DealerFeedException $exception) {
            $this->failed($exception);
        }
    }

    public function failed(?Throwable $exception): void
    {
        DB::transaction(function () {
            $source = DealerFeedSource::query()->lockForUpdate()->find($this->sourceId);
            $run = $source?->runs()->where('import_status', 'queued')->find($this->runId);
            if ($run) {
                app(DealerFeedCheck::class)->discardFile($run, $source, deleteRun: true);
            }
        });
    }
}
