<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class BackupExport extends Model
{
    public const TYPE_MEDIA = 'media';

    public const STATUS_PENDING = 'pending';
    public const STATUS_PROCESSING = 'processing';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DELETED = 'deleted';

    public const ACTIVE_MEDIA_KEY = 'media';

    protected $fillable = [
        'type',
        'status',
        'filename',
        'relative_path',
        'size',
        'started_at',
        'completed_at',
        'error_message',
        'active_key',
    ];

    protected function casts(): array
    {
        return [
            'size' => 'integer',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function scopeMedia(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MEDIA);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', [
            self::STATUS_PENDING,
            self::STATUS_PROCESSING,
        ]);
    }
}
