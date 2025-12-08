<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Brand extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'sort_order',
    ];

    public function carModels()
    {
        return $this->hasMany(CarModel::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
