<?php

namespace App\Jobs;

use App\Models\DealerFeedSource;
use App\Services\DealerFeedDownload;
use App\Services\DealerFeedException;
use App\Services\DealerFeedImport;
use App\Services\DealerFeedQueuePriority;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class SyncDealerFeed implements ShouldQueue, ShouldBeUnique
{
    use InteractsWithQueue, Queueable;

    public int $timeout = 180;
    public int $uniqueFor = 43200;
    public int $tries = 0;
    public int $expiresAt;

    public function __construct(public int $sourceId)
    {
        $this->expiresAt = now()->addHours(11)->timestamp;
    }

    public function uniqueId(): string
    {
        return (string) $this->sourceId;
    }

    public function handle(DealerFeedDownload $download, DealerFeedImport $import, DealerFeedQueuePriority $priority): void
    {
        if (now()->timestamp >= $this->expiresAt) {
            return;
        }
        $source = DealerFeedSource::query()->forAutomaticSync()->find($this->sourceId);
        if (! $source) {
            return;
        }
        if ($source->hasWorkInProgress() || $priority->userWorkPending()) {
            $this->release(300);
            return;
        }

        try {
            $import->synchronize($source, $download->fetch($source->url));
        } catch (DealerFeedException) {
            // Invalid or unavailable feeds leave no history or stored response.
        }
    }
}
