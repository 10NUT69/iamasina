<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportGeoNamesRO extends Command
{
    protected $signature = 'import:geonames-ro {--truncate : Goleste tabela inainte}';
    protected $description = 'Importa RO.txt (GeoNames) in geonames_ro';

    public function handle(): int
    {
        $path = storage_path('app/import/RO.txt');

        if (!file_exists($path)) {
            $this->error("Nu gasesc RO.txt in storage/app/import/");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::table('geonames_ro')->truncate();
            $this->info("Tabela geonames_ro a fost golita.");
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error("Nu pot deschide fisierul RO.txt");
            return self::FAILURE;
        }

        $batch = [];
        $batchSize = 2000;
        $count = 0;

        while (($line = fgets($handle)) !== false) {
            $row = explode("\t", trim($line));

            // format GeoNames: avem nevoie doar de cateva coloane
            if (count($row) < 15) {
                continue;
            }

            // doar localitati populate
            if ($row[6] !== 'P') {
                continue;
            }

            $batch[] = [
                'geonameid'     => (int)$row[0],
                'name'          => $row[1],
                'asciiname'     => $row[2],
                'latitude'      => (float)$row[4],
                'longitude'     => (float)$row[5],
                'feature_class' => $row[6],
                'feature_code'  => $row[7],
                'country_code'  => $row[8],
                'admin1_code'   => $row[10] ?? null,
                'population'    => is_numeric($row[14]) ? (int)$row[14] : null,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('geonames_ro')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
                $this->info("Importate: {$count}");
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            DB::table('geonames_ro')->insertOrIgnore($batch);
            $count += count($batch);
        }

        $dbCount = DB::table('geonames_ro')->count();

        $this->info("Gata. Randuri procesate: {$count}");
        $this->info("Randuri in DB: {$dbCount}");

        return self::SUCCESS;
    }
}
