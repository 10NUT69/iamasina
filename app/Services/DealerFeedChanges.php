<?php

namespace App\Services;

use App\Models\DealerFeedListing;
use Illuminate\Support\Str;

class DealerFeedChanges
{
    public function availability(string $value): ?string
    {
        $key = preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($value)));
        foreach (config('dealer-feeds.listing_statuses') as $status => $aliases) {
            if (in_array($key, $aliases, true)) {
                return $status;
            }
        }

        return null;
    }

    public function existing(DealerFeedListing $listing, array $attributes, array $photos): array
    {
        $service = $listing->service;
        $fingerprint = DealerFeedListing::photoFingerprint($photos);
        $newGallery = $photos !== [] && ($listing->photo_fingerprint !== null
            ? $listing->photo_fingerprint !== $fingerprint
            : empty($service->images));
        $baseline = $listing->photo_fingerprint === null && ! $newGallery && $photos !== [] ? $fingerprint : null;
        $candidate = clone $service;
        $candidate->fill($attributes)->forceFill(['city' => $attributes['city']]);
        $dirty = $candidate->getDirty();
        $action = $listing->feed_withdrawn || $listing->awaitingMissingCleanup()
            ? 'reactivated' : ($dirty || $newGallery ? 'updated' : 'unchanged');
        $details = [];
        if (array_key_exists('price_value', $dirty) || array_key_exists('currency', $dirty)) {
            $price = fn ($value, $currency) => $value === null ? 'La cerere' : $value.' '.$currency;
            $details[] = 'Preț '.$price($service->price_value, $service->currency).' → '.$price($candidate->price_value, $candidate->currency);
            unset($dirty['price_value'], $dirty['currency']);
        }
        if ($dirty) {
            $details[] = 'Datele anunțului se actualizează.';
        }
        if ($newGallery) {
            $details[] = 'Se schimbă galeria.';
        }

        return ['action' => $action, 'gallery' => $newGallery, 'baseline' => $baseline, 'details' => $details];
    }
}
