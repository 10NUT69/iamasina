<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Models\CarGeneration;

class CarController extends Controller
{
    // 1. Afișează pagina de căutare
    public function index()
    {
        $brands = CarBrand::orderBy('name')->get();
        return view('search', compact('brands'));
    }

    // 2. API pentru Modele
    public function getModels($brandId)
    {
        $models = CarModel::where('car_brand_id', $brandId)
                          ->orderBy('name')
                          ->get(['id', 'name']);
        return response()->json($models);
    }

    // 3. API pentru Generații (Ani)
    public function getGenerations($modelId)
    {
        $generations = CarGeneration::where('car_model_id', $modelId)
                            ->orderBy('year_start', 'desc')
                            ->get(['id', 'name', 'year_start', 'year_end']);
        
        $formatted = $generations->map(function($gen) {
            $years = " [" . $gen->year_start . " - " . ($gen->year_end ?? 'Prezent') . "]";
            $displayName = $gen->name; 
            
            if (strpos($displayName, (string)$gen->year_start) === false) {
                 $displayName .= $years;
            }

            return [
                'id' => $gen->id,
                'name' => $displayName
            ];
        });

        return response()->json($formatted);
    }
}