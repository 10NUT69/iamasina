<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $table = 'car_models';
    
    protected $fillable = ['car_brand_id', 'name', 'slug'];

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }
	public function generations()
    {
        return $this->hasMany(CarGeneration::class, 'car_model_id');
    }
}