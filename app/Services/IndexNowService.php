<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class IndexNowService
{
    private const MAX_URLS_PER_REQUEST = 10000;

    public function submit(string|array $urls): bool
    {
        if (!$this->isConfigured()) {
            return false;
        }

        $urls = $this->normalizeUrls($urls);

        if ($urls === []) {
            return false;
        }

        $host = $this->hostFromUrl($urls[0]);

        if (!$host || !$this->isPublicHost($host)) {
            return false;
        }

        $urls = array_values(array_filter(
            $urls,
            fn (string $url): bool => $this->hostFromUrl($url) === $host
        ));

        if ($urls === []) {
            return false;
        }

        $key = (string) config('services.indexnow.key');
        $payload = [
            'host' => $host,
            'key' => $key,
            'keyLocation' => $this->keyLocation($urls[0], $key),
            'urlList' => $urls,
        ];

        try {
            $response = Http::timeout((int) config('services.indexnow.timeout', 5))
                ->acceptJson()
                ->asJson()
                ->post((string) config('services.indexnow.endpoint'), $payload);
        } catch (\Throwable $e) {
            Log::warning('IndexNow submission failed.', [
                'error' => $e->getMessage(),
                'urls' => $urls,
            ]);

            return false;
        }

        if (!$response->successful()) {
            Log::warning('IndexNow submission returned a non-success response.', [
                'status' => $response->status(),
                'body' => Str::limit($response->body(), 500),
                'urls' => $urls,
            ]);

            return false;
        }

        return true;
    }

    private function isConfigured(): bool
    {
        $key = (string) config('services.indexnow.key');
        $endpoint = (string) config('services.indexnow.endpoint');

        return (bool) config('services.indexnow.enabled', true)
            && $endpoint !== ''
            && preg_match('/^[A-Za-z0-9-]{8,128}$/', $key) === 1;
    }

    private function normalizeUrls(string|array $urls): array
    {
        return collect(is_array($urls) ? $urls : [$urls])
            ->map(fn ($url): string => trim((string) $url))
            ->filter(fn (string $url): bool => $this->isValidPublicUrl($url))
            ->unique()
            ->take(self::MAX_URLS_PER_REQUEST)
            ->values()
            ->all();
    }

    private function isValidPublicUrl(string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = $this->hostFromUrl($url);

        return in_array($scheme, ['http', 'https'], true)
            && $host !== null
            && $this->isPublicHost($host);
    }

    private function keyLocation(string $url, string $key): string
    {
        $configuredLocation = config('services.indexnow.key_location');

        if (is_string($configuredLocation) && trim($configuredLocation) !== '') {
            return trim($configuredLocation);
        }

        $scheme = parse_url($url, PHP_URL_SCHEME) ?: 'https';
        $host = $this->hostFromUrl($url);

        return "{$scheme}://{$host}/{$key}.txt";
    }

    private function hostFromUrl(string $url): ?string
    {
        $host = parse_url($url, PHP_URL_HOST);

        if (!is_string($host) || $host === '') {
            return null;
        }

        return strtolower(trim($host, '[]'));
    }

    private function isPublicHost(string $host): bool
    {
        $host = strtolower(trim($host, '[]'));

        if (in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return false;
        }

        if (str_ends_with($host, '.local')
            || str_ends_with($host, '.localhost')
            || str_ends_with($host, '.test')
        ) {
            return false;
        }

        if (filter_var($host, FILTER_VALIDATE_IP)) {
            return filter_var(
                $host,
                FILTER_VALIDATE_IP,
                FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE
            ) !== false;
        }

        return str_contains($host, '.');
    }
}
