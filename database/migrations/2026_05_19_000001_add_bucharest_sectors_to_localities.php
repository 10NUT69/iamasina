<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private const BUCHAREST_SECTORS = [
        1 => 179141,
        2 => 179150,
        3 => 179169,
        4 => 179178,
        5 => 179187,
        6 => 179196,
    ];

    public function up(): void
    {
        DB::transaction(function (): void {
            $county = DB::table('counties')
                ->where('slug', 'bucuresti')
                ->orWhereIn('name', ['Bucuresti', 'București'])
                ->first();

            if (!$county) {
                return;
            }

            $sectorOne = DB::table('localities')
                ->where('county_id', $county->id)
                ->where(function ($query): void {
                    $query->where('slug', 'sector-1')
                        ->orWhere('slug', 'bucuresti')
                        ->orWhereIn('name', ['Sector 1', 'Bucuresti', 'București']);
                })
                ->orderByRaw("CASE WHEN slug = 'sector-1' THEN 0 WHEN slug = 'bucuresti' THEN 1 ELSE 2 END")
                ->first();

            if ($sectorOne) {
                DB::table('localities')
                    ->where('id', $sectorOne->id)
                    ->update([
                        'siruta_code' => self::BUCHAREST_SECTORS[1],
                        'type' => 9,
                        'name' => 'Sector 1',
                        'slug' => 'sector-1',
                    ]);
            } else {
                DB::table('localities')->insert([
                    'siruta_code' => self::BUCHAREST_SECTORS[1],
                    'type' => 9,
                    'county_id' => $county->id,
                    'name' => 'Sector 1',
                    'slug' => 'sector-1',
                    'latitude' => null,
                    'longitude' => null,
                ]);
            }

            foreach (array_slice(self::BUCHAREST_SECTORS, 1, null, true) as $sector => $sirutaCode) {
                $name = 'Sector ' . $sector;
                $slug = 'sector-' . $sector;

                $existing = DB::table('localities')
                    ->where('county_id', $county->id)
                    ->where(function ($query) use ($name, $slug): void {
                        $query->where('slug', $slug)->orWhere('name', $name);
                    })
                    ->first();

                if ($existing) {
                    DB::table('localities')
                        ->where('id', $existing->id)
                        ->update([
                            'siruta_code' => $sirutaCode,
                            'type' => 9,
                            'name' => $name,
                            'slug' => $slug,
                        ]);

                    continue;
                }

                DB::table('localities')->insert([
                    'siruta_code' => $sirutaCode,
                    'type' => 9,
                    'county_id' => $county->id,
                    'name' => $name,
                    'slug' => $slug,
                    'latitude' => null,
                    'longitude' => null,
                ]);
            }
        });
    }

    public function down(): void
    {
        DB::transaction(function (): void {
            $county = DB::table('counties')
                ->where('slug', 'bucuresti')
                ->orWhereIn('name', ['Bucuresti', 'București'])
                ->first();

            if (!$county) {
                return;
            }

            foreach (range(2, 6) as $sector) {
                $locality = DB::table('localities')
                    ->where('county_id', $county->id)
                    ->where('slug', 'sector-' . $sector)
                    ->first();

                if ($locality && !DB::table('services')->where('locality_id', $locality->id)->exists()) {
                    DB::table('localities')->where('id', $locality->id)->delete();
                }
            }

            $sectorOne = DB::table('localities')
                ->where('county_id', $county->id)
                ->where('slug', 'sector-1')
                ->first();

            $hasGenericBucharest = DB::table('localities')
                ->where('county_id', $county->id)
                ->where('slug', 'bucuresti')
                ->exists();

            if ($sectorOne && !$hasGenericBucharest) {
                DB::table('localities')
                    ->where('id', $sectorOne->id)
                    ->update([
                        'siruta_code' => 179132,
                        'type' => 9,
                        'name' => 'Bucuresti',
                        'slug' => 'bucuresti',
                    ]);
            }
        });
    }
};
