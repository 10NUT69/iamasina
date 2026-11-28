<?php

namespace App\Services;

use App\Models\DealerFeedListing;
use App\Models\DealerFeedSource;
use App\Support\ServiceImageStorage;

class DealerFeedSimulation
{
    public function __construct(
        private DealerFeedCheck $checks,
        private DealerFeedPreview $preview,
        private DealerFeedTrialImport $listings,
        private DealerFeedChanges $changes,
    ) {}

    public function run(DealerFeedSource $source, bool $includePlan = false, bool $lockAds = false, ?string $downloaded = null): array
    {
        if ($source->dealer->user_type !== 'dealer' || $source->dealer->is_active === false) {
            throw new DealerFeedException('Sursa trebuie să aparțină unui dealer activ.');
        }
        $run = $downloaded === null ? $this->checks->verifiedRun($source) : null;
        $entries = iterator_to_array($this->preview->entries(
            $downloaded ?? $this->checks->read($run), $source->mapping ?? [], $source->profile,
        ));
        $idCounts = array_count_values(array_column($entries, 'id'));
        $linked = $source->listings()->with([
            'service' => fn ($query) => $query->when($lockAds, fn ($ads) => $ads->lockForUpdate()),
            'trial:id,listing_id,status',
        ])->get()->keyBy('external_key');

        $result = [
            'run_id' => $run?->id,
            'total' => count($entries),
            'new' => 0,
            'linked' => 0,
            'blocked' => 0,
            'is_sync' => $source->runs()->where('import_status', 'prepared')->exists() || $linked->contains('import_status', 'published'),
            'changes' => [],
            'change_counts' => array_fill_keys(['new', 'updated', 'withdrawn', 'reactivated', 'unchanged', 'skipped', 'missing', 'galleries'], 0),
            'missing' => [],
            'active_stock' => $linked->filter(fn ($listing) => $listing->service?->status === 'active'
                && ! $listing->service->trashed() && $listing->service->user_id === $source->dealer_id)->count(),
            'locations' => [],
            'branches' => [],
            'issues' => [],
        ];
        $digest = hash_init('sha256');
        hash_update($digest, json_encode([
            $source->id, $run?->id, $source->url, $source->profile, $source->mapping, $source->location_overrides,
        ], JSON_THROW_ON_ERROR));
        $plan = [];
        $seen = [];

        foreach ($entries as $index => $entry) {
            $id = $entry['id'];
            $errors = match (true) {
                $id === '' => ['Lipsește ID-ul extern.'],
                mb_strlen($id) > 191 => ['ID-ul extern depășește 191 de caractere.'],
                $idCounts[$id] > 1 => ['ID-ul extern apare de mai multe ori în fișier.'],
                default => [],
            };
            $warnings = [];
            $title = $entry['title'];
            $listing = null;
            $action = null;
            if ($errors === []) {
                $key = DealerFeedListing::externalKey($id);
                $seen[$key] = true;
                $listing = $linked->get($key);
                $service = $listing?->service;
                $availability = $this->changes->availability($entry['status']);
                if ($listing && $service?->user_id !== $source->dealer_id) {
                    $errors[] = 'Anunțul asociat acestui ID nu aparține dealerului.';
                } elseif ($listing && in_array($listing->trial?->status, ['queued', 'processing'], true)) {
                    $errors[] = 'O probă pentru acest ID este încă în curs.';
                } elseif ($service?->trashed()) {
                    $action = 'skipped';
                    $warnings[] = 'Anunțul este în Coș.';
                } elseif ($service && ! ($service->status === 'active' && $service->is_active)
                    && ! ($service->status === 'pending' && $service->published_at === null)
                    && ! ($listing->feed_withdrawn && $service->status === 'expired')) {
                    $action = 'skipped';
                    $warnings[] = 'Anunțul este dezactivat sau respins.';
                } elseif ($availability === 'withdrawn') {
                    $action = $service?->status === 'active' ? 'withdrawn' : ($listing?->feed_withdrawn ? 'unchanged' : 'skipped');
                    if ($action === 'skipped') {
                        $warnings[] = 'Anunțul este indisponibil în feed.';
                    }
                } elseif ($listing?->feed_withdrawn && $availability !== 'active') {
                    $action = 'unchanged';
                }

                $attributes = [];
                $photos = [];
                $details = [];
                $gallery = false;
                $baseline = null;
                if ($action === null && $errors === []) {
                    $review = $this->listings->prepareListing($source, $entry);
                    $attributes = $review['attributes'];
                    $photos = $review['photos'];
                    $errors = $review['errors'];
                    $warnings = $review['warnings'];
                    $title = $attributes['title'];
                    $this->addLocationChoice($result, $entry, $review, $key);
                    if ($review['photo_total'] > ServiceImageStorage::MAX_IMAGES) {
                        $warnings[] = 'Se vor lua în calcul doar primele '.ServiceImageStorage::MAX_IMAGES.' de poze.';
                    }
                    $action = 'new';
                    if ($service && ($service->status === 'active' || $listing->feed_withdrawn)) {
                        ['action' => $action, 'gallery' => $gallery, 'baseline' => $baseline, 'details' => $details] =
                            $this->changes->existing($listing, $attributes, $photos);
                    }
                }
                hash_update($digest, json_encode([
                    $id, $attributes, $photos, $availability, $action, $listing?->photo_fingerprint, $listing?->feed_withdrawn,
                    $service ? array_intersect_key($service->getRawOriginal(), $attributes + array_flip(['status', 'is_active', 'deleted_at', 'published_at', 'images'])) + ['id' => $service->id] : null,
                ], JSON_THROW_ON_ERROR));
                if ($errors === []) {
                    $result['change_counts'][$action]++;
                    $result['change_counts']['galleries'] += (int) $gallery;
                    if (in_array($action, ['updated', 'withdrawn', 'reactivated'], true)) {
                        $result['changes'][] = ['action' => $action, 'id' => $id, 'title' => $title ?: $service?->title, 'details' => $details];
                    }
                    if ($includePlan && $action !== 'skipped' && ($action !== 'unchanged' || $baseline !== null)) {
                        $plan[] = compact('id', 'attributes', 'photos', 'action', 'gallery', 'baseline', 'listing');
                    }
                }
            }

            if ($errors !== []) {
                $result['blocked']++;
            } elseif ($action !== 'skipped') {
                $result[$listing ? 'linked' : 'new']++;
            }
            if ($errors !== [] || $warnings !== []) {
                $result['issues'][] = [
                    'row' => $index + 1,
                    'id' => $id,
                    'title' => $title,
                    'errors' => $errors,
                    'warnings' => $warnings,
                ];
            }
        }

        foreach ($linked as $key => $listing) {
            if (! isset($seen[$key]) && $listing->service?->status === 'active' && ! $listing->service->trashed()
                && $listing->service->user_id === $source->dealer_id && $listing->import_status === 'published') {
                $result['missing'][] = ['id' => $listing->external_id, 'title' => $listing->service->title];
            }
        }
        $result['change_counts']['missing'] = count($result['missing']);
        hash_update($digest, json_encode($result['missing'], JSON_THROW_ON_ERROR));
        $result['fingerprint'] = hash_final($digest);
        if ($includePlan) {
            $result['plan'] = $plan;
        }

        return $result;
    }

    private function addLocationChoice(array &$result, array $entry, array $review, string $key): void
    {
        if (! $review['needs_location']) {
            return;
        }
        if ($entry['branch_code'] !== '') {
            $branch = DealerFeedSource::branchLocationKey($entry['branch_code']);
            $result['branches'][$branch] ??= [
                'id' => $entry['branch_code'],
                'title' => $entry['branch_name'] ?: $entry['branch_code'],
                'county_id' => $review['branch_location']?->county_id,
                'locality_id' => $review['branch_location']?->id,
                'count' => 0,
            ];
            $result['branches'][$branch]['count']++;

            return;
        }

        $result['locations'][$key] = [
            'id' => $entry['id'],
            'title' => $review['attributes']['title'],
            'county_id' => $review['attributes']['county_id'],
            'locality_id' => $review['attributes']['locality_id'],
        ];
    }
}
