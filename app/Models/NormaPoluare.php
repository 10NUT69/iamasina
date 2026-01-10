<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NormaPoluare extends Model
{
    protected $table = 'norme_poluare';
    public $timestamps = false;

    protected $fillable = ['nume'];
}
