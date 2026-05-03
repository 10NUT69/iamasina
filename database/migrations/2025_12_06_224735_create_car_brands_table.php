<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    if (!Schema::hasTable('car_brands')) {
        Schema::create('car_brands', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('slug')->index();
            $table->string('country')->nullable();
            $table->timestamps();
        });
    }
}

    public function down(): void
    {
        // The initial car_brands migration owns this table.
    }
};
