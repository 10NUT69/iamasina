<?php

namespace App\Console\Commands;

use App\Services\BnrExchangeRateService;
use Illuminate\Console\Command;
use Throwable;

class FetchBnrExchangeRate extends Command
{
    protected $signature = 'exchange-rates:fetch-bnr';

    protected $description = 'Preia și salvează cursul EUR/RON din feedul oficial BNR';

    public function handle(BnrExchangeRateService $service): int
    {
        try {
            $exchangeRate = $service->fetchAndStoreEurRate();
        } catch (Throwable $exception) {
            report($exception);
            $this->error('Cursul BNR nu a putut fi preluat. Ultimul curs valid a rămas neschimbat.');

            return self::FAILURE;
        }

        $this->info(sprintf(
            'Curs BNR salvat: 1 EUR = %s RON (%s).',
            $exchangeRate->rate,
            $exchangeRate->rate_date?->toDateString()
        ));

        return self::SUCCESS;
    }
}
