<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (
            !Schema::hasTable('services')
            || !Schema::hasTable('localities')
            || !Schema::hasColumn('services', 'locality_id')
            || !Schema::hasColumn('services', 'city')
        ) {
            return;
        }

        DB::table('services')
            ->join('localities', 'services.locality_id', '=', 'localities.id')
            ->whereNotNull('services.locality_id')
            ->update([
                'services.city' => DB::raw('localities.name'),
            ]);
    }

    public function down(): void
    {
        // This migration only synchronizes a legacy fallback field.
    }
};
