<?php

namespace App\Http\Controllers;

use App\Models\SavedSearch;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class SavedSearchController extends Controller
{
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:160'],
            'url' => ['required', 'string', 'max:2048'],
            'filters' => ['nullable', 'array'],
        ]);

        $savedSearch = $this->saveForUser($request, $data);

        return response()->json([
            'status' => $savedSearch->wasRecentlyCreated ? 'created' : 'updated',
            'saved_search' => [
                'id' => $savedSearch->id,
                'name' => $savedSearch->name,
                'url' => $savedSearch->url,
            ],
        ]);
    }

    public function import(Request $request)
    {
        $data = $request->validate([
            'saved_searches' => ['required', 'array', 'max:25'],
            'saved_searches.*.name' => ['nullable', 'string', 'max:160'],
            'saved_searches.*.url' => ['required', 'string', 'max:2048'],
            'saved_searches.*.filters' => ['nullable', 'array'],
        ]);

        $created = 0;
        $processed = 0;

        foreach ($data['saved_searches'] as $searchData) {
            $savedSearch = $this->saveForUser($request, $searchData);

            $processed++;
            if ($savedSearch->wasRecentlyCreated) {
                $created++;
            }
        }

        return response()->json([
            'status' => 'ok',
            'imported' => $created,
            'processed' => $processed,
        ]);
    }

    public function destroy(Request $request, SavedSearch $savedSearch)
    {
        abort_unless($savedSearch->user_id === $request->user()->id, 403);

        $savedSearch->delete();

        return back()->with('success', 'Cautarea a fost stearsa.');
    }

    private function saveForUser(Request $request, array $data): SavedSearch
    {
        $url = $this->normalizeUrl($data['url'], $request);
        $filters = $this->normalizeFilters($data['filters'] ?? []);
        $hash = hash('sha256', $url . '|' . json_encode($filters));

        return SavedSearch::updateOrCreate(
            [
                'user_id' => $request->user()->id,
                'hash' => $hash,
            ],
            [
                'name' => $this->searchName($data['name'] ?? null, $filters),
                'url' => $url,
                'filters' => $filters,
            ]
        );
    }

    private function normalizeUrl(string $url, Request $request): string
    {
        $url = trim($url);
        $allowedHosts = array_filter([
            parse_url(url('/'), PHP_URL_HOST),
            $request->getHost(),
        ]);
        $urlHost = parse_url($url, PHP_URL_HOST);

        if ($urlHost && ! in_array($urlHost, $allowedHosts, true)) {
            return route('cars.index');
        }

        if (! $urlHost && ! Str::startsWith($url, '/')) {
            return route('cars.index');
        }

        return $url;
    }

    private function normalizeFilters(array $filters): array
    {
        return collect($filters)
            ->map(fn ($value) => is_string($value) ? trim($value) : $value)
            ->filter(fn ($value) => $value !== null && $value !== '')
            ->only([
                'seller_type',
                'brand_id',
                'brand',
                'model_id',
                'model',
                'county_id',
                'county',
                'locality_id',
                'locality',
                'caroserie_id',
                'caroserie',
                'combustibil_id',
                'combustibil',
                'cutie_viteze_id',
                'cutie_viteze',
                'price_min',
                'price_max',
                'km_min',
                'km_max',
                'year_min',
                'year_max',
                'sort',
            ])
            ->all();
    }

    private function searchName(?string $name, array $filters): string
    {
        $name = trim((string) $name);

        if ($name !== '') {
            return Str::limit($name, 160, '');
        }

        $parts = array_filter([
            Arr::get($filters, 'brand'),
            Arr::get($filters, 'model'),
            Arr::get($filters, 'locality'),
            Arr::get($filters, 'county'),
        ]);

        return $parts ? Str::limit(implode(' ', $parts), 160, '') : 'Cautare auto';
    }
}
