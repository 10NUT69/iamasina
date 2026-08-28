<?php

namespace App\Services;

use App\Models\ExchangeRate;
use App\Models\Service;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

class ServicePriceNormalizer
{
    private bool $latestRateLoaded = false;

    private ?ExchangeRate $latestRate = null;

    public function apply(Service $service): void
    {
        $service->price_eur = $this->normalizedEurPrice(
            $service->price_value,
            (string) $service->currency
        );
    }

    public function normalizedEurPrice(mixed $price, string $currency): ?string
    {
        if ($price === null || $price === '' || ! is_numeric($price)) {
            return null;
        }

        $currency = strtoupper($currency);

        if ($currency === 'EUR') {
            return number_format((float) $price, 4, '.', '');
        }

        if ($currency !== 'RON') {
            return null;
        }

        $exchangeRate = $this->latestRate();

        if (! $exchangeRate || (float) $exchangeRate->rate <= 0) {
            Log::warning('Prețul EUR normalizat nu a putut fi calculat deoarece nu există încă un curs BNR valid.', [
                'currency' => $currency,
            ]);

            return null;
        }

        return number_format((float) $price / (float) $exchangeRate->rate, 4, '.', '');
    }

    public function refreshRonPrices(ExchangeRate $exchangeRate): int
    {
        if (
            $exchangeRate->source !== 'BNR'
            || $exchangeRate->base_currency !== 'EUR'
            || $exchangeRate->quote_currency !== 'RON'
            || (float) $exchangeRate->rate <= 0
            || ! Schema::hasColumn('services', 'price_eur')
        ) {
            return 0;
        }

        $this->latestRate = $exchangeRate;
        $this->latestRateLoaded = true;
        $rateSql = number_format((float) $exchangeRate->rate, 8, '.', '');

        return DB::table('services')
            ->whereNull('deleted_at')
            ->where('currency', 'RON')
            ->whereNotNull('price_value')
            ->where(function ($query) {
                $query
                    ->where('status', 'active')
                    ->orWhereNull('price_eur');
            })
            ->update([
                'price_eur' => DB::raw("ROUND(price_value / {$rateSql}, 4)"),
            ]);
    }

    private function latestRate(): ?ExchangeRate
    {
        if (! $this->latestRateLoaded) {
            $this->latestRate = ExchangeRate::latestFor('EUR', 'RON');
            $this->latestRateLoaded = true;
        }

        return $this->latestRate;
    }
}
