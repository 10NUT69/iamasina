<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dealer_feed_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_id')->constrained('dealer_feed_sources')->cascadeOnDelete();
            $table->foreignId('service_id')->unique()->constrained('services')->cascadeOnDelete();
            $table->string('external_id', 191);
            $table->char('external_key', 64);
            $table->timestamps();
            $table->unique(['source_id', 'external_key']);
        });

        Schema::table('dealer_feed_trials', function (Blueprint $table) {
            $table->foreignId('listing_id')->nullable()->constrained('dealer_feed_listings')->cascadeOnDelete();
        });

        DB::transaction(function () {
            $columns = ['id', 'source_id', 'service_id', 'external_id', 'external_key', 'created_at', 'updated_at'];
            DB::table('dealer_feed_listings')->insertUsing($columns, DB::table('dealer_feed_trials')->select($columns));
            DB::table('dealer_feed_trials')->update(['listing_id' => DB::raw('id')]);
        });

        Schema::table('dealer_feed_trials', function (Blueprint $table) {
            $table->unsignedBigInteger('listing_id')->nullable(false)->change();
            $table->unique('listing_id');
            $table->dropForeign(['source_id']);
            $table->dropForeign(['service_id']);
            $table->dropUnique(['source_id', 'external_key']);
            $table->dropUnique(['service_id']);
            $table->dropColumn(['source_id', 'service_id', 'external_id', 'external_key']);
        });
    }

    public function down(): void
    {
        $listingsWithoutTrial = DB::table('dealer_feed_listings')->whereNotIn('id', DB::table('dealer_feed_trials')->select('listing_id'));
        if ($listingsWithoutTrial->exists()) {
            throw new RuntimeException('Există anunțuri importate fără probă. Revenirea ar pierde proveniența lor.');
        }

        Schema::table('dealer_feed_trials', function (Blueprint $table) {
            $table->unsignedBigInteger('source_id')->nullable();
            $table->unsignedBigInteger('service_id')->nullable();
            $table->string('external_id', 191)->nullable();
            $table->char('external_key', 64)->nullable();
        });

        DB::transaction(function () {
            DB::table('dealer_feed_listings')->orderBy('id')->chunkById(500, function ($listings) {
                foreach ($listings as $listing) {
                    DB::table('dealer_feed_trials')->where('listing_id', $listing->id)->update([
                        'source_id' => $listing->source_id,
                        'service_id' => $listing->service_id,
                        'external_id' => $listing->external_id,
                        'external_key' => $listing->external_key,
                    ]);
                }
            });
        });

        Schema::table('dealer_feed_trials', function (Blueprint $table) {
            $table->unsignedBigInteger('source_id')->nullable(false)->change();
            $table->unsignedBigInteger('service_id')->nullable(false)->change();
            $table->string('external_id', 191)->nullable(false)->change();
            $table->char('external_key', 64)->nullable(false)->change();
            $table->foreign('source_id')->references('id')->on('dealer_feed_sources')->cascadeOnDelete();
            $table->foreign('service_id')->references('id')->on('services')->cascadeOnDelete();
            $table->unique(['source_id', 'external_key']);
            $table->unique('service_id');
            $table->dropForeign(['listing_id']);
            $table->dropUnique(['listing_id']);
            $table->dropColumn('listing_id');
        });

        Schema::dropIfExists('dealer_feed_listings');
    }
};
