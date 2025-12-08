<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarGeneration; // Noul model
use Illuminate\Support\Str;

class ImportCars extends Command
{
    protected $signature = 'import:cars';
    protected $description = 'Importa Marci, Modele si Generatii (Ani) din Autovit HTML';

    public function handle()
    {
        // Setări pentru performanță
        ini_set('memory_limit', '512M');
        ini_set('pcre.backtrack_limit', '100000000');

        $path = storage_path('app/autovit.html');
        
        if (!file_exists($path)) {
            $this->error('Fisierul autovit.html lipseste!');
            return;
        }

        $this->info('1. Citesc si procesez datele...');
        $html = file_get_contents($path);
        
        preg_match('/<script[^>]+id="__NEXT_DATA__"[^>]*>(.*?)<\/script>/s', $html, $matches);
        
        if (!isset($matches[1])) {
            $this->error('Nu am gasit JSON-ul.'); 
            return;
        }

        $data = json_decode($matches[1], true);
        if (!$data) { $this->error('JSON invalid.'); return; }

        // Structurile de date
        $filterMakeValues = $data['props']['pageProps']['filters']['543']['_meta']['values'] ?? [];
        $allModelsData = $data['props']['pageProps']['filtersValues'] ?? []; // Conține și modelele, și generațiile

        // Calculăm totalul pentru bara de progres
        $totalMakes = 0;
        foreach ($filterMakeValues as $group) {
            $totalMakes += count($group['group_values']);
        }

        $bar = $this->output->createProgressBar($totalMakes);
        $bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% -- %message%');
        $bar->start();

        foreach ($filterMakeValues as $group) {
            foreach ($group['group_values'] as $makeData) {
                
                // --- NIVEL 1: MARCA ---
                $makeNameClean = trim(preg_replace('/\s*\(\d+\)$/', '', $makeData['name']));
                $makeIdInternal = $makeData['value_key'];

                $bar->setMessage($makeNameClean);

                $brand = CarBrand::firstOrCreate(
                    ['name' => $makeNameClean],
                    ['slug' => Str::slug($makeNameClean)]
                );

                // --- NIVEL 2: MODELUL ---
                // Cheia pentru modele este "545:543:ID_MARCA"
                $modelKey = "545:543:" . $makeIdInternal;

                if (isset($allModelsData[$modelKey])) {
                    foreach ($allModelsData[$modelKey] as $modelGroup) {
                        foreach ($modelGroup['group_values'] as $modelData) {
                            
                            $modelNameClean = trim(preg_replace('/\s*\(\d+\)$/', '', $modelData['name']));
                            $modelIdInternal = $modelData['value_key']; // ID-ul intern al modelului

                            $model = CarModel::firstOrCreate(
                                ['car_brand_id' => $brand->id, 'name' => $modelNameClean], 
                                ['slug' => Str::slug($modelNameClean)]
                            );

                            // --- NIVEL 3: GENERAȚIA (ANII) ---
                            // Cheia pentru generații este "4005:545:ID_MODEL"
                            // 4005 este ID-ul filtrului "Generație" pe Autovit
                            $genKey = "4005:545:" . $modelIdInternal;

                            if (isset($allModelsData[$genKey])) {
                                foreach ($allModelsData[$genKey] as $genGroup) {
                                    foreach ($genGroup['group_values'] as $genData) {
                                        
                                        // Aici vine magia: Parsăm textul "II [2008 - 2016]"
                                        $rawName = $genData['name']; // ex: "II [2008 - 2016]"
                                        
                                        // Folosim Regex să extragem numele și anii
                                        // Caută ceva de genul: Nume [An1 - An2]
                                        if (preg_match('/^(.*?)\s*\[(\d{4})\s*-\s*(.*?)\]/', $rawName, $genMatches)) {
                                            $genName = trim($genMatches[1]); // "II"
                                            $yearStart = (int)$genMatches[2]; // 2008
                                            $yearEndRaw = $genMatches[3]; // "2016" sau "Prezent"
                                            
                                            $yearEnd = ($yearEndRaw === 'Prezent' || $yearEndRaw === 'prezent') ? null : (int)$yearEndRaw;

                                            // Dacă numele e gol (ex: "[2005 - 2010]"), punem anii ca nume
                                            if (empty($genName)) {
                                                $genName = "$yearStart - " . ($yearEnd ?? 'Prezent');
                                            }

                                            CarGeneration::firstOrCreate([
                                                'car_model_id' => $model->id,
                                                'name' => $genName,
                                            ], [
                                                'year_start' => $yearStart,
                                                'year_end' => $yearEnd
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
                $bar->advance();
            }
        }

        $bar->finish();
        $this->newLine(2);
        $this->info('IMPORT COMPLET CU GENERATII (ANI)!');
    }
}