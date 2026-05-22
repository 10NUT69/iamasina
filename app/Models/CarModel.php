<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarModel extends Model
{
    protected $table = 'car_models';
    
    protected $fillable = ['car_brand_id', 'name', 'slug', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function brand()
    {
        return $this->belongsTo(CarBrand::class, 'car_brand_id');
    }
	public function generations()
    {
        return $this->hasMany(CarGeneration::class, 'car_model_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'model_id');
    }

    public function scopeOrdered($query)
    {
        $table = $query->getModel()->getTable();

        return $query
            ->orderByRaw("CASE WHEN {$table}.sort_order IS NULL OR {$table}.sort_order = 0 THEN 1 ELSE 0 END")
            ->orderBy("{$table}.sort_order")
            ->orderBy("{$table}.name");
    }
}
