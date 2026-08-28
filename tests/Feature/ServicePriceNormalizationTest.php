<?php

namespace Tests\Feature;

use App\Models\ExchangeRate;
use App\Models\Service;
use App\Services\ServicePriceNormalizer;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ServicePriceNormalizationTest extends TestCase
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
        });

        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->decimal('price_value', 10, 2)->nullable();
            $table->string('currency', 3)->default('EUR');
            $table->decimal('price_eur', 14, 4)->nullable();
            $table->string('status')->default('active');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function test_create_and_edit_keep_the_normalized_price_in_sync(): void
    {
        $this->createRate('5.25840000');

        $service = Service::query()->create([
            'price_value' => 52584,
            'currency' => 'RON',
            'status' => 'active',
        ]);

        $this->assertSame('10000.0000', $service->price_eur);

        $service->price_value = 12000;
        $service->currency = 'EUR';
        $service->save();

        $this->assertSame('12000.0000', $service->fresh()->price_eur);
    }

    public function test_ron_listing_can_still_be_saved_before_the_first_valid_rate_exists(): void
    {
        $service = Service::query()->create([
            'price_value' => 50000,
            'currency' => 'RON',
            'status' => 'active',
        ]);

        $this->assertNull($service->price_eur);
        $this->assertDatabaseHas('services', [
            'id' => $service->id,
            'price_value' => 50000,
            'currency' => 'RON',
        ]);
    }

    public function test_new_bnr_rate_repairs_old_active_ron_prices_without_touching_updated_at(): void
    {
        $exchangeRate = $this->createRate('5.25840000');
        $originalUpdatedAt = '2026-08-20 10:00:00';

        DB::table('services')->insert([
            'price_value' => 52584,
            'currency' => 'RON',
            'price_eur' => null,
            'status' => 'active',
            'created_at' => $originalUpdatedAt,
            'updated_at' => $originalUpdatedAt,
        ]);

        $updated = app(ServicePriceNormalizer::class)->refreshRonPrices($exchangeRate);
        $service = DB::table('services')->first();

        $this->assertSame(1, $updated);
        $this->assertEqualsWithDelta(10000, (float) $service->price_eur, 0.0001);
        $this->assertSame($originalUpdatedAt, $service->updated_at);
    }

    private function createRate(string $rate): ExchangeRate
    {
        return ExchangeRate::query()->create([
            'source' => 'BNR',
            'base_currency' => 'EUR',
            'quote_currency' => 'RON',
            'rate' => $rate,
            'rate_date' => '2026-08-28',
            'fetched_at' => '2026-08-28 14:00:00',
            'source_url' => 'https://curs.bnr.ro/nbrfxrates.xml',
        ]);
    }
}
