<?php

namespace App\Jobs;

use App\Models\DealerFeedListing;
use App\Services\DealerFeedPhotoDownload;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class DownloadDealerFeedPhotos implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;
    public int $timeout = 80;
    public bool $failOnTimeout = true;
    public bool $updatesGallery = false;

    public function __construct(public int $listingId, public int $runId, public string $domain, public array $photos) {}

    public function handle(DealerFeedPhotoDownload $download): void
    {
        $item = DealerFeedListing::importItem($this->listingId, $this->runId, 'waiting', $this->updatesGallery)->with('service')->first();
        if (! $item) {
            return;
        }
        $expectedStatus = $this->updatesGallery ? 'active' : 'pending';
        if (! $item->service || $item->service->trashed() || $item->service->status !== $expectedStatus) {
            $item->finishImport('failed', 'Starea anunțului nu mai permite actualizarea pozelor.', $this->updatesGallery);
            return;
        }

        [$paths, $messages] = $download->collect($this->photos, $this->domain, $this->directory());
        if (count($paths) !== count($this->photos)) {
            Storage::delete(array_values($paths));
            $item->finishImport('failed', implode(' ', $messages), $this->updatesGallery);
            return;
        }

        $queued = DB::transaction(function () use ($paths) {
            $current = DealerFeedListing::importItem($this->listingId, $this->runId, 'waiting', $this->updatesGallery)->lockForUpdate()->first();
            if (! $current) {
                return false;
            }
            $current->finishImport('processing', updatesGallery: $this->updatesGallery);
            $job = new ProcessServiceImages($current->service_id, array_values($paths), true);
            $job->feedImportContext = ['id' => $this->listingId, 'run' => $this->runId, 'gallery' => $this->updatesGallery];
            $job->tries = 40;
            $job->failOnTimeout = true;
            Bus::dispatch($job->onConnection('dealer_feeds')->onQueue('dealer-import-photo-processing'));

            return true;
        });
        if (! $queued) {
            Storage::delete(array_values($paths));
        }
    }

    public function failed(?Throwable $exception): void
    {
        $item = DealerFeedListing::importItem($this->listingId, $this->runId, 'waiting', $this->updatesGallery)->first();
        if ($item) {
            $item->finishImport('failed', 'Workerul nu a putut termina descărcarea pozelor.', $this->updatesGallery);
        }
        Storage::delete(Storage::files($this->directory()));
    }

    private function directory(): string
    {
        $directory = 'dealer-feed-imports/'.$this->runId.'/'.$this->listingId;

        return $this->updatesGallery ? $directory.'/'.DealerFeedListing::photoFingerprint($this->photos) : $directory;
    }
}
