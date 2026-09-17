<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->boolean('is_current')->default(true)->index();
        });

        // Răspunsurile vechi ale anunțurilor deja reactivate nu sunt curente.
        DB::table('service_deactivation_feedback')
            ->whereIn('service_id', DB::table('services')
                ->select('id')
                ->where('status', 'active')
                ->whereNull('deleted_at'))
            ->update(['is_current' => false]);
    }

    public function down(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->dropColumn('is_current');
        });
    }
};
