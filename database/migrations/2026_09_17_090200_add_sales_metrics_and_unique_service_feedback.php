<?php

use Carbon\Carbon;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('service_deactivation_feedback', 'days_to_deactivate')) {
            Schema::table('service_deactivation_feedback', function (Blueprint $table) {
                $table->unsignedInteger('days_to_deactivate')->nullable()->after('service_published_at');
            });
        }

        // Păstrăm doar cea mai recentă dezactivare pentru fiecare anunț înainte
        // de a impune regula la nivelul bazei de date.
        $rows = DB::table('service_deactivation_feedback')
            ->whereNotNull('service_id')
            ->orderBy('service_id')
            ->orderByDesc('deactivated_at')
            ->orderByDesc('id')
            ->get(['id', 'service_id']);

        $seenServices = [];
        $duplicateIds = [];

        foreach ($rows as $row) {
            if (isset($seenServices[$row->service_id])) {
                $duplicateIds[] = $row->id;
                continue;
            }

            $seenServices[$row->service_id] = true;
        }

        foreach (array_chunk($duplicateIds, 500) as $ids) {
            DB::table('service_deactivation_feedback')->whereIn('id', $ids)->delete();
        }

        // Completează metrica pentru feedbackul deja existent.
        DB::table('service_deactivation_feedback')
            ->whereNull('days_to_deactivate')
            ->whereNotNull('deactivated_at')
            ->where(function ($query) {
                $query->whereNotNull('service_published_at')
                    ->orWhereNotNull('service_created_at');
            })
            ->orderBy('id')
            ->chunkById(500, function ($feedbackRows) {
                foreach ($feedbackRows as $row) {
                    $startDate = $row->service_published_at ?: $row->service_created_at;

                    if (! $startDate) {
                        continue;
                    }

                    $days = Carbon::parse($startDate)->diffInDays(Carbon::parse($row->deactivated_at));

                    DB::table('service_deactivation_feedback')
                        ->where('id', $row->id)
                        ->update(['days_to_deactivate' => $days]);
                }
            });

        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->unique('service_id', 'service_feedback_service_unique');
        });
    }

    public function down(): void
    {
        Schema::table('service_deactivation_feedback', function (Blueprint $table) {
            $table->dropUnique('service_feedback_service_unique');
        });

        if (Schema::hasColumn('service_deactivation_feedback', 'days_to_deactivate')) {
            Schema::table('service_deactivation_feedback', function (Blueprint $table) {
                $table->dropColumn('days_to_deactivate');
            });
        }
    }
};
