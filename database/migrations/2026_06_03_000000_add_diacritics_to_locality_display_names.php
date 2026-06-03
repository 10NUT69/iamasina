<?php

use App\Support\RomanianLocalityNames;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->applyNames(RomanianLocalityNames::displayNames());
    }

    public function down(): void
    {
        $this->applyNames(RomanianLocalityNames::asciiNames());
    }

    private function applyNames(array $names): void
    {
        if (!Schema::hasTable('localities')) {
            return;
        }

        $canSyncServices = Schema::hasTable('services')
            && Schema::hasColumn('services', 'locality_id')
            && Schema::hasColumn('services', 'city');

        $touchedCountyIds = [];

        DB::transaction(function () use ($names, $canSyncServices, &$touchedCountyIds): void {
            $localities = DB::table('localities')
                ->whereIn('siruta_code', array_keys($names))
                ->get(['id', 'siruta_code', 'county_id', 'name']);

            foreach ($localities as $locality) {
                $sirutaCode = (int) $locality->siruta_code;
                $name = $names[$sirutaCode] ?? null;

                if (!$name) {
                    continue;
                }

                if ($locality->name !== $name) {
                    DB::table('localities')
                        ->where('id', $locality->id)
                        ->update(['name' => $name]);
                }

                if ($canSyncServices) {
                    DB::table('services')
                        ->where('locality_id', $locality->id)
                        ->update(['city' => $name]);
                }

                $touchedCountyIds[(int) $locality->county_id] = true;
            }
        });

        foreach (array_keys($touchedCountyIds) as $countyId) {
            Cache::forget("iaauto:localities:county:{$countyId}:v1");
        }
    }
};
