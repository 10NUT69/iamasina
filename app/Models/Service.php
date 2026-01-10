<?php

namespace App\Models;
use App\Models\CarBrand;
use App\Models\CarModel;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;


class Service extends Model
{
    use HasFactory, SoftDeletes;

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
'numar_usi'        => 'integer',
'numar_locuri'     => 'integer',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function county() { return $this->belongsTo(County::class); }
    public function locality() { return $this->belongsTo(Locality::class); }
    public function user() { return $this->belongsTo(User::class); }
    public function favorites() { return $this->hasMany(Favorite::class); }

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

    // SMART SLUG
    public function getSmartSlugAttribute()
    {
        $cleanTitle = trim(preg_replace('/\s+/', ' ', $this->title));
        $words = explode(' ', $cleanTitle);
        $firstThreeWords = array_slice($words, 0, 3);
        $slugString = implode(' ', $firstThreeWords);
        return Str::slug($slugString);
    }

    // PUBLIC URL
    // PUBLIC URL – /auto/{brand}/{model}/{county}/{locality}/{id}
   public function getPublicUrlAttribute()
{
    $county = $this->county;
    $countySlug = $county ? $county->slug : 'romania';

    $brandSlug = null;
    $modelSlug = null;

    // 1. ÎNCERCI VARIANTA CU GENERAȚIE (relații complete)
    if ($this->car_generation_id && $this->generation) {
        $generation = $this->generation;
        $model      = $generation ? $generation->model : null;
        $brand      = $model ? $model->brand : null;

        if ($brand && $model) {
            $brandSlug = $brand->slug;
            $modelSlug = $model->slug;
        }
    }
	

// 2. FALLBACK: dacă nu avem generație → folosim brand_id + model_id
if ((!$brandSlug || !$modelSlug) && $this->brand_id && $this->model_id) {
    $brand = CarBrand::select('slug', 'name')->find($this->brand_id);
    $model = CarModel::select('slug', 'name')->find($this->model_id);

    if ($brand && $model) {
        $brandSlug = $brand->slug ?: Str::slug($brand->name);
        $modelSlug = $model->slug ?: Str::slug($model->name);
    }
}

// 2.1 fallback EXTRA (opțional): dacă ai și text
if (!$brandSlug || !$modelSlug) {
    if (!empty($this->brand)) {
        $brandSlug = Str::slug($this->brand);
    }
    if (!empty($this->model)) {
        $modelSlug = Str::slug($this->model);
    }
}


    $localitySlug = null;
    if ($this->locality) {
        $localitySlug = $this->locality->slug ?: Str::slug($this->locality->name);
    } elseif (!empty($this->city)) {
        $localitySlug = Str::slug($this->city);
    }
    $localitySlug = $localitySlug ?: 'romania';

    // 3. Dacă tot avem brand + model + județ → construim URL-ul frumos
    if ($brandSlug && $modelSlug && $countySlug) {
        return route('service.show.car', [
            'brandSlug'  => $brandSlug,
            'modelSlug'  => $modelSlug,
            'countySlug' => $countySlug,
            'localitySlug' => $localitySlug,
            'id'         => $this->id,
        ]);
    }

    // 4. Fallback final (dacă chiar nu avem nimic)
    return url('/');
}


    // ==========================================
    // 🖼️ IMAGINI (LOGICA DE DEFAULT CATEGORIE)
    // ==========================================
    public function getMainImageUrlAttribute()
    {
        // 1. Dacă utilizatorul a încărcat poze, o afișăm pe prima
        if (!empty($this->images) && is_array($this->images) && isset($this->images[0])) {
            return asset('storage/services/' . $this->images[0]);
        }

        // 2. Dacă NU are poze (sau au fost șterse), căutăm poza categoriei
        // Logica ta: images/defaults/{category-slug}.webp
        if ($this->category) {
            return asset('images/defaults/' . $this->category->slug . '.webp');
        }

        // 3. Fallback absolut (dacă nu are nici categorie)
        return asset('images/defaults/placeholder.png');
    }
	// Legătura critică: Anunț -> Generație
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
}
