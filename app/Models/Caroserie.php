<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Caroserie extends Model
{
    protected $table = 'caroserii';
    public $timestamps = false;
    protected $fillable = ['nume'];
}