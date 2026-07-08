<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    public const DEALER_TIER_STANDARD = 'standard';
    public const DEALER_TIER_FOUNDING = 'founding';
    public const DEALER_TIER_PREMIUM = 'premium';

    public const DEALER_TIERS = [
        self::DEALER_TIER_STANDARD,
        self::DEALER_TIER_FOUNDING,
        self::DEALER_TIER_PREMIUM,
    ];

    public const DEALER_TIER_LABELS = [
        self::DEALER_TIER_STANDARD => 'Standard',
        self::DEALER_TIER_FOUNDING => 'Fondator',
        self::DEALER_TIER_PREMIUM => 'Premium',
    ];

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
    'name',
    'email',
    'password',

    // flags
    'is_admin',
    'is_active',

    // tip cont
    'user_type',
    'dealer_tier',

    // date firmă (dealer)
    'company_name',
    'dealer_slug',
    'dealer_description',
    'dealer_gallery',
    'dealer_logo',
    'cui',
    'phone',
    'phone_2',
    'phone_3',
    'county',
    'county_id',
    'city',
    'locality_id',
    'address',
];


    /**
     * Hidden attributes
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_active' => 'boolean',
            'dealer_gallery' => 'array',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (User $user) {
            if (static::hasDealerTierColumn() && ! in_array($user->dealer_tier, self::DEALER_TIERS, true)) {
                $user->dealer_tier = self::DEALER_TIER_STANDARD;
            }

            if (! static::hasDealerSlugColumn()) {
                unset($user->attributes['dealer_slug']);
                return;
            }

            if ($user->user_type !== 'dealer' || empty($user->company_name)) {
                $user->dealer_slug = null;
                return;
            }

            if (!$user->dealer_slug || $user->isDirty('company_name') || $user->isDirty('user_type')) {
                $user->dealer_slug = static::makeUniqueDealerSlug($user->company_name, $user->id);
            }
        });
    }

    /* ========================================
     *        RELAȚIE FAVORITE
     * ======================================== */
    public function favorites()
    {
        return $this->hasMany(Favorite::class);
    }

    public function savedSearches()
    {
        return $this->hasMany(SavedSearch::class);
    }

    public function dealerCounty()
    {
        return $this->belongsTo(County::class, 'county_id');
    }

    public function dealerLocality()
    {
        return $this->belongsTo(Locality::class, 'locality_id');
    }

    /* ========================================
     *        RELAȚIE ANUNȚURI
     * ======================================== */
    public function services()
    {
        return $this->hasMany(Service::class);
    }

    public function emailNotificationLogs()
    {
        return $this->hasMany(EmailNotificationLog::class);
    }

    public function buyerConversations()
    {
        return $this->hasMany(Conversation::class, 'buyer_id');
    }

    public function sellerConversations()
    {
        return $this->hasMany(Conversation::class, 'seller_id');
    }

    public function sentMessages()
    {
        return $this->hasMany(Message::class, 'sender_id');
    }

    public static function findDealerByRouteSlug(string $dealerSlug): ?self
    {
        $dealerQuery = static::query()->where('user_type', 'dealer');

        if (static::hasDealerSlugColumn()) {
            $dealer = (clone $dealerQuery)
                ->where('dealer_slug', $dealerSlug)
                ->first();

            if ($dealer) {
                return $dealer;
            }

            $dealerQuery->where(function ($query) use ($dealerSlug) {
                $query->whereNull('dealer_slug')
                    ->orWhere('dealer_slug', '');
            });
        }

        return $dealerQuery
            ->get()
            ->first(fn (User $user) => $user->dealer_route_slug === $dealerSlug);
    }

    public function getDealerRouteSlugAttribute(): ?string
    {
        if ($this->user_type !== 'dealer' || empty($this->company_name)) {
            return null;
        }

        if (static::hasDealerSlugColumn() && ! empty($this->dealer_slug)) {
            return $this->dealer_slug;
        }

        return static::makeDealerSlugBase($this->company_name);
    }

    public static function dealerTierLabel(?string $tier): string
    {
        return self::DEALER_TIER_LABELS[$tier] ?? self::DEALER_TIER_LABELS[self::DEALER_TIER_STANDARD];
    }

    public function getDealerTierLabelAttribute(): string
    {
        return self::dealerTierLabel($this->dealer_tier);
    }

    public function getHasSpecialDealerTierAttribute(): bool
    {
        return $this->user_type === 'dealer'
            && in_array($this->dealer_tier, [self::DEALER_TIER_FOUNDING, self::DEALER_TIER_PREMIUM], true);
    }

    public function getDealerPublicUrlAttribute(): ?string
    {
        $path = $this->dealer_public_path;

        return $path ? url($path) : null;
    }

    public function getDealerCanonicalUrlAttribute(): ?string
    {
        $path = $this->dealer_public_path;

        if (!$path) {
            return null;
        }

        return rtrim((string) config('app.url'), '/') . $path;
    }

    public function getDealerPublicPathAttribute(): ?string
    {
        if ($this->user_type !== 'dealer' || empty($this->company_name)) {
            return null;
        }

        $dealerLocality = $this->relationLoaded('dealerLocality')
            ? $this->getRelation('dealerLocality')
            : null;
        $dealerCounty = $this->relationLoaded('dealerCounty')
            ? $this->getRelation('dealerCounty')
            : null;

        if (!$dealerLocality && ! empty($this->locality_id)) {
            $dealerLocality = Locality::query()
                ->select('id', 'county_id', 'slug')
                ->find($this->locality_id);
        }

        if (!$dealerCounty && ! empty($this->county_id)) {
            $dealerCounty = County::query()
                ->select('id', 'slug')
                ->find($this->county_id);
        }

        if (!$dealerCounty && $dealerLocality?->county_id) {
            $dealerCounty = County::query()
                ->select('id', 'slug')
                ->find($dealerLocality->county_id);
        }

        $countySlug = $dealerCounty?->slug ?: Str::slug($this->county ?: 'romania');
        $citySlug = $dealerLocality?->slug ?: $this->dealerCityFallbackSlug($countySlug);

        return route('dealers.show', [
            'countySlug' => $countySlug,
            'citySlug' => $citySlug,
            'dealerSlug' => $this->dealer_route_slug,
        ], false);
    }

    public function getDealerGalleryUrlsAttribute(): array
    {
        return collect($this->dealer_gallery ?: [])
            ->filter()
            ->map(fn ($path) => asset('storage/' . ltrim($path, '/')))
            ->values()
            ->all();
    }

    public function getDealerLogoUrlAttribute(): ?string
    {
        if ($this->user_type !== 'dealer' || empty($this->dealer_logo)) {
            return null;
        }

        if (Str::startsWith($this->dealer_logo, ['http://', 'https://'])) {
            return $this->dealer_logo;
        }

        return asset('storage/' . ltrim($this->dealer_logo, '/'));
    }

    public function getIsActiveAttribute($value): bool
    {
        return array_key_exists('is_active', $this->attributes)
            ? (bool) $value
            : true;
    }

    private static function makeUniqueDealerSlug(string $companyName, ?int $ignoreId = null): string
    {
        $base = static::makeDealerSlugBase($companyName);
        $slug = $base;
        $counter = 2;

        if (! static::hasDealerSlugColumn()) {
            return $slug;
        }

        while (static::query()
            ->where('dealer_slug', $slug)
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $slug = $base . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    private static function makeDealerSlugBase(string $companyName): string
    {
        return Str::slug($companyName) ?: 'parc-auto';
    }

    private function dealerCityFallbackSlug(string $countySlug): string
    {
        $city = trim((string) $this->city);

        if ($city === '') {
            return 'romania';
        }

        $county = trim((string) $this->county);
        $cityParts = array_values(array_filter(array_map('trim', explode(',', $city))));

        if ($county !== '' && count($cityParts) > 1) {
            $lastCityPart = end($cityParts);

            if ($lastCityPart && Str::slug($lastCityPart) === $countySlug) {
                array_pop($cityParts);
                $city = implode(', ', $cityParts) ?: $city;
            }
        }

        $citySlug = Str::slug($city);
        $countySuffix = '-' . $countySlug;

        if ($countySlug !== '' && Str::endsWith($citySlug, $countySuffix)) {
            $withoutCounty = Str::beforeLast($citySlug, $countySuffix);
            $citySlug = $withoutCounty !== '' ? $withoutCounty : $citySlug;
        }

        return $citySlug ?: 'romania';
    }

    private static function hasDealerSlugColumn(): bool
    {
        static $hasColumn = null;

        return $hasColumn ??= Schema::hasColumn((new static())->getTable(), 'dealer_slug');
    }

    private static function hasDealerTierColumn(): bool
    {
        static $hasColumn = null;

        return $hasColumn ??= Schema::hasColumn((new static())->getTable(), 'dealer_tier');
    }
}
