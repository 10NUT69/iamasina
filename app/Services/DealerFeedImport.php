<?php

namespace App\Services;

use App\Jobs\DownloadDealerFeedPhotos;
use App\Jobs\ImportDealerFeed;
use App\Models\DealerFeedListing;
use App\Models\DealerFeedRun;
use App\Models\DealerFeedSource;
use App\Models\Service;
use App\Support\ServiceImageStorage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DealerFeedImport
{
    public function __construct(private DealerFeedSimulation $simulation) {}

    public function queue(DealerFeedSource $source, int $runId, string $fingerprint): void
    {
        DB::transaction(function () use ($source, $runId, $fingerprint) {
            $source = DealerFeedSource::query()->lockForUpdate()->findOrFail($source->id);
            if ($source->hasWorkInProgress()) {
                throw new DealerFeedException('Există deja un import în curs pentru această sursă.');
            }

            $run = $source->runs()->whereNull('import_status')->find($runId);
            if (! $run) {
                throw new DealerFeedException('Descarcă și verifică din nou feedul înainte de confirmare.');
            }
            $report = $this->confirmedPlan($source, $runId, $fingerprint);
            $run->forceFill([
                'import_status' => 'queued',
                'import_error' => null,
                'sync_summary' => $report['is_sync'] ? $report['change_counts'] : null,
            ])->save();
            Bus::dispatch((new ImportDealerFeed($source->id, $runId, $fingerprint))->onConnection('dealer_feeds'));
        });
    }

    public function prepare(int $sourceId, int $runId, string $fingerprint): void
    {
        DB::transaction(function () use ($sourceId, $runId, $fingerprint) {
            $source = DealerFeedSource::query()->lockForUpdate()->find($sourceId);
            $run = $source?->runs()->find($runId);
            if ($run?->import_status !== 'queued') {
                return;
            }

            $report = $this->confirmedPlan($source, $runId, $fingerprint, lockAds: true);
            if (! $this->hasChanges($report)) {
                $this->applyBaselines($report);
                app(DealerFeedCheck::class)->discardFile($run, $source, deleteRun: true);
                return;
            }
            $this->apply($source, $run, $report);
            app(DealerFeedCheck::class)->discardFile($run, $source);
        });
    }

    public function synchronize(DealerFeedSource $before, string $downloaded): void
    {
        DB::transaction(function () use ($before, $downloaded) {
            $source = DealerFeedSource::query()->forAutomaticSync()->lockForUpdate()->find($before->id);
            if (! $source || $source->hasWorkInProgress() || ! $source->matchesFeedSettings($before)) {
                return;
            }

            $report = $this->simulation->run($source, includePlan: true, lockAds: true, downloaded: $downloaded);
            $counts = $report['change_counts'];
            $stock = $report['tracked_stock'];
            if ($report['blocked'] || ($stock >= 4 && ($counts['missing'] + $counts['withdrawn']) * 2 > $stock)) {
                return;
            }
            if (! $this->hasChanges($report)) {
                $this->applyBaselines($report);
                return;
            }

            $run = $source->runs()->create([
                'profile' => $source->profile,
                'status' => 'verified',
                'result' => [
                    'item_count' => $report['total'],
                    'dealer_name' => $source->dealer->company_name ?: $source->dealer->name,
                    'source_name' => $source->name,
                    'automatic' => true,
                ],
            ]);
            $run->sync_summary = $counts;
            $this->apply($source, $run, $report);
        });
    }

    private function hasChanges(array $report): bool
    {
        $counts = $report['change_counts'];
        return $counts['new'] + $counts['updated'] + $counts['withdrawn'] + $counts['reactivated']
            + $counts['deactivated_missing'] + $counts['trashed_missing'] > 0;
    }

    private function applyBaselines(array $report): void
    {
        foreach ($report['plan'] as $row) {
            $row['listing']->forceFill(['photo_fingerprint' => $row['baseline']])->save();
        }
    }

    private function apply(DealerFeedSource $source, DealerFeedRun $run, array $report): void
    {
        foreach ($report['plan'] as $row) {
            $key = DealerFeedListing::externalKey($row['id']);
            $listing = $row['listing'];
            $service = $listing?->service;
            $missingTransition = in_array($row['action'], ['reactivated', 'withdrawn', 'trashed_missing'], true)
                && $listing?->awaitingMissingCleanup();
            if ($listing && (! $service || ($service->trashed() && ! $missingTransition) || $service->user_id !== $source->dealer_id)) {
                throw new DealerFeedException('Un anunț asociat s-a schimbat. Repetă simularea.');
            }
            if ($row['action'] !== 'new') {
                $this->applyExisting($listing, $service, $row, $source, $run);
                continue;
            }
            if ($service && ($service->status !== 'pending' || $service->published_at !== null)) {
                throw new DealerFeedException('Un anunț asociat a fost dezactivat. Repetă simularea.');
            }

            $service ??= new Service([
                'status' => 'pending',
                'images' => [],
                'slug' => Str::limit(Str::slug($row['attributes']['title']), 100, '').'-'.Str::lower(Str::random(12)),
            ]);
            $service->fill($row['attributes'])->forceFill(['city' => $row['attributes']['city'], 'is_active' => false])->save();
            $listing ??= $source->listings()->create([
                'service_id' => $service->id,
                'external_id' => $row['id'],
                'external_key' => $key,
            ]);
            $listing->forceFill([
                'import_run_id' => $run->id,
                'import_status' => 'waiting',
                'import_error' => null,
                'photo_fingerprint' => DealerFeedListing::photoFingerprint($row['photos']),
            ])->save();
            if ($row['photos'] === []) {
                $listing->publishImport($service);
                continue;
            }

            $this->queuePhotos($listing, $run, $source, $row['photos']);
        }
        $run->forceFill([
            'import_status' => 'prepared',
            'import_unchanged' => $report['change_counts']['unchanged'],
            'sync_summary' => $run->sync_summary !== null ? $report['change_counts'] : null,
        ])->save();
    }

    private function applyExisting(DealerFeedListing $listing, Service $service, array $row, DealerFeedSource $source, DealerFeedRun $run): void
    {
        if ($row['action'] === 'deactivated_missing') {
            $service->forceFill(['status' => Service::STATUS_DEACTIVATED, 'images' => $service->images ?? []])->save();
            $service->delete();
            $listing->forceFill(['feed_missing_since' => $service->deleted_at])->save();

            return;
        }

        if ($row['action'] === 'trashed_missing') {
            $images = $service->images;
            $service->forceFill(['status' => 'expired', 'is_active' => false, 'images' => null])->save();
            $service->delete();
            $listing->forceFill(['feed_missing_since' => null])->save();
            DB::afterCommit(fn () => ServiceImageStorage::deleteServiceImages($images));

            return;
        }

        if ($row['action'] === 'withdrawn') {
            $service->forceFill(['status' => 'expired', 'is_active' => false]);
            if ($service->trashed()) {
                $service->restore();
            } else {
                $service->save();
            }
            $listing->forceFill(['feed_withdrawn' => true, 'feed_missing_since' => null])->save();

            return;
        }

        if (in_array($row['action'], ['updated', 'reactivated'], true)) {
            $service->fill($row['attributes'])->forceFill(['city' => $row['attributes']['city']]);
            if ($row['action'] === 'reactivated') {
                $service->forceFill(['status' => 'active', 'is_active' => true]);
                $listing->feed_withdrawn = false;
                $listing->feed_missing_since = null;
                if ($service->trashed()) {
                    $service->restore();
                }
            }
            if ($service->isDirty()) {
                $service->save();
            }
        }

        if ($row['baseline'] !== null) {
            $listing->photo_fingerprint = $row['baseline'];
        }
        if ($row['gallery']) {
            $listing->sync_gallery = [
                'run_id' => $run->id,
                'status' => 'waiting',
                'fingerprint' => DealerFeedListing::photoFingerprint($row['photos']),
                'expected_images' => DealerFeedListing::photoFingerprint($service->images ?? []),
            ];
        }
        if ($listing->isDirty()) {
            $listing->save();
        }
        if ($row['gallery']) {
            $this->queuePhotos($listing, $run, $source, $row['photos'], updatesGallery: true);
        }
    }

    private function queuePhotos(DealerFeedListing $listing, DealerFeedRun $run, DealerFeedSource $source, array $photos, bool $updatesGallery = false): void
    {
        $job = new DownloadDealerFeedPhotos($listing->id, $run->id, $source->domain(), $photos);
        $job->updatesGallery = $updatesGallery;
        Bus::dispatch($job->onConnection('dealer_feeds')->onQueue('dealer-import-photos'));
    }

    private function confirmedPlan(DealerFeedSource $source, int $runId, string $fingerprint, bool $lockAds = false): array
    {
        $report = $this->simulation->run($source, includePlan: true, lockAds: $lockAds);
        if ($report['run_id'] !== $runId || $report['blocked'] || ! hash_equals($report['fingerprint'], $fingerprint)) {
            throw new DealerFeedException('Datele importului s-au schimbat sau au blocaje. Repetă simularea.');
        }

        return $report;
    }
}
