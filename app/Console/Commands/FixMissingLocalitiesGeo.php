<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixMissingLocalitiesGeo extends Command
{
    protected $signature = 'fix:localities-geo {--limit=500 : Cate localitati sa proceseze}';
    protected $description = 'Completeaza latitude/longitude pentru localities lipsa, folosind geonames_ro (match ascname, apoi name, alegand populatia maxima).';

    private function norm(string $s): string
    {
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $map = [
            'ă' => 'a', 'â' => 'a', 'î' => 'i', 'ș' => 's', 'ş' => 's', 'ț' => 't', 'ţ' => 't',
            'Ă' => 'a', 'Â' => 'a', 'Î' => 'i', 'Ș' => 's', 'Ş' => 's', 'Ț' => 't', 'Ţ' => 't',
        ];
        $s = strtr($s, $map);
        // spatii multiple -> unul
        $s = preg_replace('/\s+/', ' ', $s);
        return $s;
    }

    public function handle(): int
    {
        $limit = (int)$this->option('limit') ?: 500;

        // luam doar ce lipseste
        $missing = DB::table('localities')
            ->select('id', 'name')
            ->whereNull('latitude')
            ->orWhereNull('longitude')
            ->limit($limit)
            ->get();

        if ($missing->isEmpty()) {
            $this->info('Nu exista localitati fara coordonate. Gata.');
            return self::SUCCESS;
        }

        $names = $missing->map(fn($r) => $this->norm($r->name))->unique()->values()->all();

        // luam din geonames doar candidatii relevanti (asciiname sau name)
        $candidates = DB::table('geonames_ro')
            ->select('geonameid', 'name', 'asciiname', 'latitude', 'longitude', 'population')
            ->whereIn(DB::raw('LOWER(TRIM(asciiname))'), $names)
            ->orWhereIn(DB::raw('LOWER(TRIM(name))'), $names)
            ->get();

        // indexam candidatii pe cheie normalizata, alegand pe cel cu populatie maxima
        $best = [];
        foreach ($candidates as $c) {
            $keys = [
                $this->norm($c->asciiname ?? ''),
                $this->norm($c->name ?? ''),
            ];
            foreach ($keys as $k) {
                if ($k === '') continue;
                $pop = is_null($c->population) ? -1 : (int)$c->population;
                if (!isset($best[$k]) || $pop > $best[$k]['pop']) {
                    $best[$k] = [
                        'lat' => $c->latitude,
                        'lng' => $c->longitude,
                        'pop' => $pop,
                    ];
                }
            }
        }

        $updated = 0;
        foreach ($missing as $loc) {
            $k = $this->norm($loc->name);
            if (!isset($best[$k])) {
                continue;
            }

            DB::table('localities')
                ->where('id', $loc->id)
                ->update([
                    'latitude' => $best[$k]['lat'],
                    'longitude' => $best[$k]['lng'],
                ]);

            $updated++;
        }

        $this->info("Procesate: {$missing->count()}, updatate: {$updated}");

        // afisam status
        $stats = DB::table('localities')
            ->selectRaw("SUM(latitude IS NOT NULL AND longitude IS NOT NULL) AS matched,
                        SUM(latitude IS NULL OR longitude IS NULL) AS missing")
            ->first();

        $this->info("Matched: {$stats->matched}, Missing: {$stats->missing}");

        return self::SUCCESS;
    }
}
