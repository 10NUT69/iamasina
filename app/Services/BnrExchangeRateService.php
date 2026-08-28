<?php

namespace App\Services;

use App\Models\ExchangeRate;
use DateTimeImmutable;
use DateTimeZone;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Http;
use RuntimeException;
use SimpleXMLElement;

class BnrExchangeRateService
{
    private const SOURCE = 'BNR';

    private const BASE_CURRENCY = 'EUR';

    private const QUOTE_CURRENCY = 'RON';

    public function __construct(
        private readonly ServicePriceNormalizer $priceNormalizer
    ) {}

    public function fetchAndStoreEurRate(): ExchangeRate
    {
        $endpoint = (string) config('services.bnr.endpoint');

        $response = $this->request()
            ->get($endpoint)
            ->throw();

        $rateData = $this->parseEurRate($response->body());

        $exchangeRate = ExchangeRate::query()->updateOrCreate(
            [
                'source' => self::SOURCE,
                'base_currency' => self::BASE_CURRENCY,
                'quote_currency' => self::QUOTE_CURRENCY,
                'rate_date' => $rateData['date'],
            ],
            [
                'rate' => $rateData['rate'],
                'fetched_at' => now(),
                'source_url' => $endpoint,
            ]
        );

        $this->priceNormalizer->refreshRonPrices($exchangeRate);

        return $exchangeRate;
    }

    private function request(): PendingRequest
    {
        return Http::accept('application/xml')
            ->withUserAgent(config('app.name', 'Laravel').' BNR Exchange Rate Importer')
            ->connectTimeout((int) config('services.bnr.connect_timeout', 5))
            ->timeout((int) config('services.bnr.timeout', 15));
    }

    /**
     * @return array{date: string, rate: string}
     */
    private function parseEurRate(string $contents): array
    {
        $xml = $this->loadXml($contents);
        $namespace = $xml->getDocNamespaces(true)[''] ?? null;

        if (! $namespace) {
            throw new RuntimeException('Feedul BNR nu conține namespace-ul XML așteptat.');
        }

        $xml->registerXPathNamespace('bnr', $namespace);

        $publisher = $this->xpathText($xml, '/bnr:DataSet/bnr:Header/bnr:Publisher');
        $publishingDate = $this->xpathText($xml, '/bnr:DataSet/bnr:Header/bnr:PublishingDate');
        $quoteCurrency = strtoupper($this->xpathText($xml, '/bnr:DataSet/bnr:Body/bnr:OrigCurrency'));
        $cubeNodes = $xml->xpath('/bnr:DataSet/bnr:Body/bnr:Cube');
        $rateNodes = $xml->xpath('/bnr:DataSet/bnr:Body/bnr:Cube/bnr:Rate[@currency="EUR"]');

        if (stripos($publisher, 'National Bank of Romania') === false) {
            throw new RuntimeException('Feedul nu identifică Banca Națională a României ca emitent.');
        }

        if ($quoteCurrency !== self::QUOTE_CURRENCY) {
            throw new RuntimeException('Moneda de referință din feedul BNR nu este RON.');
        }

        if (! $cubeNodes || count($cubeNodes) !== 1 || ! $rateNodes || count($rateNodes) !== 1) {
            throw new RuntimeException('Feedul BNR nu conține o singură cotație EUR validă.');
        }

        $rateDate = trim((string) $cubeNodes[0]['date']);

        if (! $this->isValidDate($publishingDate) || $publishingDate !== $rateDate) {
            throw new RuntimeException('Data cotației BNR este invalidă sau inconsistentă.');
        }

        $rawRate = trim((string) $rateNodes[0]);
        $rawMultiplier = trim((string) $rateNodes[0]['multiplier']);
        $multiplier = $rawMultiplier === '' ? 1 : (int) $rawMultiplier;

        if (! preg_match('/^\d+(?:\.\d+)?$/', $rawRate) || $multiplier < 1) {
            throw new RuntimeException('Valoarea cotației EUR din feedul BNR este invalidă.');
        }

        $normalizedRate = (float) $rawRate / $multiplier;

        if (! is_finite($normalizedRate) || $normalizedRate <= 0) {
            throw new RuntimeException('Valoarea normalizată a cotației EUR este invalidă.');
        }

        return [
            'date' => $rateDate,
            'rate' => number_format($normalizedRate, 8, '.', ''),
        ];
    }

    private function loadXml(string $contents): SimpleXMLElement
    {
        if (trim($contents) === '') {
            throw new RuntimeException('Feedul BNR este gol.');
        }

        $previousErrorHandling = libxml_use_internal_errors(true);
        libxml_clear_errors();

        try {
            $xml = simplexml_load_string(
                $contents,
                SimpleXMLElement::class,
                LIBXML_NONET | LIBXML_NOCDATA | LIBXML_NOBLANKS
            );
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previousErrorHandling);
        }

        if (! $xml instanceof SimpleXMLElement) {
            throw new RuntimeException('Feedul BNR nu este un document XML valid.');
        }

        return $xml;
    }

    private function xpathText(SimpleXMLElement $xml, string $path): string
    {
        $nodes = $xml->xpath($path);

        if (! $nodes || count($nodes) !== 1) {
            throw new RuntimeException('Structura feedului BNR este incompletă.');
        }

        $value = trim((string) $nodes[0]);

        if ($value === '') {
            throw new RuntimeException('Feedul BNR conține un câmp obligatoriu gol.');
        }

        return $value;
    }

    private function isValidDate(string $date): bool
    {
        $parsed = DateTimeImmutable::createFromFormat('!Y-m-d', $date, new DateTimeZone('UTC'));

        return $parsed instanceof DateTimeImmutable && $parsed->format('Y-m-d') === $date;
    }
}
