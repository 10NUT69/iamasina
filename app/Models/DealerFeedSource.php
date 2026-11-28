<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

class DealerFeedSource extends Model
{
    public const DEFAULT_PROFILE = 'wordpress-1';

    protected $fillable = ['dealer_id', 'name', 'url', 'profile', 'mapping'];

    protected $attributes = ['profile' => self::DEFAULT_PROFILE];

    protected $hidden = ['url'];

    protected function casts(): array
    {
        return [
            'dealer_id' => 'integer',
            'mapping' => 'array',
            'last_check' => 'array',
            'last_checked_at' => 'datetime',
            'location_overrides' => 'array',
        ];
    }

    public function dealer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dealer_id');
    }

    public function listings(): HasMany
    {
        return $this->hasMany(DealerFeedListing::class, 'source_id');
    }

    public function trials(): HasManyThrough
    {
        return $this->hasManyThrough(DealerFeedTrial::class, DealerFeedListing::class, 'source_id', 'listing_id');
    }

    public function runs(): HasMany
    {
        return $this->hasMany(DealerFeedRun::class, 'source_id');
    }

    public function scopeForAutomaticSync(Builder $query): Builder
    {
        return $query->whereHas('dealer', fn ($dealer) => $dealer->where('user_type', 'dealer')->where('is_active', true))
            ->whereHas('listings', fn ($listings) => $listings->where('import_status', 'published')
                ->whereHas('service', fn ($services) => $services->where(function ($state) {
                    $state->whereNull('services.deleted_at')
                        ->orWhere(function ($missing) {
                            $missing->where('services.status', Service::STATUS_DEACTIVATED)
                                ->whereColumn('services.deleted_at', 'dealer_feed_listings.feed_missing_since');
                        });
                })));
    }

    public function hasWorkInProgress(): bool
    {
        return $this->runs()->where('import_status', 'queued')->exists()
            || $this->listings()->where(fn ($query) => $query
                ->whereIn('import_status', ['waiting', 'processing'])
                ->orWhereIn('sync_gallery->status', ['waiting', 'processing']))->exists();
    }

    public function matchesFeedSettings(self $other): bool
    {
        return $this->dealer_id === $other->dealer_id
            && $this->url === $other->url
            && $this->profile === $other->profile
            && $this->mapping === $other->mapping
            && $this->location_overrides === $other->location_overrides
            && ($this->last_check['run_id'] ?? null) === ($other->last_check['run_id'] ?? null);
    }

    public function domain(): string
    {
        return parse_url($this->url, PHP_URL_HOST) ?: '';
    }

    public static function branchLocationKey(string $branch): string
    {
        return 'branch:'.hash('sha256', $branch);
    }
}
