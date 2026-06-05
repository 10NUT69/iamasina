<?php

namespace App\Models;
use App\Models\CarBrand;
use App\Models\CarModel;
use App\Support\ServiceImageStorage;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Service extends Model
{
    use HasFactory, SoftDeletes;

    private const DEFAULT_AUTO_IMAGE = 'images/defaults/auto-de-vanzare-iaauto-default.webp';
    private const AUTO_DEFAULT_CATEGORY_SLUGS = ['autoturisme', 'servicii-auto'];

    public const FEATURE_OPTIONS = [
        'filtru_particule' => 'Filtru particule',
        'importata' => 'Importată',
        'avariata' => 'Avariată',
        'predare_leasing' => 'Predare leasing',
        'garantie' => 'Garanție',
        'km_reali' => 'Km reali',
        'prim_proprietar' => 'Prim proprietar',
        'carte_service' => 'Carte service',
        'fiscal_pe_loc' => 'Fiscal pe loc',
        'accept_schimb' => 'Accept schimb',
        'tva_deductibil' => 'TVA deductibil',
    ];

    public const IMPORTANT_DETAIL_OPTIONS = [
        ['key' => 'km_reali', 'label' => 'Km reali', 'type' => 'positive', 'icon' => 'gauge'],
        ['key' => 'garantie', 'label' => 'Garanție', 'type' => 'positive', 'icon' => 'shield'],
        ['key' => 'carte_service', 'label' => 'Carte service', 'type' => 'positive', 'icon' => 'clipboard'],
        ['key' => 'prim_proprietar', 'label' => 'Prim proprietar', 'type' => 'positive', 'icon' => 'user'],
        ['key' => 'fiscal_pe_loc', 'label' => 'Fiscal pe loc', 'type' => 'positive', 'icon' => 'receipt'],
        ['key' => 'accept_schimb', 'label' => 'Accept schimb', 'type' => 'normal', 'icon' => 'arrows'],
        ['key' => 'predare_leasing', 'label' => 'Predare leasing', 'type' => 'normal', 'icon' => 'key'],
        ['key' => 'tva_deductibil', 'label' => 'TVA deductibil', 'type' => 'normal', 'icon' => 'file'],
        ['key' => 'importata', 'label' => 'Importată', 'type' => 'normal', 'icon' => 'globe'],
        ['key' => 'filtru_particule', 'label' => 'Filtru particule', 'type' => 'normal', 'icon' => 'filter'],
        ['key' => 'avariata', 'label' => 'Avariată', 'type' => 'warning', 'icon' => 'warning'],
    ];

    protected $fillable = [
        'user_id',
        'category_id',
        'county_id',
        'locality_id',
        'latitude',
        'longitude',
        'title',
        'slug',
        'description',
        'price_value',
        'price_type',
        'currency',
        'contact_name',
        'phone',
        'email',
        'images',
        'status',
        'views',
        'published_at',
        'expires_at',

        // câmpuri specifice pentru anunțuri auto
        'brand',
        'model',
		'car_generation_id',
        'vin',
        'an_fabricatie',
        'km',
        'capacitate_cilindrica',
        'putere',
        'combustibil_id',
        'cutie_viteze_id',
        'caroserie_id',
        'culoare_id',
		'tractiune_id',
		'norma_poluare_id',
'numar_usi',
'numar_locuri',
'importata',
'avariata',
'filtru_particule',
'predare_leasing',
'garantie',
'km_reali',
'prim_proprietar',
'carte_service',
'fiscal_pe_loc',
'accept_schimb',
'tva_deductibil',
'culoare_opt_id',
'brand_id',
'model_id',



    ];

    protected $casts = [
        'images' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_value' => 'float',
        'latitude' => 'float',
        'longitude' => 'float',

        // cast pentru câmpurile numerice auto
        
		'an_fabricatie' => 'integer',
        'km' => 'integer',
        'capacitate_cilindrica' => 'integer',
        'putere' => 'integer',
		'importata'        => 'boolean',
'avariata'         => 'boolean',
'filtru_particule' => 'boolean',
'predare_leasing'  => 'boolean',
'garantie'         => 'boolean',
'km_reali'         => 'boolean',
'prim_proprietar'  => 'boolean',
'carte_service'    => 'boolean',
'fiscal_pe_loc'    => 'boolean',
'accept_schimb'    => 'boolean',
'tva_deductibil'   => 'boolean',
'numar_usi'        => 'integer',
'numar_locuri'     => 'integer',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function county() { return $this->belongsTo(County::class); }
    public function locality() { return $this->belongsTo(Locality::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }
    public function conversations() { return $this->hasMany(Conversation::class); }

    public function isFavoritedBy($user)
    {
        if (!$user) return false;
        return $this->favorites()->where('user_id', $user->id)->exists();
    }

    // LOGICA NUME AUTOR
    public function getAuthorNameAttribute()
    {
        if ($this->user) {
            if (!empty($this->user->name) && $this->user->name !== 'Anonymous' && $this->user->name !== 'Vizitator') {
                return $this->user->name;
            }
            $parts = explode('@', $this->user->email);
            return ucfirst($parts[0]);
        }

        $guestName = $this->getAttribute('contact_name');
        if (!empty($guestName)) return $guestName;

        if (!empty($this->email)) {
            $parts = explode('@', $this->email);
            return ucfirst($parts[0]);
        }

        return 'Vizitator';
    }

    public function getAuthorInitialAttribute()
    {
        return strtoupper(substr($this->author_name, 0, 1));
    }

    public function getActiveFeatureOptionsAttribute(): array
    {
        return collect(self::FEATURE_OPTIONS)
            ->filter(fn ($label, $field) => (bool) $this->getAttribute($field))
            ->all();
    }

    public function getImportantDetailsAttribute(): array
    {
        return collect(self::IMPORTANT_DETAIL_OPTIONS)
            ->filter(fn ($item) => (bool) $this->getAttribute($item['key']))
            ->values()
            ->all();
    }

    // SMART SLUG
    public function getSmartSlugAttribute()
    {
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $this->title));
        $words = explode(' ', $cleanTitle);
        $firstThreeWords = array_slice($words, 0, 3);
        $slugString = implode(' ', $firstThreeWords);
        return Str::slug($slugString);
    }

    // PUBLIC URL: /anunturi-auto-de-vanzare/{brand}/{model}/{county}/{city}/{slug}-{id}
   public function getPublicUrlAttribute()
{
    $locality = $this->relationLoaded('locality') ? $this->getRelation('locality') : $this->locality;
    $county = $this->relationLoaded('county') ? $this->getRelation('county') : $this->county;

    if (!$county && $locality) {
        $county = $locality->relationLoaded('county') ? $locality->getRelation('county') : $locality->county;
    }

    $countySlug = $county?->slug ?? 'romania';

    $citySlug = $locality?->slug
        ?: (!empty($this->city) ? Str::slug($this->city) : null)
        ?: $countySlug;

    $brandSlug = null;
    $modelSlug = null;

    if ($this->brand_id && $this->model_id) {
        $brand = $this->relationLoaded('brandRel') ? $this->getRelation('brandRel') : $this->brandRel;
        $model = $this->relationLoaded('modelRel') ? $this->getRelation('modelRel') : $this->modelRel;

        if ($brand && $model) {
            $brandSlug = $brand->slug ?: Str::slug($brand->name);
            $modelSlug = $model->slug ?: Str::slug($model->name);
        }
    }

    if (!$brandSlug || !$modelSlug) {
        if (!empty($this->brand)) {
            $brandSlug = Str::slug($this->brand);
        }
        if (!empty($this->model)) {
            $modelSlug = Str::slug($this->model);
        }
    }

    $generation = null;
    if ((!$brandSlug || !$modelSlug) && $this->car_generation_id) {
        $generation = $this->relationLoaded('generation') ? $this->getRelation('generation') : $this->generation;
    }

    if ($generation) {
        $model = $generation->relationLoaded('model') ? $generation->getRelation('model') : $generation->model;
        $brand = $model
            ? ($model->relationLoaded('brand') ? $model->getRelation('brand') : $model->brand)
            : null;

        if ($brand && $model) {
            $brandSlug = $brand->slug ?: Str::slug($brand->name);
            $modelSlug = $model->slug ?: Str::slug($model->name);
        }
    }

    if ($brandSlug && $modelSlug && $citySlug) {
        return route('service.show.car', [
            'brandSlug'  => $brandSlug,
            'modelSlug'  => $modelSlug,
            'countySlug' => $countySlug,
            'citySlug'   => $citySlug,
            'slug'       => $this->slug ?: $this->smart_slug,
            'id'         => $this->id,
        ]);
    }

    return url('/');
}

    public function getListingDateAttribute()
    {
        if ($this->published_at && $this->created_at) {
            return $this->created_at->greaterThan($this->published_at)
                ? $this->created_at
                : $this->published_at;
        }

        return $this->published_at ?: $this->created_at;
    }

    public function getListingDateLabelAttribute(): string
    {
        $listingDate = $this->listing_date;

        if (!$listingDate) {
            return '-';
        }

        if ($listingDate->isToday()) {
            return 'Astăzi ' . $listingDate->format('H:i');
        }

        if ($listingDate->isYesterday()) {
            return 'Ieri ' . $listingDate->format('H:i');
        }

        return $listingDate->translatedFormat('d M Y');
    }

    public function getImageAltAttribute(): string
    {
        $brandName = $this->relationLoaded('brandRel')
            ? $this->brandRel?->name
            : null;
        $brandName = $brandName ?: ($this->brand ?? null);

        $modelName = $this->relationLoaded('modelRel')
            ? $this->modelRel?->name
            : null;
        $modelName = $modelName ?: ($this->model ?? null);

        if ((!$brandName || !$modelName) && $this->relationLoaded('generation')) {
            $generation = $this->generation;
            $generationModel = $generation?->relationLoaded('model') ? $generation->model : null;
            $generationBrand = $generationModel?->relationLoaded('brand') ? $generationModel->brand : null;

            $brandName = $brandName ?: $generationBrand?->name;
            $modelName = $modelName ?: $generationModel?->name;
        }

        $vehicleLabel = trim(implode(' ', array_filter([
            $brandName,
            $modelName,
            $this->an_fabricatie,
        ])));

        $title = trim((string) $this->title);
        $label = $title !== '' ? $title : ($vehicleLabel ?: 'Autoturism');

        $normalizedLabel = Str::lower(Str::ascii($label));
        if (!str_contains($normalizedLabel, 'de vanzare')) {
            $label .= ' de vanzare';
        }

        $countyName = $this->relationLoaded('county') ? $this->county?->name : null;
        $localityName = $this->relationLoaded('locality')
            ? $this->locality?->name
            : ($this->city ?? null);

        $location = trim(implode(', ', array_filter([$localityName, $countyName])));
        if ($location !== '') {
            $label .= ' in ' . $location;
        }

        return trim(preg_replace('/\s+/', ' ', $label));
    }


    // ==========================================
    // 🖼️ IMAGINI (LOGICA DE DEFAULT CATEGORIE)
    // ==========================================
    public function getMainImageUrlAttribute()
    {
        $images = $this->images;
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        // 1. Dacă utilizatorul a încărcat poze, o afișăm pe prima
        if (is_array($images) && !empty($images[0])) {
            $firstImage = ltrim((string) $images[0], '/');

            if (Str::startsWith($firstImage, ['http://', 'https://'])) {
                return $firstImage;
            }

            if (Str::startsWith($firstImage, ['storage/', 'images/'])) {
                return asset($firstImage);
            }

            return asset('storage/services/' . $firstImage);
        }

        $categorySlug = $this->category?->slug;
        if ($categorySlug && in_array($categorySlug, self::AUTO_DEFAULT_CATEGORY_SLUGS, true)) {
            return asset(self::DEFAULT_AUTO_IMAGE);
        }

        // 2. Dacă NU are poze (sau au fost șterse), căutăm poza categoriei
        // Logica ta: images/defaults/{category-slug}.webp
        if ($categorySlug) {
            return asset('images/defaults/' . $categorySlug . '.webp');
        }

        // 3. Fallback absolut (dacă nu are nici categorie)
        return asset(self::DEFAULT_AUTO_IMAGE);
    }

    public function getCardImageUrlAttribute(): string
    {
        $images = $this->images;
        if (is_string($images)) {
            $images = json_decode($images, true) ?: [];
        }

        if (is_array($images) && !empty($images[0])) {
            return $this->imageCardUrl($images[0]);
        }

        return $this->main_image_url;
    }

    public function imageCardUrl(mixed $image): string
    {
        return ServiceImageStorage::cardImageUrl($image) ?: $this->main_image_url;
    }
	// Legacy fallback pentru anunțurile vechi care încă au generație salvată.
    public function generation()
    {
        return $this->belongsTo(CarGeneration::class, 'car_generation_id');
    }

// Legături cu nomenclatoarele (combustibil, cutie, caroserie, culoare)
public function combustibil()
{
    return $this->belongsTo(Combustibil::class, 'combustibil_id');
}

public function cutieViteze()
{
    return $this->belongsTo(CutieViteze::class, 'cutie_viteze_id');
}

public function caroserie()
{
    return $this->belongsTo(Caroserie::class, 'caroserie_id');
}

public function culoare()
{
    return $this->belongsTo(Culoare::class, 'culoare_id');
}

// ✅ NOI: Tracțiune
public function tractiune()
{
    return $this->belongsTo(Tractiune::class, 'tractiune_id');
}

// ✅ NOI: Normă poluare
public function normaPoluare()
{
    return $this->belongsTo(NormaPoluare::class, 'norma_poluare_id');
}

// ✅ NOI: Finisaj culoare (mat/metalizat/perlat)
public function culoareOpt()
{
    return $this->belongsTo(CuloareOpt::class, 'culoare_opt_id');
}

// ✅ NOI: Brand/Model pe FK (fără generație)
public function brandRel()
{
    return $this->belongsTo(CarBrand::class, 'brand_id');
}

public function modelRel()
{
    return $this->belongsTo(CarModel::class, 'model_id');
}

public function getIsActiveAttribute($value): bool
{
    return array_key_exists('is_active', $this->attributes)
        ? (bool) $value
        : $this->status === 'active';
}
}
