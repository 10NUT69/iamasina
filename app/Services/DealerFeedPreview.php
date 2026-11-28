<?php

namespace App\Services;

use App\Models\DealerFeedSource;
use App\Support\ServiceImageStorage;
use DOMDocument;
use DOMElement;
use Illuminate\Support\Str;
use JsonException;
use stdClass;

class DealerFeedPreview
{
    public const FIELDS = [
        'id' => 'ID extern',
        'title' => 'Titlu',
        'description' => 'Descriere',
        'brand' => 'Marcă',
        'model' => 'Model',
        'year' => 'An fabricație',
        'mileage' => 'Kilometraj',
        'price_eur' => 'Preț',
        'price_type' => 'Tip preț',
        'currency' => 'Monedă',
        'body' => 'Caroserie',
        'fuel' => 'Combustibil',
        'transmission' => 'Transmisie',
        'drive' => 'Tracțiune',
        'engine_cc' => 'Cilindree',
        'power' => 'Putere',
        'county' => 'Județ',
        'locality' => 'Localitate',
        'status' => 'Stare în feed',
        'photos' => 'Fotografii',
    ];

    private const CHECKED_FIELDS = ['id', 'title', 'description', 'brand', 'model', 'year'];

    public function inspect(string $content, array $mapping, string $profileKey = DealerFeedSource::DEFAULT_PROFILE): array
    {
        $profile = $this->profile($profileKey);
        [$rows, $columns, $currency] = $this->parse($content, $profile);
        $suggested = $this->suggestMapping($columns, $profile['columns']);

        return [
            'columns' => $columns,
            'suggested' => $suggested,
            'item_count' => count($rows),
        ] + $this->summarize($rows, $mapping + $suggested, $profileKey, $profile, $currency);
    }

    public function entries(string $content, array $mapping, string $profileKey): \Generator
    {
        $profile = $this->profile($profileKey);
        [$rows, $columns, $currency] = $this->parse($content, $profile);
        if ($rows === []) {
            throw new DealerFeedException('Feedul nu conține anunțuri.');
        }

        $mapping += $this->suggestMapping($columns, $profile['columns']);
        if ($mapping['id'] === '' || ! in_array($mapping['id'], $columns, true)) {
            throw new DealerFeedException('Mapează câmpul ID extern la o coloană din fișier.');
        }

        foreach ($rows as $row) {
            yield $this->values($row, $mapping, $profile, $currency);
        }
    }

    public function format(string $profileKey): string
    {
        return $this->profile($profileKey)['format'] ?? 'xml';
    }

    public function photoUrls(string|array $value, string $profileKey): array
    {
        $separator = $this->profile($profileKey)['photo_separator'];

        $urls = is_array($value) ? array_filter($value, 'is_string') : explode($separator, $value);

        return array_values(array_filter(array_map('trim', $urls), fn ($url) => $url !== ''));
    }

    private function profile(string $profileKey): array
    {
        $profile = config('dealer-feeds.profiles.'.$profileKey);
        if (! is_array($profile)) {
            throw new DealerFeedException('Modelul de import selectat nu este disponibil.');
        }

        return $profile;
    }

    private function parse(string $content, array $profile): array
    {
        if (($profile['format'] ?? 'xml') === 'json') {
            return $this->parseJson($content, $profile);
        }

        return $this->parseXml($content, $profile);
    }

    private function parseJson(string $content, array $profile): array
    {
        try {
            $document = json_decode($content, false, 64, JSON_THROW_ON_ERROR | JSON_BIGINT_AS_STRING);
        } catch (JsonException) {
            throw new DealerFeedException('Răspunsul trebuie să fie un document JSON valid.');
        }
        if (! $document instanceof stdClass || ! is_array($document->{$profile['root']} ?? null)) {
            throw new DealerFeedException('Modelul '.$profile['label'].' așteaptă o listă de anunțuri în „'.$profile['root'].'”.');
        }

        $items = $document->{$profile['root']};
        $totalField = $profile['document_total'] ?? '';
        if (property_exists($document, $totalField) && ! in_array($document->{$totalField}, [count($items), (string) count($items)], true)) {
            throw new DealerFeedException('Numărul de anunțuri din lista JSON diferă de totalul declarat.');
        }

        $rows = [];
        $columns = [];
        foreach ($items as $item) {
            if (! $item instanceof stdClass) {
                throw new DealerFeedException('Lista JSON trebuie să conțină obiecte, câte unul pentru fiecare anunț.');
            }
            $row = get_object_vars($item);
            foreach (array_keys($row) as $column) {
                if (is_string($column) && $column !== '') {
                    $columns[$column] = true;
                }
            }
            $rows[] = $row;
        }
        $currency = $document->{$profile['document_currency']} ?? $profile['default_currency'];
        $availableColumns = array_keys($columns);
        sort($availableColumns, SORT_NATURAL | SORT_FLAG_CASE);

        return [$rows, $availableColumns, is_string($currency) ? trim($currency) : ''];
    }

