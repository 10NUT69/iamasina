<?php

namespace Tests\Feature;

use App\Jobs\FetchBnrExchangeRate;
use App\Models\ExchangeRate;
use App\Services\BnrExchangeRateService;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Tests\TestCase;

class BnrExchangeRateServiceTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('exchange_rates', function (Blueprint $table) {
            $table->id();
            $table->string('source', 20);
            $table->char('base_currency', 3);
            $table->char('quote_currency', 3);
            $table->decimal('rate', 14, 8);
            $table->date('rate_date');
            $table->timestamp('fetched_at');
            $table->string('source_url');
            $table->timestamps();
            $table->unique(['source', 'base_currency', 'quote_currency', 'rate_date']);
        });
    }

    public function test_it_stores_the_valid_eur_rate_from_the_official_bnr_feed(): void
    {
        Http::fake([
            config('services.bnr.endpoint') => Http::response($this->validFeed(), 200, [
                'Content-Type' => 'application/xml',
            ]),
        ]);

        $exchangeRate = app(BnrExchangeRateService::class)->fetchAndStoreEurRate();

        $this->assertSame('BNR', $exchangeRate->source);
        $this->assertSame('EUR', $exchangeRate->base_currency);
        $this->assertSame('RON', $exchangeRate->quote_currency);
        $this->assertSame('5.25840000', $exchangeRate->rate);
        $this->assertSame('2026-08-28', $exchangeRate->rate_date->toDateString());
        $this->assertNotNull($exchangeRate->fetched_at);
        $this->assertDatabaseCount('exchange_rates', 1);
    }

    public function test_invalid_feed_leaves_the_last_valid_rate_untouched(): void
    {
        $lastValidRate = ExchangeRate::query()->create([
            'source' => 'BNR',
            'base_currency' => 'EUR',
            'quote_currency' => 'RON',
            'rate' => '5.12340000',
            'rate_date' => '2026-08-27',
            'fetched_at' => '2026-08-27 14:00:00',
            'source_url' => config('services.bnr.endpoint'),
        ]);

        Http::fake([
            config('services.bnr.endpoint') => Http::response('<html>indisponibil</html>', 200),
        ]);

        try {
            app(BnrExchangeRateService::class)->fetchAndStoreEurRate();
            $this->fail('Feedul invalid trebuia să oprească salvarea.');
        } catch (RuntimeException) {
            // Eșecul este retransmis jobului pentru reîncercare.
        }

        $lastValidRate->refresh();

        $this->assertSame('5.12340000', $lastValidRate->rate);
        $this->assertSame('2026-08-27', $lastValidRate->rate_date->toDateString());
        $this->assertDatabaseCount('exchange_rates', 1);
    }

    public function test_unavailable_feed_leaves_the_last_valid_rate_untouched(): void
    {
        $lastValidRate = ExchangeRate::query()->create([
            'source' => 'BNR',
            'base_currency' => 'EUR',
            'quote_currency' => 'RON',
            'rate' => '5.12340000',
            'rate_date' => '2026-08-27',
            'fetched_at' => '2026-08-27 14:00:00',
            'source_url' => config('services.bnr.endpoint'),
        ]);

        Http::fake([
            config('services.bnr.endpoint') => Http::response('indisponibil', 503),
        ]);

        try {
            app(BnrExchangeRateService::class)->fetchAndStoreEurRate();
            $this->fail('Răspunsul BNR indisponibil trebuia să oprească salvarea.');
        } catch (RequestException) {
            // Eșecul este retransmis jobului pentru reîncercare.
        }

        $lastValidRate->refresh();

        $this->assertSame('5.12340000', $lastValidRate->rate);
        $this->assertSame('2026-08-27', $lastValidRate->rate_date->toDateString());
        $this->assertDatabaseCount('exchange_rates', 1);
    }

    public function test_job_retries_three_times_at_thirty_minute_intervals(): void
    {
        $job = new FetchBnrExchangeRate;

        $this->assertSame(4, $job->tries);
        $this->assertSame([1800, 1800, 1800], $job->backoff());
        $this->assertSame(30, $job->timeout);
        $this->assertTrue($job->failOnTimeout);
    }

    private function validFeed(): string
    {
        return <<<'XML'
<?xml version="1.0" encoding="utf-8"?>
<DataSet xmlns="https://www.bnr.ro/xsd">
    <Header>
        <Publisher>National Bank of Romania</Publisher>
        <PublishingDate>2026-08-28</PublishingDate>
        <MessageType>DR</MessageType>
    </Header>
    <Body>
        <Subject>Reference rates</Subject>
        <OrigCurrency>RON</OrigCurrency>
        <Cube date="2026-08-28">
            <Rate currency="USD">4.5171</Rate>
            <Rate currency="EUR">5.2584</Rate>
        </Cube>
    </Body>
</DataSet>
XML;
    }
}
