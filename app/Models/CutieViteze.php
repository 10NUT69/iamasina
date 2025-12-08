<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class CutieViteze extends Model
{
    protected $table = 'cutii_viteze';
    public $timestamps = false;
    protected $fillable = ['nume'];
}