<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealer_feed_listings', function (Blueprint $table) {
            $table->char('photo_fingerprint', 64)->nullable();
            $table->boolean('feed_withdrawn')->default(false);
            $table->json('sync_gallery')->nullable();
        });

        Schema::table('dealer_feed_runs', function (Blueprint $table) {
            $table->json('sync_summary')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dealer_feed_listings', function (Blueprint $table) {
            $table->dropColumn(['photo_fingerprint', 'feed_withdrawn', 'sync_gallery']);
        });

        Schema::table('dealer_feed_runs', function (Blueprint $table) {
            $table->dropColumn('sync_summary');
        });
    }
};
