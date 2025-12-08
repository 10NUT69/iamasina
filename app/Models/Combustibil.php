<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Combustibil extends Model
{
    protected $table = 'combustibili'; // Numele tabelei din DB
    public $timestamps = false; // Nu avem created_at/updated_at aici
    protected $fillable = ['nume'];
}