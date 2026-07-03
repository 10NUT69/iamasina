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
        if ($this->user_type !== 'dealer' || empty($this->company_name)) {
            return null;
        }

        return route('dealers.show', [
            'countySlug' => Str::slug($this->county ?: 'romania'),
            'citySlug' => Str::slug($this->city ?: 'romania'),
            'dealerSlug' => $this->dealer_route_slug,
        ]);
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
