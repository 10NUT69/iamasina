<?php

namespace App\Services;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\UriResolver;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use InvalidArgumentException;

class DealerFeedDownload
{
    private const MAX_BYTES = 5 * 1024 * 1024;
    private const MAX_REDIRECTS = 3;

    public function fetch(string $url): string
    {
        return $this->download($url, self::MAX_BYTES, 'application/xml, text/xml, application/json, */*', 8);
    }

    public function fetchPhoto(string $url, string $feedDomain, int $seconds): string
    {
        if (strtolower((string) parse_url($url, PHP_URL_HOST)) !== strtolower($feedDomain)) {
            throw new DealerFeedException('Fotografia este pe alt domeniu decât feedul.');
        }

        return $this->download($url, \App\Support\ServiceImageStorage::MAX_IMAGE_KB * 1024, 'image/*', $seconds, 0);
    }

    private function download(string $url, int $maxBytes, string $accept, int $seconds, int $maxRedirects = self::MAX_REDIRECTS): string
    {
        if (! extension_loaded('curl')) {
            throw new DealerFeedException('Extensia PHP cURL este necesară pentru a testa feedul.');
        }

        try {
            $uri = $this->httpsUri($url);
        } catch (InvalidArgumentException $exception) {
            throw new DealerFeedException($exception->getMessage());
        }
        $host = $uri->getHost();
        $addresses = gethostbynamel($host) ?: [];
        if ($addresses === [] || collect($addresses)->contains(fn ($ip) => ! filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_GLOBAL_RANGE))) {
            throw new DealerFeedException('Domeniul feedului trebuie să aibă o adresă IPv4 publică.');
        }

        // Pin the validated address for the request, including same-host redirects.
        $connection = Http::withOptions([
            'allow_redirects' => false,
            'decode_content' => false,
            'proxy' => '',
            'curl' => [CURLOPT_RESOLVE => ["{$host}:443:{$addresses[0]}"]],
            'progress' => function ($total, $received) use ($maxBytes) {
                if ($total > $maxBytes || $received > $maxBytes) {
                    throw new DealerFeedException('Fișierul depășește limita de '.($maxBytes / 1024 / 1024).' MB.');
                }
            },
        ])->withHeaders(['Accept' => $accept])
            ->connectTimeout(min(4, $seconds))->timeout($seconds);

        for ($redirects = 0; $redirects <= $maxRedirects; $redirects++) {
            try {
                $response = $connection->get((string) $uri);
            } catch (ConnectionException) {
                throw new DealerFeedException('Nu s-a putut conecta la serverul feedului.');
            }

            if (in_array($response->status(), [301, 302, 303, 307, 308], true)) {
                $location = $response->header('Location');
                try {
                    $destination = $this->httpsUri((string) UriResolver::resolve($uri, new Uri($location)));
                } catch (InvalidArgumentException) {
                    throw new DealerFeedException('Feedul a trimis o redirecționare nevalidă.');
                }
                if ($location === '' || $destination->getHost() !== $host || $redirects === $maxRedirects) {
                    throw new DealerFeedException('Serverul a trimis o redirecționare care nu poate fi urmată.');
                }
                $uri = $destination;
                continue;
            }

            if (! $response->successful()) {
                throw new DealerFeedException('Serverul feedului a răspuns cu HTTP '.$response->status().'.');
            }

            $body = $response->body();
            if (strlen($body) > $maxBytes) {
                throw new DealerFeedException('Fișierul depășește limita de '.($maxBytes / 1024 / 1024).' MB.');
            }

            return $body;
        }

        throw new DealerFeedException('Redirecționarea feedului nu a putut fi finalizată.');
    }

    private function httpsUri(string $url): Uri
    {
        $uri = new Uri($url);
        if (
            $uri->getScheme() !== 'https'
            || $uri->getPort() !== null
            || $uri->getUserInfo() !== ''
            || ! preg_match('/\A(?=.{1,253}\z)(?:[a-z0-9](?:[a-z0-9-]{0,61}[a-z0-9])?\.)+[a-z](?:[a-z0-9-]{0,61}[a-z0-9])?\z/i', $uri->getHost())
        ) {
            throw new InvalidArgumentException('Introdu un URL HTTPS cu un domeniu public, fără port sau utilizator în adresă.');
        }

        return $uri->withFragment('');
    }
}
