<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    // Specificăm numele tabelei
    protected $table = 'car_brands';
    
    // Câmpurile pe care le putem scrie
    protected $fillable = ['name', 'slug', 'country'];

    public function models()
    {
        return $this->hasMany(CarModel::class, 'car_brand_id');
    }
}