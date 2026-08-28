<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

            $table->unique(
                ['source', 'base_currency', 'quote_currency', 'rate_date'],
                'exchange_rates_source_pair_date_unique'
            );
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exchange_rates');
    }
};