    private function parseXml(string $xml, array $profile): array
    {
        $document = new DOMDocument();
        $document->resolveExternals = false;
        $document->substituteEntities = false;
        if (trim($xml) === '' || ! $document->loadXML($xml, LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING) || $document->doctype !== null) {
            throw new DealerFeedException('Răspunsul trebuie să fie un document XML valid fără declarație DOCTYPE.');
        }

        $root = $document->documentElement;
        if ($root?->tagName !== $profile['root']) {
            throw new DealerFeedException('Modelul '.$profile['label'].' așteaptă XML cu structura '.$profile['root'].' → '.$profile['record'].'.');
        }

        $rows = [];
        $columns = [];
        foreach ($root->childNodes as $record) {
            if (! $record instanceof DOMElement) {
                continue;
            }
            if ($record->tagName !== $profile['record']) {
                throw new DealerFeedException('XML-ul conține elemente neașteptate în lista de anunțuri.');
            }
            $row = [];
            foreach ($record->childNodes as $element) {
                if ($element instanceof DOMElement && ! isset($row[$element->tagName])) {
                    $row[$element->tagName] = trim($element->textContent);
                    $columns[$element->tagName] = true;
                }
            }
            $rows[] = $row;
        }

        $availableColumns = array_keys($columns);
        sort($availableColumns, SORT_NATURAL | SORT_FLAG_CASE);
        return [$rows, $availableColumns, $profile['default_currency']];
    }

    private function suggestMapping(array $columns, array $defaults): array
    {
        $suggested = [];
        foreach (config('dealer-feeds.field_aliases') as $field => $aliases) {
            if (isset($defaults[$field]) && in_array($defaults[$field], $columns, true)) {
                $suggested[$field] = $defaults[$field];
                continue;
            }
            $candidates = array_values(array_filter($columns, function (string $column) use ($aliases) {
                $key = preg_replace('/[^a-z0-9]/', '', strtolower(Str::ascii($column)));

                return in_array($key, $aliases, true);
            }));
            $suggested[$field] = count($candidates) === 1 ? $candidates[0] : '';
        }

        return $suggested;
    }

    private function values(array $row, array $mapping, array $profile, string $defaultCurrency): array
    {
        $values = [];
        foreach ($mapping as $field => $column) {
            $value = $row[$column] ?? '';
            $values[$field] = match (true) {
                $field === 'photos' && is_array($value) => $value,
                is_string($value), is_int($value) => trim((string) $value),
                $field !== 'id' && is_float($value) => (string) $value,
                $field === 'price_type' => json_encode($value, JSON_THROW_ON_ERROR),
                default => '',
            };
        }
        if ($values['currency'] === '') {
            $values['currency'] = $defaultCurrency;
        }

        foreach (['branch_code', 'branch_name'] as $field) {
            $value = $row[$profile[$field] ?? ''] ?? '';
            $values[$field] = is_string($value) ? trim($value) : '';
        }

        return $values;
    }

    private function summarize(array $rows, array $mapping, string $profileKey, array $profile, string $defaultCurrency): array
    {
        $missing = array_fill_keys(self::CHECKED_FIELDS, 0);
        $ids = [];
        $duplicates = 0;
        $overPhotoLimit = 0;
        $sample = [];

        foreach ($rows as $row) {
            $values = $this->values($row, $mapping, $profile, $defaultCurrency);
            foreach ($missing as $field => $count) {
                if ($values[$field] === '') {
                    $missing[$field]++;
                }
            }
            $id = $values['id'];
            if ($id !== '') {
                $duplicates += isset($ids[$id]) ? 1 : 0;
                $ids[$id] = true;
            }

            $photos = $this->photoUrls($values['photos'], $profileKey);
            $overPhotoLimit += count($photos) > ServiceImageStorage::MAX_IMAGES ? 1 : 0;

            if (count($sample) < 5) {
                $item = [];
                foreach ($values as $field => $value) {
                    $item[$field] = $field === 'photos'
                        ? count($photos)
                        : Str::limit(trim(strip_tags($value)), 150);
                }
                $sample[] = $item;
            }
        }

        return [
            'missing' => array_filter($missing),
            'duplicate_ids' => $duplicates,
            'over_photo_limit' => $overPhotoLimit,
            'sample' => $sample,
        ];
    }
}
