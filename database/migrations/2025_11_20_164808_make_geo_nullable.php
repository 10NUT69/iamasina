<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Aici facem coloanele să accepte valori NULL.
     */
    public function up(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Folosim ->change() pentru a modifica coloanele existente
            $table->string('country')->nullable()->change();
            $table->string('city')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     * Aici revenim la starea inițială (fără NULL), în caz de rollback.
     */
    public function down(): void
    {
        Schema::table('visits', function (Blueprint $table) {
            // Revenim la setarea "NOT NULL"
            $table->string('country')->nullable(false)->change();
            $table->string('city')->nullable(false)->change();
        });
    }
};