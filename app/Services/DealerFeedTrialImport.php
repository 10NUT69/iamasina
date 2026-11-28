<?php

namespace App\Services;

use App\Jobs\DownloadDealerFeedTrialPhotos;
use App\Models\CarBrand;
use App\Models\Caroserie;
use App\Models\Category;
use App\Models\Combustibil;
use App\Models\County;
use App\Models\CutieViteze;
use App\Models\DealerFeedListing;
use App\Models\DealerFeedSource;
use App\Models\DealerFeedTrial;
use App\Models\Locality;
use App\Models\Service;
use App\Models\Tractiune;
use App\Support\ServiceImageStorage;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

class DealerFeedTrialImport
{
    public function __construct(private DealerFeedCheck $checks, private DealerFeedPreview $preview)
    {
    }

    public function review(DealerFeedSource $source, string $externalId, ?int $localityId = null): array
    {
        $run = $this->checks->verifiedRun($source);
        $entries = iterator_to_array($this->preview->entries($this->checks->read($run), $source->mapping ?? [], $source->profile));
        $matches = array_values(array_filter($entries, fn ($row) => $row['id'] === $externalId));
        if (count($matches) !== 1) {
            throw new DealerFeedException($matches === []
                ? 'ID-ul căutat nu apare în feed.'
                : 'ID-ul căutat apare de mai multe ori în feed.');
        }
        $entry = $matches[0];

        $review = $this->prepareListing($source, $entry, $localityId);

        return $review + [
            'run_id' => $run->id,
            'fingerprint' => hash('sha256', json_encode([$source->id, $run->id, $source->profile, $review['attributes'], $entry], JSON_THROW_ON_ERROR)),
        ];
    }

