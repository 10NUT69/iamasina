<?php

namespace App\Services;

use Illuminate\Contracts\Queue\Job;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;

class DealerFeedQueuePriority
{
    public function userWorkPending(): bool
    {
        $queue = Queue::connection();
        $name = config('queue.service_images_queue');

        return $queue->pendingSize($name) > 0 || $queue->reservedSize($name) > 0;
    }

    public function popPhotos(callable $pop): ?Job
    {
        if ($this->userWorkPending()) {
            return null;
        }

        return Cache::lock('dealer-feed-photo-dequeue', 5)->get(function () use ($pop) {
            $config = config('queue.connections.dealer_feeds');
            $busy = DB::connection($config['connection'])->table($config['table'])
                ->whereIn('queue', ['dealer-import-photos', 'dealer-import-photo-processing'])
                ->where('reserved_at', '>', now()->timestamp - $config['retry_after'])->exists();
            if ($busy) {
                return null;
            }

            $queue = Queue::connection('dealer_feeds');
            // Delayed processing still takes precedence over the next download.
            return $pop($queue->size('dealer-import-photo-processing') > 0
                ? 'dealer-import-photo-processing'
                : 'dealer-import-photos');
        }) ?: null;
    }
}
