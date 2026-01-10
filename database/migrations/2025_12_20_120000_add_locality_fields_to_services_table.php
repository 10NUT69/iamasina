<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->foreignId('locality_id')->nullable()->after('county_id')->constrained('localities')->nullOnDelete();
            $table->decimal('latitude', 10, 7)->nullable()->after('locality_id');
            $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropForeign(['locality_id']);
            $table->dropColumn(['locality_id', 'latitude', 'longitude']);
        });
    }
};
