<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Category extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'icon'
    ];

    // Relație: o categorie are multe servicii
    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
