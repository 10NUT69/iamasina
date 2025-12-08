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
            // Marca & model (FK)
            if (!Schema::hasColumn('services', 'brand_id')) {
                $table->foreignId('brand_id')
                    ->nullable()
                    ->after('category_id')
                    ->constrained('brands')
                    ->nullOnDelete();
            }

            if (!Schema::hasColumn('services', 'model_id')) {
                $table->foreignId('model_id')
                    ->nullable()
                    ->after('brand_id')
                    ->constrained('car_models')
                    ->nullOnDelete();
            }

            // Caroserie – dacă o ai deja, nu o mai creăm
            if (!Schema::hasColumn('services', 'body_type')) {
                $table->string('body_type', 30)
                    ->nullable()
                    ->after('price_type');
            }

            // An fabricație
            if (!Schema::hasColumn('services', 'year_of_fabrication')) {
                $table->unsignedSmallInteger('year_of_fabrication')
                    ->nullable()
                    ->after('body_type');
            }

            // Cutie viteze
            if (!Schema::hasColumn('services', 'gearbox')) {
                $table->string('gearbox', 20)
                    ->nullable()
                    ->after('year_of_fabrication');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            // Drop foreign keys + coloane doar dacă există

            if (Schema::hasColumn('services', 'brand_id')) {
                // numele default: services_brand_id_foreign
                $table->dropForeign(['brand_id']);
                $table->dropColumn('brand_id');
            }

            if (Schema::hasColumn('services', 'model_id')) {
                // numele default: services_model_id_foreign
                $table->dropForeign(['model_id']);
                $table->dropColumn('model_id');
            }

            if (Schema::hasColumn('services', 'year_of_fabrication')) {
                $table->dropColumn('year_of_fabrication');
            }

            if (Schema::hasColumn('services', 'gearbox')) {
                $table->dropColumn('gearbox');
            }

            // body_type NU îl mai șterg, fiindcă exista deja înainte de această migrare
            // Dacă vrei să fie și el reversibil, putem să-l punem și aici.
        });
    }
};
