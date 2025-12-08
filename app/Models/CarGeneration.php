<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarGeneration extends Model
{
    protected $table = 'car_generations';
    
    protected $fillable = ['car_model_id', 'name', 'year_start', 'year_end'];

    public function model()
    {
        return $this->belongsTo(CarModel::class, 'car_model_id');
    }
}