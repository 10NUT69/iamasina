<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->index(
                ['status', 'deleted_at', 'created_at', 'id'],
                'services_listing_newest_idx'
            );
            $table->index(
                ['status', 'cutie_viteze_id', 'deleted_at', 'created_at', 'id'],
                'services_transmission_newest_idx'
            );
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropIndex('services_listing_newest_idx');
            $table->dropIndex('services_transmission_newest_idx');
        });
    }
};
