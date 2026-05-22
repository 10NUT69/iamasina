<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NormaPoluare extends Model
{
    protected $table = 'norme_poluare';
    public $timestamps = false;

    protected $fillable = ['nume', 'slug', 'sort_order'];

    protected $casts = [
        'sort_order' => 'integer',
    ];

    public function services()
    {
        return $this->hasMany(Service::class, 'norma_poluare_id');
    }

    public function scopeOrdered($query)
    {
        $table = $query->getModel()->getTable();

        return $query
            ->orderByRaw("CASE WHEN {$table}.sort_order >= 900 THEN 2 WHEN {$table}.sort_order IS NULL OR {$table}.sort_order = 0 THEN 1 ELSE 0 END")
            ->orderBy("{$table}.sort_order")
            ->orderBy("{$table}.nume");
    }
}
