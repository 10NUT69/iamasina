<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    protected $fillable = [
        'url',
        'ip',
        'user_agent',
        'referer',
        'country',
        'city',
        'device',
        'browser',
        'user_id',
    ];
}
