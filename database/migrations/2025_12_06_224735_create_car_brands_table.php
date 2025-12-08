<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::create('car_brands', function (Blueprint $table) {
        $table->id();
        $table->string('name')->unique();
        $table->string('slug')->index(); // <--- AI NEVOIE DE LINIA ASTA
        $table->string('country')->nullable();
        $table->timestamps();
    });
}

    public function down(): void
    {
        Schema::dropIfExists('car_brands');
    }
};