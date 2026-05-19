<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const COLUMNS = [
        'predare_leasing',
        'garantie',
        'km_reali',
        'prim_proprietar',
        'carte_service',
        'fiscal_pe_loc',
        'accept_schimb',
        'tva_deductibil',
    ];

    public function up(): void
    {
        Schema::table('services', function (Blueprint $table) {
            foreach (self::COLUMNS as $column) {
                if (!Schema::hasColumn('services', $column)) {
                    $table->boolean($column)->default(false);
                }
            }
        });
    }

    public function down(): void
    {
        Schema::table('services', function (Blueprint $table) {
            $columns = array_values(array_filter(
                self::COLUMNS,
                fn ($column) => Schema::hasColumn('services', $column)
            ));

            if ($columns !== []) {
                $table->dropColumn($columns);
            }
        });
    }
};
