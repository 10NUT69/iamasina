<?php

use App\Jobs\FetchBnrExchangeRate;
use App\Jobs\SyncDealerFeed;
use App\Models\DealerFeedSource;
use App\Services\DealerFeedCheck;
use App\Services\DealerFeedQueuePriority;
use App\Services\TemporaryServiceImages;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Queue\Worker;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::command('services:send-missing-image-reminders')
    ->dailyAt('08:00')
    ->timezone(config('app.timezone', 'Europe/Bucharest'))
    ->withoutOverlapping(60);

Schedule::command('backups:cleanup-media-exports')
    ->hourly()
    ->timezone(config('app.timezone', 'Europe/Bucharest'))
    ->withoutOverlapping(60);

Artisan::command('services:cleanup-temporary-images', function (TemporaryServiceImages $images) {
    $images->cleanup();
})->purpose('Remove expired temporary listing image uploads');

Schedule::command('services:cleanup-temporary-images')->hourly()->withoutOverlapping(60);

Schedule::job(new FetchBnrExchangeRate())
    ->dailyAt('14:00')
    ->timezone(config('app.timezone', 'Europe/Bucharest'))
    ->withoutOverlapping(120);

Artisan::command('dealer-feeds:sync', function (DealerFeedCheck $checks) {
    $checks->cleanup();
    $delay = 0;
    foreach (DealerFeedSource::query()->forAutomaticSync()->select('id')->lazyById(100) as $source) {
        dispatch((new SyncDealerFeed($source->id))->onConnection('dealer_feeds')->delay($delay));
        $delay += 10;
    }
})->purpose('Queue periodic dealer feed synchronization');

Schedule::command('dealer-feeds:sync')
    ->cron('0 */12 * * *')
    ->timezone(config('app.timezone', 'Europe/Bucharest'))
    ->onOneServer()
    ->withoutOverlapping(10);

Worker::popUsing('dealer-feeds-photos', fn ($pop) => app(DealerFeedQueuePriority::class)->popPhotos($pop));
