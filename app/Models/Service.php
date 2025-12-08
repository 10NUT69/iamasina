<?php

namespace App\Models;

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
    ];

    protected $casts = [
        'images' => 'array',
        'published_at' => 'datetime',
        'expires_at' => 'datetime',
        'price_value' => 'float',

        // cast pentru câmpurile numerice auto
        
		'an_fabricatie' => 'integer',
        'km' => 'integer',
        'capacitate_cilindrica' => 'integer',
        'putere' => 'integer',
    ];

    public function category() { return $this->belongsTo(Category::class); }
    public function county() { return $this->belongsTo(County::class); }
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
    public function getPublicUrlAttribute()
    {
        $catSlug = $this->category ? $this->category->slug : 'diverse';
        $countySlug = $this->county ? $this->county->slug : 'romania';

        return route('service.show', [ 
            'category' => $catSlug,
            'county'   => $countySlug,
            'slug'     => $this->smart_slug,
            'id'       => $this->id
        ]);
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

}