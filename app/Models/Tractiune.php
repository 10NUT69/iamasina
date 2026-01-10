<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Tractiune extends Model
{
    protected $table = 'tractiuni';
    public $timestamps = false;

    protected $fillable = ['nume'];
}
