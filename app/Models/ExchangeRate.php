<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ExchangeRate extends Model
{
    protected $fillable = [
        'source',
        'base_currency',
        'quote_currency',
        'rate',
        'rate_date',
        'fetched_at',
        'source_url',
    ];

    protected function casts(): array
    {
        return [
            'rate' => 'decimal:8',
            'rate_date' => 'date',
            'fetched_at' => 'datetime',
        ];
    }

    public static function latestFor(
        string $baseCurrency,
        string $quoteCurrency,
        string $source = 'BNR'
    ): ?self {
        return self::query()
            ->where('source', strtoupper($source))
            ->where('base_currency', strtoupper($baseCurrency))
            ->where('quote_currency', strtoupper($quoteCurrency))
            ->latest('rate_date')
            ->first();
    }
}
