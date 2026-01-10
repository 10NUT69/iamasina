<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportLocalitatiRaw extends Command
{
    protected $signature = 'import:localitati-raw 
        {--path= : Path relativ din storage/app (ex: import/orase.csv)} 
        {--truncate : Goleste tabela inainte}';

    protected $description = 'Importa localitati din CSV in localitati_raw (robust, fara LOCAL INFILE)';

    public function handle(): int
    {
        $relativePath = $this->option('path') ?: 'import/orase.csv';
        $path = storage_path('app/' . $relativePath);

        if (!file_exists($path)) {
            $this->error("Nu gasesc fisierul: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::table('localitati_raw')->truncate();
            $this->info("Tabela localitati_raw a fost golita.");
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error("Nu pot deschide fisierul: {$path}");
            return self::FAILURE;
        }

        $batch = [];
        $batchSize = 2000;
        $count = 0;
        $lineNo = 0;
        $badCount = 0;

        // salvam liniile problematice aici (ca sa le poti sterge / inspecta)
        $badPath = storage_path('app/import/bad_rows.csv');
        if (!is_dir(dirname($badPath))) {
            @mkdir(dirname($badPath), 0777, true);
        }
        $badHandle = fopen($badPath, 'w');

        while (($line = fgets($handle)) !== false) {
            $lineNo++;

            // curatare linie + BOM (unele CSV-uri au BOM)
            $line = preg_replace('/^\xEF\xBB\xBF/', '', $line);
            $line = trim($line);

            if ($line === '') {
                continue;
            }

            // 1) Incercam delimitatori posibili: , apoi ; apoi TAB
            $row = str_getcsv($line, ',');
            if (count($row) < 5) {
                $row = str_getcsv($line, ';');
            }
            if (count($row) < 5) {
                $row = str_getcsv($line, "\t");
            }

            // 2) Daca tot nu avem 5, o consideram stricata si o logam
            if (!$row || count($row) < 5) {
                $badCount++;
                if (is_resource($badHandle)) {
                    fputcsv($badHandle, [$lineNo, $line]);
                }
                continue;
            }

            // 3) Daca avem mai mult de 5 (ex: virgule in nume), lipim surplusul in "nume"
            if (count($row) > 5) {
                $extras = count($row) - 5; // cate coloane in plus
                $nameParts = array_slice($row, 0, 1 + $extras);
                $row = array_merge([implode(',', $nameParts)], array_slice($row, 1 + $extras));
            }

            // Normalize basic (trim)
            $nume      = trim($row[0] ?? '');
            $judet     = trim($row[1] ?? '');
            $judetAuto = trim($row[2] ?? '');
            $pop       = trim($row[3] ?? '');
            $regiune   = trim($row[4] ?? '');

            // Validare minima
            // - nume/judet/cod judet obligatorii
            // - cod judet 1-2 litere mari (AB, B etc.)
            if (
                $nume === '' ||
                $judet === '' ||
                $judetAuto === '' ||
                !preg_match('/^[A-Z]{1,2}$/', $judetAuto)
            ) {
                $badCount++;
                if (is_resource($badHandle)) {
                    fputcsv($badHandle, [$lineNo, $line]);
                }
                continue;
            }

            $batch[] = [
                'nume'       => $nume,
                'judet'      => $judet,
                'judet_auto' => $judetAuto,
                'populatie'  => is_numeric($pop) ? (int) $pop : null,
                'regiune'    => $regiune !== '' ? $regiune : null,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('localitati_raw')->insert($batch);
                $count += count($batch);
                $batch = [];
                $this->info("Importate: {$count}");
            }
        }

        fclose($handle);

        if (is_resource($badHandle)) {
            fclose($badHandle);
        }

        if (!empty($batch)) {
            DB::table('localitati_raw')->insert($batch);
            $count += count($batch);
        }

        $this->info("Gata. Total importate: {$count}");
        $this->info("Randuri sarite (problematice): {$badCount}");
        $this->info("Bad rows salvate in: {$badPath}");

        return self::SUCCESS;
    }
}
