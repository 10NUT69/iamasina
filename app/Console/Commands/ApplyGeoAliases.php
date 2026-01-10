<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ApplyGeoAliases extends Command
{
    protected $signature = 'geo:apply-aliases';
    protected $description = 'Aplica alias-uri (localities.name -> geonames_ro) si completeaza latitude/longitude fara probleme de collation.';

    public function handle(): int
    {
        // alias-uri exacte: local name -> geonames search name
        $aliases = [
            'RAMETEA'   => 'Rimet',
            'NEDEICU'   => 'Nedelcu',
            'ISVOARELE' => 'Isvarna',
            'BIRA'      => 'Birda',
            'ALTINA'    => 'Altana',
            '23-Aug'    => '23 August',
            'CAVARAN'   => 'Cavnic',
            'SINOIE'    => 'Sinoie',
        ];

        $updated = 0;

        foreach ($aliases as $localName => $geoName) {
            $geo = DB::table('geonames_ro')
                ->select('latitude', 'longitude')
                ->where('asciiname', $geoName)
                ->orWhere('name', $geoName)
                ->first();

            if (!$geo) {
                $this->warn("Nu gasesc in geonames_ro: {$geoName} (pentru {$localName})");
                continue;
            }

            $affected = DB::table('localities')
                ->where('name', $localName)
                ->where(function ($q) {
                    $q->whereNull('latitude')->orWhereNull('longitude');
                })
                ->update([
                    'latitude' => $geo->latitude,
                    'longitude' => $geo->longitude,
                ]);

            if ($affected > 0) {
                $updated += $affected;
                $this->info("OK: {$localName} -> {$geoName}");
            }
        }

        $stats = DB::table('localities')
            ->selectRaw("SUM(latitude IS NOT NULL AND longitude IS NOT NULL) AS matched,
                        SUM(latitude IS NULL OR longitude IS NULL) AS missing")
            ->first();

        $this->info("Updatate: {$updated}");
        $this->info("Matched: {$stats->matched}, Missing: {$stats->missing}");

        // afisam ce mai lipseste (daca mai lipseste)
        if ((int)$stats->missing > 0) {
            $left = DB::table('localities')
                ->select('id', 'name', 'county_id')
                ->whereNull('latitude')
                ->orWhereNull('longitude')
                ->orderBy('county_id')
                ->orderBy('name')
                ->get();

            $this->warn("Ramase fara coordonate:");
            foreach ($left as $r) {
                $this->line(" - {$r->id} | {$r->name} | county_id={$r->county_id}");
            }
        }

        return self::SUCCESS;
    }
}
