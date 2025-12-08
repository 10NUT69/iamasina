<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        // 1. CAR BRANDS (Verificăm dacă există)
        if (!Schema::hasTable('car_brands')) {
            Schema::create('car_brands', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->string('slug')->nullable();
                $table->string('country')->nullable();
                $table->timestamps();
            });
        }

        // 2. CAR MODELS (Verificăm dacă există)
        if (!Schema::hasTable('car_models')) {
            Schema::create('car_models', function (Blueprint $table) {
                $table->id();
                $table->foreignId('brand_id')->constrained('car_brands')->onDelete('cascade');
                $table->string('name');
                $table->string('slug')->nullable();
                $table->timestamps();
            });
        }

        // 3. CAR GENERATIONS (Verificăm dacă există)
        if (!Schema::hasTable('car_generations')) {
            Schema::create('car_generations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('model_id')->constrained('car_models')->onDelete('cascade');
                $table->string('name');
                $table->integer('year_start')->nullable();
                $table->integer('year_end')->nullable();
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        // Ștergem în ordine inversă pentru a nu avea erori de chei străine
        Schema::dropIfExists('car_generations');
        Schema::dropIfExists('car_models');
        Schema::dropIfExists('car_brands');
    }
};