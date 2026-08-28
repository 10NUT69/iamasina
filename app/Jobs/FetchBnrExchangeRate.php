<?php

namespace App\Jobs;

use App\Services\BnrExchangeRateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class FetchBnrExchangeRate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /** Initial attempt plus a maximum of three retries. */
    public int $tries = 4;

    public int $timeout = 30;

    public bool $failOnTimeout = true;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [1800, 1800, 1800];
    }

    public function handle(BnrExchangeRateService $service): void
    {
        $exchangeRate = $service->fetchAndStoreEurRate();

        Log::info('Cursul EUR/RON de la BNR a fost salvat.', [
            'rate_date' => $exchangeRate->rate_date?->toDateString(),
            'rate' => $exchangeRate->rate,
        ]);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('Preluarea cursului EUR/RON de la BNR a eșuat după toate reîncercările.', [
            'message' => $exception?->getMessage(),
            'last_valid_rate_preserved' => true,
        ]);
    }
}
