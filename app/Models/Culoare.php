<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Culoare extends Model
{
    protected $table = 'culori';
    public $timestamps = false;
    protected $fillable = ['nume'];
}