    public function prepareListing(
        DealerFeedSource $source,
        array $entry,
        ?int $localityId = null,
    ): array
    {
        $dealer = $source->dealer;
        $category = once(fn () => Category::query()->where('slug', 'autoturisme')->first());
        [$county, $locality] = $this->mappedLocation($entry);
        $needsLocation = ! $county || ! $locality;
        $branchLocation = null;
        if ($needsLocation) {
            $branch = $entry['branch_code'] ?? '';
            if ($branch !== '') {
                $branchLocation = $this->selectedLocation($source->location_overrides[DealerFeedSource::branchLocationKey($branch)] ?? null)
                    ?? $this->configuredBranchLocation($source->domain(), $branch);
            }
            $choice = $localityId ?? ($source->location_overrides[DealerFeedListing::externalKey($entry['id'])] ?? null);
            $selected = $this->selectedLocation($choice) ?? $branchLocation;
            if ($selected?->county) {
                $county = $selected->county;
                $locality = $selected;
            }
        }
        $brands = once(fn () => CarBrand::query()->get(['id', 'name', 'slug']));
        $brand = $this->match($brands, $entry['brand'], 'name');
        if (! $brand) {
            $alias = config('dealer-feeds.brand_aliases.'.$this->key($entry['brand']), '');
            $brand = $this->match($brands, $alias, 'name');
        }
        $models = $brand ? once(fn () => $brand->models()->get(['id', 'name', 'slug'])) : [];
        $model = $this->match($models, $entry['model'], 'name');
        if (! $model && $brand) {
            $aliases = config('dealer-feeds.model_aliases.'.$this->key($brand->name), []);
            $model = $this->match($models, $aliases[$this->key($entry['model'])] ?? '', 'name');
        }
        $title = $this->plainText($entry['title']);
        $description = $this->plainText($entry['description']);
        $year = $this->integer($entry['year'], 1886, now()->year + 1);
        $warnings = [];
        $errors = [];

        if ($dealer->user_type !== 'dealer' || $dealer->is_active === false) {
            $errors[] = 'Sursa trebuie să aparțină unui dealer activ.';
        }
        if (! $county || ! $locality) {
            $errors[] = 'Alege județul și localitatea.';
        }
        if (! $dealer->phone) {
            $errors[] = 'Completează telefonul în profilul dealerului.';
        }
        if (! $category) {
            $errors[] = 'Categoria Autoturisme nu există în iaAuto.';
        }
        if (! $brand || ! $model) {
            $errors[] = 'Marca și modelul din feed nu au o asociere unică în catalogul iaAuto.';
        }
        if ($year === null) {
            $errors[] = 'Anul de fabricație valid este obligatoriu.';
        }
        $power = $this->integer(preg_replace('/\s*(?:cp|hp)$/i', '', $entry['power']), 1, 10000);
        if ($brand && $model) {
            $vehicle = $brand->name.' '.$model->name;
            $powerLabel = $power === null ? null : $power.' CP';
            if ($title === '') {
                $title = implode(' · ', array_filter([$vehicle, $powerLabel, $county?->name]));
                $warnings[] = 'Titlul a fost generat din datele disponibile.';
            }
            if ($description === '') {
                $description = $vehicle.($powerLabel ? ' cu '.$powerLabel : '').($county ? ', în zona '.$county->name : '').'.';
                $warnings[] = 'Descrierea a fost generată din datele disponibile.';
            }
        }
        if (mb_strlen($title) > 90 || mb_strlen($description) > 10000) {
            $warnings[] = 'Titlul sau descrierea va fi scurtată la limita iaAuto.';
        }

        $photos = $this->preview->photoUrls($entry['photos'], $source->profile);
        $price = str_replace(',', '.', $entry['price_eur']);
        $priceValue = preg_match('/\A\d{1,8}(?:\.\d{1,2})?\z/', $price) ? $price : null;
        $rawPriceType = trim($entry['price_type'] ?? '');
        $priceType = match (strtolower(Str::ascii($rawPriceType))) {
            'fixed', 'fix', 'pret fix', 'fixed price' => 'fixed',
            '', 'negotiable', 'negociabil', 'pret negociabil' => 'negotiable',
            default => null,
        };
        if ($priceType === null) {
            $warnings[] = 'Tipul prețului din feed nu este recunoscut. Se va folosi Negociabil.';
            $priceType = 'negotiable';
        }
        $mileage = $this->integer($entry['mileage'], 0, 9999999);
        $capacity = $this->integer($entry['engine_cc'], 100, 10000);
        foreach ([
            'Prețul' => [$entry['price_eur'], $priceValue],
            'Kilometrajul' => [$entry['mileage'], $mileage],
            'Cilindreea' => [$entry['engine_cc'], $capacity],
            'Puterea' => [$entry['power'], $power],
        ] as $label => [$original, $normalized]) {
            if ($original !== '' && $normalized === null) {
                $warnings[] = $label.' are o valoare nevalidă și va rămâne necompletat.';
            }
        }
        $currency = strtoupper($entry['currency']);
        if (! in_array($currency, ['EUR', 'RON'], true)) {
            if ($priceValue !== null) {
                $warnings[] = 'Moneda prețului nu a putut fi confirmată.';
            }
            $priceValue = null;
            $currency = 'EUR';
        }

        $attributes = [
            'user_id' => $dealer->id,
            'category_id' => $category?->id,
            'county_id' => $county?->id,
            'locality_id' => $locality?->id,
            'city' => $locality?->name,
            'latitude' => $locality?->latitude,
            'longitude' => $locality?->longitude,
            'phone' => $dealer->phone,
            'title' => mb_substr($title, 0, 90),
            'description' => mb_substr($description, 0, 10000),
            'brand_id' => $brand?->id,
            'model_id' => $model?->id,
            'an_fabricatie' => $year,
            'km' => $mileage,
            'price_value' => $priceValue,
            'price_type' => $priceType,
            'currency' => $currency,
            'capacitate_cilindrica' => $capacity,
            'putere' => $power,
        ];
        $lookup = [
            'body' => ['caroserie_id', Caroserie::class],
            'fuel' => ['combustibil_id', Combustibil::class],
            'transmission' => ['cutie_viteze_id', CutieViteze::class],
            'drive' => ['tractiune_id', Tractiune::class],
        ];
        $catalog = ['brand' => $brand?->name, 'model' => $model?->name, 'county' => $county?->name, 'locality' => $locality?->name];
        foreach ($lookup as $field => [$column, $class]) {
            $value = $entry[$field];
            $aliases = config('dealer-feeds.value_aliases.'.$field, []);
            $match = $value === '' ? null : $this->match(once(fn () => $class::all(['id', 'nume'])), $aliases[$this->key($value)] ?? $value, 'nume');
            $catalog[$field] = $match?->nume;
            $attributes[$column] = $match?->id;
            if ($value !== '' && ! $match) {
                $warnings[] = DealerFeedPreview::FIELDS[$field].' nu are o asociere unică și va rămâne necompletată.';
            }
        }

        return [
            'values' => $entry,
            'catalog' => $catalog,
            'needs_location' => $needsLocation,
            'branch_location' => $branchLocation,
            'attributes' => $attributes,
            'warnings' => $warnings,
            'errors' => $errors,
            'locality' => $locality?->name,
            'photo_total' => count($photos),
            'photos' => array_slice($photos, 0, ServiceImageStorage::MAX_IMAGES),
        ];
    }

    private function selectedLocation(?int $id): ?Locality
    {
        return $id ? once(fn () => Locality::query()->cities()->whereHas('county')->with('county')->find($id)) : null;
    }

