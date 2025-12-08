<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class County extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug'
    ];

    // Relație: un județ are multe servicii
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
