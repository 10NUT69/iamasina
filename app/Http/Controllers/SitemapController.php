<?php

namespace App\Http\Controllers;

use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\Service;
use App\Models\User;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    public function index(): Response
    {
        $latestUpdate = $this->latestRelevantUpdate();

        $todayKey = 'sitemap.xml:v6:' . now()->toDateString();
        $yesterdayKey = 'sitemap.xml:v6:' . now()->subDay()->toDateString();

        if (Cache::has($todayKey)) {
            return response(Cache::get($todayKey), 200)->header('Content-Type', 'text/xml');
        }

        $yesterdayMetaKey = $yesterdayKey . ':generated_at';
        if (Cache::has($yesterdayKey) && Cache::has($yesterdayMetaKey)) {
            $yesterdayGeneratedAt = Cache::get($yesterdayMetaKey);
            if (is_string($yesterdayGeneratedAt)) {
                $yesterdayGeneratedAt = Carbon::parse($yesterdayGeneratedAt);
            }

            if (!$latestUpdate || $latestUpdate->lte($yesterdayGeneratedAt)) {
                return response(Cache::get($yesterdayKey), 200)->header('Content-Type', 'text/xml');
            }
        }

        $xml = $this->buildSitemapXml();

        Cache::put($todayKey, $xml, now()->addDay());
        Cache::put($todayKey . ':generated_at', now(), now()->addDay());

        return response($xml, 200)->header('Content-Type', 'text/xml');
    }

    private function buildSitemapXml(): string
    {
        $urls = [];
        $now = now();

        $this->upsertUrl($urls, route('services.index'), $now, 'daily', '1.0');
        $this->upsertUrl($urls, route('cars.index'), $now, 'daily', '0.9');

        foreach ($this->staticPages() as $routeName => $meta) {
            if (Route::has($routeName)) {
                $this->upsertUrl($urls, route($routeName), $now, $meta['changefreq'], $meta['priority']);
            }
        }

        $this->activeServicesQuery()
            ->select([
                'id',
                'title',
                'slug',
                'status',
                'user_id',
                'county_id',
                'locality_id',
                'brand_id',
                'model_id',
                'car_generation_id',
                'city',
                'updated_at',
                'created_at',
            ])
            ->with([
                'county:id,slug',
                'locality:id,county_id,slug',
                'locality.county:id,slug',
                'user:id,user_type,company_name,dealer_slug,county,county_id,city,locality_id,updated_at,created_at',
                'user.dealerCounty:id,slug',
                'user.dealerLocality:id,county_id,slug',
                'generation:id,car_model_id',
                'generation.model:id,car_brand_id,name,slug',
                'generation.model.brand:id,name,slug',
                'brandRel:id,name,slug',
                'modelRel:id,car_brand_id,name,slug',
            ])
            ->orderBy('id')
            ->chunkById(1000, function ($services) use (&$urls) {
                foreach ($services as $service) {
                    $lastmod = $service->updated_at ?? $service->created_at;
                    $this->addListingUrlsForService($urls, $service, $lastmod);

                    $publicUrl = $service->public_url;
                    if ($publicUrl && $publicUrl !== url('/')) {
                        $this->upsertUrl($urls, $publicUrl, $lastmod, 'daily', '0.7');
                    }
                }
            });

        $urls = array_values($urls);

        return view('sitemap', compact('urls'))->render();
    }

    private function addListingUrlsForService(array &$urls, Service $service, mixed $lastmod): void
    {
        $model = $service->generation?->model ?: $service->modelRel;
        $brand = $service->generation?->model?->brand ?: $service->brandRel ?: $model?->brand;

        $brandSlug = $brand?->slug
            ?: ($brand?->name ? Str::slug($brand->name) : null)
            ?: ($service->brand ? Str::slug($service->brand) : null);

        $modelSlug = $model?->slug
            ?: ($model?->name ? Str::slug($model->name) : null)
            ?: ($service->model ? Str::slug($service->model) : null);

        if ($brandSlug) {
            $this->upsertUrl(
                $urls,
                route('brand.index', ['segment1' => $brandSlug]),
                $lastmod,
                'daily',
                '0.8'
            );
        }

        if ($brandSlug && $modelSlug) {
            $this->upsertUrl(
                $urls,
                route('brand.model.index', ['segment1' => $brandSlug, 'segment2' => $modelSlug]),
                $lastmod,
                'daily',
                '0.75'
            );
        }

        $county = $service->county ?: $service->locality?->county;
        $countySlug = $county?->slug;
        $citySlug = $service->locality?->slug ?: ($service->city ? Str::slug($service->city) : null);

        if ($countySlug) {
            $this->upsertUrl(
                $urls,
                route('brand.index', ['segment1' => $countySlug]),
                $lastmod,
                'daily',
                '0.65'
            );
        }

        if ($countySlug && $citySlug) {
            $this->upsertUrl(
                $urls,
                route('brand.model.index', ['segment1' => $countySlug, 'segment2' => $citySlug]),
                $lastmod,
                'daily',
                '0.6'
            );
        }

        if ($service->user?->dealer_canonical_url) {
            $this->upsertUrl($urls, $service->user->dealer_canonical_url, $lastmod, 'daily', '0.65');
        }
    }

    private function staticPages(): array
    {
        return [
            'page.about' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'page.contact' => ['changefreq' => 'monthly', 'priority' => '0.5'],
            'page.blog' => ['changefreq' => 'weekly', 'priority' => '0.6'],
            'dealers.index' => ['changefreq' => 'daily', 'priority' => '0.7'],
            'page.terms' => ['changefreq' => 'yearly', 'priority' => '0.3'],
            'page.privacy' => ['changefreq' => 'yearly', 'priority' => '0.3'],
        ];
    }

    private function upsertUrl(array &$urls, string $loc, mixed $lastmod, string $changefreq, string $priority): void
    {
        $lastmodCarbon = $lastmod ? Carbon::parse($lastmod) : now();
        $key = rtrim($loc, '/');

        if (isset($urls[$key]) && Carbon::parse($urls[$key]['lastmod'])->gte($lastmodCarbon)) {
            return;
        }

        $urls[$key] = [
            'loc' => $loc,
            'lastmod' => $lastmodCarbon->toAtomString(),
            'changefreq' => $changefreq,
            'priority' => $priority,
        ];
    }

    private function latestRelevantUpdate(): ?Carbon
    {
        return collect([
            $this->latestModelTimestamp(Service::class),
            $this->latestModelTimestamp(CarBrand::class),
            $this->latestModelTimestamp(CarModel::class),
            $this->latestDealerTimestamp(),
        ])
            ->filter()
            ->map(fn ($timestamp) => Carbon::parse($timestamp))
            ->sortDesc()
            ->first();
    }

    private function latestModelTimestamp(string $modelClass): mixed
    {
        $query = $modelClass === Service::class
            ? $this->activeServicesQuery()
            : $modelClass::query();

        $record = $query
            ->select(['updated_at', 'created_at'])
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        return $record?->updated_at ?? $record?->created_at;
    }

    private function latestDealerTimestamp(): mixed
    {
        $dealer = User::query()
            ->select(['updated_at', 'created_at'])
            ->where('user_type', 'dealer')
            ->whereNotNull('company_name')
            ->where('company_name', '!=', '')
            ->whereHas('services', fn ($query) => $query->withTrashed()->where('status', 'active'))
            ->orderByDesc('updated_at')
            ->orderByDesc('created_at')
            ->first();

        return $dealer?->updated_at ?? $dealer?->created_at;
    }

    private function activeServicesQuery()
    {
        if (in_array(SoftDeletes::class, class_uses_recursive(Service::class))) {
            return Service::withTrashed()->where('status', 'active');
        }

        return Service::query()->where('status', 'active');
    }
}
