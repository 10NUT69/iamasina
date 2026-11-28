<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dealer_feed_sources', function (Blueprint $table) {
            $table->json('location_overrides')->nullable();
            $table->dropConstrainedForeignId('fallback_locality_id');
        });
    }

    public function down(): void
    {
        Schema::table('dealer_feed_sources', function (Blueprint $table) {
            $table->foreignId('fallback_locality_id')->nullable()->constrained('localities')->nullOnDelete();
            $table->dropColumn('location_overrides');
        });
    }
};
