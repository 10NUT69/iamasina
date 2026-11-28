<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealer_feed_listings', function (Blueprint $table) {
            $table->timestamp('feed_missing_since')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('dealer_feed_listings', function (Blueprint $table) {
            $table->dropColumn('feed_missing_since');
        });
    }
};
