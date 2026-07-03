<?php

namespace App\Support;

use App\Models\Service;

class ServiceShowHeading
{
    public static function make(Service $service): string
    {
        return self::mobileTitle($service);
    }

    public static function desktop(Service $service): string
    {
        $parts = array_values(array_filter([
            self::desktopLead($service),
            self::engineSummary($service),
            self::fuelSummary($service),
            self::powerSummary($service),
            self::desktopTransmissionSummary($service),
            self::mileageSummary($service),
            self::locationLabel($service),
        ]));

        return implode(', ', $parts) ?: 'Autoturism de vânzare';
    }

    public static function mobileTitle(Service $service): string
    {
        return self::headlineTitle($service);
    }

    public static function mobileSpecs(Service $service): string
    {
        $parts = array_values(array_filter([
            self::mobileMotorizareSummary($service),
            self::powerSummary($service),
            self::mobileTransmissionSummary($service),
            self::mileageSummary($service),
            self::locationLabel($service),
        ]));

        return implode(' · ', $parts);
    }

    private static function desktopLead(Service $service): string
    {
        return self::headlineTitle($service);
    }

    private static function headlineTitle(Service $service): string
    {
        $base = self::baseLabel($service);
        $year = self::number($service->an_fabricatie ?? $service->year);
        $emissions = self::emissionsSummary($service);

        return implode(' · ', array_values(array_filter([
            $base,
            $year ? (string) $year : null,
            $emissions,
        ])));
    }

    private static function baseLabel(Service $service): string
    {
        $brandRel = self::relation($service, 'brandRel');
        $modelRel = self::relation($service, 'modelRel');
        $generation = self::relation($service, 'generation');
        $generationModel = self::relation($generation, 'model');
        $generationBrand = self::relation($generationModel, 'brand');
        $caroserie = self::relation($service, 'caroserie');

        $brand = self::text($brandRel?->name)
            ?: self::text($generationBrand?->name)
            ?: self::text($service->brand);

        $model = self::text($modelRel?->name)
            ?: self::text($generationModel?->name)
            ?: self::text($service->model);

        $body = self::text($caroserie?->nume)
            ?: self::text($service->body_type);

        return self::joinWords([$brand, $model, $body])
            ?: self::text($service->title)
            ?: 'Autoturism';
    }

    private static function engineSummary(Service $service): ?string
    {
        $engine = self::number($service->capacitate_cilindrica ?? $service->engine_capacity ?? $service->engine_size);

        return $engine ? 'Motorizare: ' . self::formatNumber($engine) . ' cmc' : null;
    }

    private static function fuelSummary(Service $service): ?string
    {
        $combustibil = self::relation($service, 'combustibil');
        $fuel = self::text($combustibil?->nume)
            ?: self::text($service->fuel_type);

        return $fuel ? self::lowerFirst($fuel) : null;
    }

    private static function powerSummary(Service $service): ?string
    {
        $power = self::number($service->putere ?? $service->putere_cp ?? $service->power);

        return $power ? "{$power} CP" : null;
    }

    private static function mobileEngineFuelSummary(Service $service): ?string
    {
        $engine = self::number($service->capacitate_cilindrica ?? $service->engine_capacity ?? $service->engine_size);
        $fuel = self::fuelSummary($service);

        if ($engine && $fuel) {
            return self::formatLiters($engine) . ' ' . $fuel;
        }

        if ($engine) {
            return self::formatLiters($engine) . ' L';
        }

        return $fuel;
    }

    private static function mobileMotorizareSummary(Service $service): ?string
    {
        $summary = self::mobileEngineFuelSummary($service);

        return $summary ? 'Motorizare: ' . $summary : null;
    }

    private static function desktopTransmissionSummary(Service $service): ?string
    {
        $transmission = self::transmissionLabel($service);

        return $transmission ? self::lowerFirst($transmission) : null;
    }

    private static function mobileTransmissionSummary(Service $service): ?string
    {
        $transmission = self::transmissionLabel($service);

        return $transmission ? self::upperFirst(self::lowerFirst($transmission)) : null;
    }

    private static function transmissionLabel(Service $service): ?string
    {
        $cutieViteze = self::relation($service, 'cutieViteze');

        return self::text($cutieViteze?->nume)
            ?: self::text($service->transmission);
    }

    private static function emissionsSummary(Service $service): ?string
    {
        $normaPoluare = self::relation($service, 'normaPoluare');

        return self::text($normaPoluare?->nume);
    }

    private static function mileageSummary(Service $service): ?string
    {
        $mileage = self::number($service->km ?? $service->mileage);

        return $mileage ? self::formatNumber($mileage) . ' km' : null;
    }

    private static function locationLabel(Service $service): ?string
    {
        $localityRel = self::relation($service, 'locality');
        $countyRel = self::relation($service, 'county');
        $locality = self::text($localityRel?->name)
            ?: self::text($service->city);
        $county = self::text($countyRel?->name);

        if ($locality && $county) {
            return self::sameAscii($locality, $county)
                ? $locality
                : "{$locality}, {$county}";
        }

        return $locality ?: $county;
    }

    private static function joinWords(array $parts): ?string
    {
        $text = implode(' ', array_values(array_filter(array_map([self::class, 'text'], $parts))));

        return self::text($text);
    }

    private static function lowerFirst(string $value): string
    {
        return function_exists('mb_strtolower')
            ? mb_strtolower($value, 'UTF-8')
            : strtolower($value);
    }

    private static function upperFirst(string $value): string
    {
        if (function_exists('mb_substr') && function_exists('mb_strtoupper')) {
            $first = mb_substr($value, 0, 1, 'UTF-8');
            $rest = mb_substr($value, 1, null, 'UTF-8');

            return mb_strtoupper($first, 'UTF-8') . $rest;
        }

        return ucfirst($value);
    }

    private static function formatNumber(int $number): string
    {
        return number_format($number, 0, ',', '.');
    }

    private static function formatLiters(int $engine): string
    {
        return number_format($engine / 1000, 1, ',', '');
    }

    private static function sameAscii(string $left, string $right): bool
    {
        $ascii = static function (string $value): string {
            $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;

            return strtolower(preg_replace('/[^a-z0-9]+/i', '', $value) ?: '');
        };

        return $ascii($left) === $ascii($right);
    }

    private static function number(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        $number = (int) $value;

        return $number > 0 ? $number : null;
    }

    private static function text(mixed $value): ?string
    {
        if (!is_scalar($value)) {
            return null;
        }

        $text = trim(preg_replace('/\s+/', ' ', (string) $value) ?: '');

        return $text === '' ? null : $text;
    }

    private static function relation(mixed $model, string $name): mixed
    {
        if (!is_object($model) || !method_exists($model, 'relationLoaded')) {
            return null;
        }

        return $model->relationLoaded($name) ? $model->getRelation($name) : null;
    }
}