    private function configuredBranchLocation(string $domain, string $branch): ?Locality
    {
        $location = config('dealer-feeds.branch_locations', [])[$domain][$branch] ?? null;
        if (! $location) {
            return null;
        }

        return once(fn () => Locality::query()->cities()->with('county')
            ->where('slug', $location['locality'])
            ->whereHas('county', fn ($county) => $county->where('slug', $location['county']))
            ->first());
    }

    private function mappedLocation(array $entry): array
    {
        $counties = once(fn () => County::all(['id', 'name', 'slug']));
        $county = $this->match($counties, $entry['county'], 'name');
        if (! $county || $entry['locality'] === '') {
            return [$county, null];
        }

        $countyId = $county->id;
        $localities = once(fn () => Locality::query()->cities()->where('county_id', $countyId)->get());

        return [$county, $this->match($localities, $entry['locality'], 'name')];
    }

    public function createTrial(DealerFeedSource $source, string $externalId, array $review): DealerFeedTrial
    {
        if ($review['errors'] !== []) {
            throw new DealerFeedException('Corectează erorile din previzualizare înainte să pornești proba.');
        }

        $trial = DB::transaction(function () use ($source, $externalId, $review) {
            $currentSource = DealerFeedSource::query()->lockForUpdate()->findOrFail($source->id);
            if ($currentSource->dealer_id !== $source->dealer_id || $currentSource->url !== $source->url
                || $currentSource->profile !== $source->profile || $currentSource->mapping !== $source->mapping
                || $currentSource->location_overrides !== $source->location_overrides
                || ($currentSource->last_check['run_id'] ?? null) !== $review['run_id']) {
                throw new DealerFeedException('Sursa a fost modificată. Verifică anunțul din nou.');
            }

            $externalKey = DealerFeedListing::externalKey($externalId);
            $listing = $currentSource->listings()->where('external_key', $externalKey)->first();
            if ($currentSource->runs()->where('import_status', 'queued')->exists()
                || in_array($listing?->import_status, ['waiting', 'processing'], true)) {
                throw new DealerFeedException('Există deja un import în curs pentru acest anunț.');
            }
            $trial = $listing?->trial;
            $service = $listing?->service;
            if ($trial && in_array($trial->status, ['queued', 'processing'], true)) {
                throw new DealerFeedException('Există deja o probă în curs pentru acest ID.');
            }
            if ($service && ($service->status !== 'pending' || $service->trashed() || $service->user_id !== $source->dealer_id)) {
                throw new DealerFeedException('Anunțul asociat a fost modificat în afara probei.');
            }
            $service ??= new Service(['status' => 'pending', 'images' => [], 'slug' => 'proba-feed-'.$source->id.'-'.substr($externalKey, 0, 24)]);
            $service->fill($review['attributes'])->forceFill([
                'city' => $review['attributes']['city'],
                'is_active' => false,
            ])->save();

            $listing ??= $currentSource->listings()->create([
                'service_id' => $service->id,
                'external_id' => $externalId,
                'external_key' => $externalKey,
            ]);
            $trial ??= new DealerFeedTrial();
            $trial->forceFill([
                'listing_id' => $listing->id,
                'run_id' => (string) Str::uuid(),
                'status' => $review['photos'] === [] ? 'complete' : 'queued',
                'photo_total' => $review['photo_total'],
                'photo_processed' => 0,
                'messages' => $review['warnings'],
            ])->save();

            return $trial;
        });

        if ($review['photos'] !== []) {
            try {
                $job = new DownloadDealerFeedTrialPhotos($trial->id, $trial->run_id, $source->domain(), $review['photos']);
                Bus::dispatch($job->onQueue(config('queue.service_images_queue')));
            } catch (Throwable) {
                $trial->finish(0, [...$trial->messages, 'Nu s-a putut programa descărcarea pozelor.'], 'failed');
            }
        }

        return $trial;
    }

    private function match(iterable $entries, string $value, string $label): mixed
    {
        $key = $this->key($value);
        if ($key === '') {
            return null;
        }
        $matches = collect($entries)->filter(fn ($entry) => $this->key($entry->{$label}) === $key || $this->key($entry->slug ?? '') === $key);

        return $matches->count() === 1 ? $matches->first() : null;
    }

    private function key(string $value): string
    {
        return preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($value)));
    }

    private function integer(string $value, int $minimum, int $maximum): ?int
    {
        if (! preg_match('/\A\d{1,8}\z/', $value)) {
            return null;
        }
        $number = (int) $value;

        return $number >= $minimum && $number <= $maximum ? $number : null;
    }

    private function plainText(string $value): string
    {
        $value = html_entity_decode($value, ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $value = preg_replace('~<(?:br\b[^>]*|/(?:p|div|li))>~i', "\n", $value);

        return trim(strip_tags($value));
    }
}
