<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailNotificationLog extends Model
{
    public const TYPE_MISSING_SERVICE_IMAGES = 'missing_service_images';

    public const STATUS_RESERVED = 'reserved';

    public const STATUS_SENT = 'sent';

    public const STATUS_FAILED = 'failed';

    protected $fillable = [
        'user_id',
        'notification_type',
        'period_date',
        'period_start',
        'period_end',
        'service_ids',
        'service_count',
        'status',
        'reserved_at',
        'sent_at',
        'failed_at',
        'error_message',
    ];

    protected $casts = [
        'period_date' => 'date',
        'period_start' => 'datetime',
        'period_end' => 'datetime',
        'service_ids' => 'array',
        'reserved_at' => 'datetime',
        'sent_at' => 'datetime',
        'failed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
