<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->index(['created_at', 'ip'], 'visits_created_ip_idx');
            $table->index(['ip', 'created_at'], 'visits_ip_created_idx');
            $table->index(['created_at', 'url'], 'visits_created_url_idx');
            $table->index(['created_at', 'referer'], 'visits_created_referer_idx');
            $table->index(['created_at', 'device', 'browser'], 'visits_created_device_browser_idx');
            $table->index(['created_at', 'country', 'city'], 'visits_created_location_idx');
            $table->index(['user_id', 'created_at'], 'visits_user_created_idx');
        });
    }

    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            $table->dropIndex('visits_created_ip_idx');
            $table->dropIndex('visits_ip_created_idx');
            $table->dropIndex('visits_created_url_idx');
            $table->dropIndex('visits_created_referer_idx');
            $table->dropIndex('visits_created_device_browser_idx');
            $table->dropIndex('visits_created_location_idx');
            $table->dropIndex('visits_user_created_idx');
        });
    }
};
