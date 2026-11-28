<?php

namespace App\Jobs;

use App\Models\DealerFeedTrial;
use App\Services\DealerFeedPhotoDownload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadDealerFeedTrialPhotos implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 80;
    public bool $failOnTimeout = true;

    public function __construct(public int $trialId, public string $runId, public string $feedDomain, public array $photos)
    {
    }

    public function handle(DealerFeedPhotoDownload $download): void
    {
        $trial = DealerFeedTrial::currentRun($this->trialId, $this->runId);
        if (! $trial || $trial->status !== 'queued') {
            return;
        }

        $messages = $trial->messages ?? [];
        $service = $trial->listing->service;
        if (! $service || $service->trashed() || $service->status !== 'pending') {
            $trial->finish(0, [...$messages, 'Anunțul de probă nu mai este în așteptare.'], 'failed');
            return;
        }

        [$paths, $downloadMessages] = $download->collect($this->photos, $this->feedDomain, 'dealer-feed-trials/'.$this->runId);
        $messages = [...$messages, ...$downloadMessages];

        if ($paths === []) {
            $trial->finish(0, $messages, 'failed');
            return;
        }

        $trial->finish(0, $messages, 'processing');
        $job = new ProcessServiceImages($service->id, array_values($paths), true);
        $job->feedTrialContext = ['id' => $trial->id, 'run' => $this->runId, 'positions' => array_keys($paths)];
        Bus::dispatch($job->onQueue($this->queue));
    }

    public function failed(?Throwable $exception): void
    {
        $trial = DealerFeedTrial::currentRun($this->trialId, $this->runId);
        if ($trial) {
            $trial->finish(0, [...($trial->messages ?? []), 'Jobul de descărcare nu a putut termina proba.'], 'failed');
            Storage::delete(Storage::files('dealer-feed-trials/'.$this->runId));
        }
    }
}
