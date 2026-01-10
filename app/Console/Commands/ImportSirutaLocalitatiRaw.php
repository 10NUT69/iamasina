<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportSirutaLocalitatiRaw extends Command
{
    protected $signature = 'import:siruta-localitati {--path= : Path relativ din storage/app} {--truncate : Goleste tabela inainte}';
    protected $description = 'Importa siruta_localitati.csv in siruta_localitati_raw';

    public function handle(): int
    {
        $relativePath = $this->option('path') ?: 'import/siruta_localitati.csv';
        $path = storage_path('app/' . $relativePath);

        if (!file_exists($path)) {
            $this->error("Nu gasesc fisierul: {$path}");
            return self::FAILURE;
        }

        if ($this->option('truncate')) {
            DB::table('siruta_localitati_raw')->truncate();
            $this->info("Tabela siruta_localitati_raw a fost golita.");
        }

        $handle = fopen($path, 'r');
        if (!$handle) {
            $this->error("Nu pot deschide fisierul: {$path}");
            return self::FAILURE;
        }

        $batch = [];
        $batchSize = 2000;
        $count = 0;

        // citim header
        $header = fgetcsv($handle, 0, ',');
        if (!$header || count($header) < 4) {
            $this->error("CSV invalid: nu are header corect.");
            fclose($handle);
            return self::FAILURE;
        }

        // ne asteptam la: siruta,denloc,jud,tip (dar acceptam si ordine diferita, daca exista)
        $map = array_flip(array_map(fn($h) => strtolower(trim($h)), $header));
        foreach (['siruta','denloc','jud','tip'] as $required) {
            if (!isset($map[$required])) {
                $this->error("CSV invalid: lipseste coloana obligatorie: {$required}");
                fclose($handle);
                return self::FAILURE;
            }
        }

        $lineNo = 1;
        $badCount = 0;

        while (($row = fgetcsv($handle, 0, ',')) !== false) {
            $lineNo++;

            if (!$row || count($row) < 4) {
                $badCount++;
                continue;
            }

            $siruta = trim($row[$map['siruta']] ?? '');
            $denloc = trim($row[$map['denloc']] ?? '');
            $jud    = trim($row[$map['jud']] ?? '');
            $tip    = trim($row[$map['tip']] ?? '');

            if ($siruta === '' || $denloc === '' || !is_numeric($jud) || !is_numeric($tip)) {
                $badCount++;
                continue;
            }

            $batch[] = [
                'siruta' => (int)$siruta,
                'denloc' => $denloc,
                'jud'    => (int)$jud,
                'tip'    => (int)$tip,
            ];

            if (count($batch) >= $batchSize) {
                DB::table('siruta_localitati_raw')->insertOrIgnore($batch);
                $count += count($batch);
                $batch = [];
                $this->info("Importate (procesate): {$count}");
            }
        }

        fclose($handle);

        if (!empty($batch)) {
            DB::table('siruta_localitati_raw')->insertOrIgnore($batch);
            $count += count($batch);
        }

        $dbCount = DB::table('siruta_localitati_raw')->count();

        $this->info("Gata. Randuri procesate: {$count}");
        $this->info("Randuri sarite (format aiurea): {$badCount}");
        $this->info("Randuri in DB (dupa insertOrIgnore): {$dbCount}");

        return self::SUCCESS;
    }
}
