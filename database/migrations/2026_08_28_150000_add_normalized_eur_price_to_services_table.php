<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->decimal('price_eur', 14, 4)->nullable()->after('currency');

            $table->index(
                ['status', 'deleted_at', 'price_eur', 'id'],
                'services_listing_price_eur_idx'
            );
            $table->index(
                ['status', 'deleted_at', 'km', 'id'],
                'services_listing_km_idx'
            );
            $table->index(
                ['status', 'deleted_at', 'an_fabricatie', 'id'],
                'services_listing_year_idx'
            );
            $table->index(
                ['status', 'deleted_at', 'putere', 'id'],
                'services_listing_power_idx'
            );
        });

        DB::table('services')
            ->where('currency', 'EUR')
            ->whereNotNull('price_value')
            ->update(['price_eur' => DB::raw('price_value')]);

        $this->backfillRonPricesFromLastValidRate();
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_listing_price_eur_idx');
            $table->dropIndex('services_listing_km_idx');
            $table->dropIndex('services_listing_year_idx');
            $table->dropIndex('services_listing_power_idx');
            $table->dropColumn('price_eur');
        });
    }

    private function backfillRonPricesFromLastValidRate(): void
    {
        if (! Schema::hasTable('exchange_rates')) {
            return;
        }

        $rate = DB::table('exchange_rates')
            ->where('source', 'BNR')
            ->where('base_currency', 'EUR')
            ->where('quote_currency', 'RON')
            ->orderByDesc('rate_date')
            ->value('rate');

        if (! is_numeric($rate) || (float) $rate <= 0) {
            return;
        }

        $rateSql = number_format((float) $rate, 8, '.', '');

        DB::table('services')
            ->where('currency', 'RON')
            ->whereNotNull('price_value')
            ->update([
                'price_eur' => DB::raw("ROUND(price_value / {$rateSql}, 4)"),
            ]);
    }
};
