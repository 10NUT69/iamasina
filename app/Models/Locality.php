<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Locality extends Model
{
    use HasFactory;

    protected $table = 'localities';

    protected $fillable = [
        'siruta_code',
        'type',
        'county_id',
        'name',
        'slug',
        'latitude',
        'longitude',
    ];

    protected $casts = [
        'latitude' => 'float',
        'longitude' => 'float',
    ];

    public function county()
    {
        return $this->belongsTo(County::class);
    }
}
