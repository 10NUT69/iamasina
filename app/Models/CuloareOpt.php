<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CuloareOpt extends Model
{
    protected $table = 'culoare_opt';
    public $timestamps = false;

    protected $fillable = ['nume'];
}
