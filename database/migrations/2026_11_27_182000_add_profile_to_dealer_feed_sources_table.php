<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealer_feed_sources', function (Blueprint $table) {
            $table->string('profile', 40)->default('wordpress-1');
        });
    }

    public function down(): void
    {
        Schema::table('dealer_feed_sources', function (Blueprint $table) {
            $table->dropColumn('profile');
        });
    }
};
