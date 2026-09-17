<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServiceDeactivationFeedback extends Model
{
    protected $table = 'service_deactivation_feedback';

    protected $fillable = [
        'service_id',
        'user_id',
        'answer',
        'sold_on',
        'completion_status',
        'survey_version',
        'deactivated_at',
        'title',
        'brand_id',
        'model_id',
        'brand_name',
        'model_name',
        'an_fabricatie',
        'km',
        'price_value',
        'currency',
        'price_eur',
        'category_id',
        'county_id',
        'locality_id',
        'county_name',
        'locality_name',
        'seller_type',
        'service_created_at',
        'service_published_at',
    ];

    protected $casts = [
        'deactivated_at' => 'datetime',
        'service_created_at' => 'datetime',
        'service_published_at' => 'datetime',
        'price_value' => 'decimal:4',
        'price_eur' => 'decimal:4',
    ];

    public function service()
    {
        return $this->belongsTo(Service::class)->withTrashed();
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
