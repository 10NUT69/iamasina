<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Câmpuri specifice pentru anunțuri auto
            $table->string('brand')->nullable();          // Marcă
            $table->string('model')->nullable();          // Model
            $table->integer('year')->nullable();          // An fabricație
            $table->integer('mileage')->nullable();       // Kilometraj (km)
            $table->string('fuel_type')->nullable();      // Combustibil (benzina, motorina etc.)
            $table->string('transmission')->nullable();   // Transmisie (manuala/automata)
            $table->string('body_type')->nullable();      // Caroserie (sedan, suv, etc.)
            $table->integer('power')->nullable();         // Putere (CP)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn([
                'brand',
                'model',
                'year',
                'mileage',
                'fuel_type',
                'transmission',
                'body_type',
                'power',
            ]);
        });
    }
};
