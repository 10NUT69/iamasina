<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class FixLocalitiesGeoFuzzy extends Command
{
    protected $signature = 'fix:localities-geo-fuzzy 
        {--limit=500 : Cate localitati lipsa sa proceseze}
        {--min=92 : Prag similaritate (0-100). Recomand 92}
        {--dry-run : Nu face update, doar raporteaza}';

    protected $description = 'Fuzzy match pentru localitati fara coordonate, folosind geonames_ro. Actualizeaza doar daca match-ul e suficient de sigur si face raport CSV.';

    private function norm(string $s): string
    {
        $s = trim(mb_strtolower($s, 'UTF-8'));
        $s = strtr($s, [
            'ă'=>'a','â'=>'a','î'=>'i','ș'=>'s','ş'=>'s','ț'=>'t','ţ'=>'t',
            'Ă'=>'a','Â'=>'a','Î'=>'i','Ș'=>'s','Ş'=>'s','Ț'=>'t','Ţ'=>'t',
        ]);
        // unificam separatori
        $s = str_replace(['-', '_', '.', ','], ' ', $s);
        $s = preg_replace('/\s+/', ' ', $s);
        // scoatem cuvinte frecvente care incurca
        $stop = ['sat', 'comuna', 'mun', 'municipiul', 'oras', 'oraș', 'judetul', 'județul'];
        $parts = array_values(array_filter(explode(' ', $s), fn($p) => $p !== '' && !in_array($p, $stop, true)));
        return implode(' ', $parts);
    }

    private function key3(string $s): string
    {
        $s = str_replace(' ', '', $s);
        return substr($s, 0, 3);
    }

    public function handle(): int
    {
        $limit = (int)$this->option('limit') ?: 500;
        $min = (int)$this->option('min') ?: 92;
        $dry = (bool)$this->option('dry-run');

        $missing = DB::table('localities')
            ->select('id', 'name', 'county_id')
            ->whereNull('latitude')
            ->orWhereNull('longitude')
            ->limit($limit)
            ->get();

        if ($missing->isEmpty()) {
            $this->info('Nu exista localitati fara coordonate. Gata.');
            return self::SUCCESS;
        }

        // incarcam candidatii GeoNames o singura data (18k e ok)
        $geo = DB::table('geonames_ro')
            ->select('geonameid','name','asciiname','latitude','longitude','population')
            ->get();

        // index pe prefix 3 litere ca sa nu comparam cu toate
        $buckets = [];
        foreach ($geo as $g) {
            $n1 = $this->norm($g->asciiname ?? '');
            $n2 = $this->norm($g->name ?? '');
            foreach ([$n1, $n2] as $n) {
                if ($n === '') continue;
                $k = $this->key3($n);
                if ($k === '') continue;
                $buckets[$k][] = [
                    'norm' => $n,
                    'lat'  => $g->latitude,
                    'lng'  => $g->longitude,
                    'pop'  => is_null($g->population) ? -1 : (int)$g->population,
                    'gid'  => (int)$g->geonameid,
                ];
            }
        }

        $reportPath = storage_path('app/import/missing_geo_report.csv');
        if (!is_dir(dirname($reportPath))) {
            @mkdir(dirname($reportPath), 0777, true);
        }
        $fh = fopen($reportPath, 'w');
        fputcsv($fh, ['id','name','county_id','best_geonameid','best_norm','score','updated']);

        $updated = 0;
        $stillMissing = 0;

        foreach ($missing as $loc) {
            $ln = $this->norm($loc->name);
            $bk = $this->key3($ln);

            $cands = $buckets[$bk] ?? [];
            if (empty($cands)) {
                fputcsv($fh, [$loc->id, $loc->name, $loc->county_id, '', '', 0, 0]);
                $stillMissing++;
                continue;
            }

            $best = null;
            $bestScore = -1;

            foreach ($cands as $c) {
                // similar_text e ok pentru set mic
                similar_text($ln, $c['norm'], $pct);
                $score = (int)round($pct);

                if ($score > $bestScore || ($score === $bestScore && $c['pop'] > ($best['pop'] ?? -1))) {
                    $bestScore = $score;
                    $best = $c;
                }
            }

            $didUpdate = 0;
            if ($best && $bestScore >= $min && $best['lat'] !== null && $best['lng'] !== null) {
                if (!$dry) {
                    DB::table('localities')
                        ->where('id', $loc->id)
                        ->update([
                            'latitude' => $best['lat'],
                            'longitude' => $best['lng'],
                        ]);
                }
                $updated++;
                $didUpdate = 1;
            } else {
                $stillMissing++;
            }

            fputcsv($fh, [
                $loc->id,
                $loc->name,
                $loc->county_id,
                $best['gid'] ?? '',
                $best['norm'] ?? '',
                $bestScore > 0 ? $bestScore : 0,
                $didUpdate
            ]);
        }

        fclose($fh);

        $stats = DB::table('localities')
            ->selectRaw("SUM(latitude IS NOT NULL AND longitude IS NOT NULL) AS matched,
                        SUM(latitude IS NULL OR longitude IS NULL) AS missing")
            ->first();

        $this->info("Updatate: {$updated}");
        $this->info("Ramase fara coordonate (din batch): {$stillMissing}");
        $this->info("Matched total: {$stats->matched}, Missing total: {$stats->missing}");
        $this->info("Raport: {$reportPath}");

        return self::SUCCESS;
    }
}
