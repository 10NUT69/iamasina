<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CarBrand extends Model
{
    // Specificăm numele tabelei
    protected $table = 'car_brands';
    
    // Câmpurile pe care le putem scrie
    protected $fillable = ['name', 'slug', 'country', 'sort_order', 'is_popular'];

    protected $casts = [
        'is_popular' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function models()
    {
        return $this->hasMany(CarModel::class, 'car_brand_id');
    }

    public function services()
    {
        return $this->hasMany(Service::class, 'brand_id');
    }

    public function scopeOrdered($query)
    {
        $table = $query->getModel()->getTable();

        return $query
            ->orderByRaw("CASE WHEN {$table}.sort_order IS NULL OR {$table}.sort_order = 0 THEN 1 ELSE 0 END")
            ->orderBy("{$table}.sort_order")
            ->orderBy("{$table}.name");
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }
}
