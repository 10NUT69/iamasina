<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('car_generations', function (Blueprint $table) {
            $table->id();
            // Legătura cu Modelul (ex: Golf)
            $table->foreignId('car_model_id')->constrained('car_models')->onDelete('cascade');
            
            $table->string('name'); // ex: "VII" sau "B8"
            $table->integer('year_start')->nullable(); // ex: 2012
            $table->integer('year_end')->nullable();   // ex: 2019 (null dacă e "Prezent")
            
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('car_generations');
    }
};