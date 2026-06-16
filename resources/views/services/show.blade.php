@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $siteBrand = 'iaAuto.ro';
    $isDeleted = $service->trashed();

    // --- DEALER DETECT ---
    $sellerUser = $service->user;
    $isDealer   = $sellerUser && ($sellerUser->user_type === 'dealer');
    $isOwnListing = auth()->check() && $sellerUser && auth()->id() === $sellerUser->id;
    $canMessageSeller = !$isDeleted && $sellerUser && auth()->check() && !$isOwnListing;

    // --- PHONE LOGIC ---
    $phoneSource = $isDealer ? ($sellerUser->phone ?? '') : ($service->phone ?? '');
    $hasPhone    = !empty($phoneSource);
    $rawPhone       = preg_replace('/[^0-9]/', '', $phoneSource);
    $formattedPhone = $phoneSource;
    if (strlen($rawPhone) === 10) {
        $formattedPhone = preg_replace('/^(\d{4})(\d{3})(\d{3})$/', '$1 $2 $3', $rawPhone);
    }

    // --- PRET ---
    $formattedPrice = number_format($service->price_value ?? 0, 0, '.', ' ');
    $currency       = $service->currency ?? 'EUR';
    $priceTypeBadge = null;
    $priceTypeBadgeClass = null;

    if ($service->price_type === 'negotiable') {
        $priceTypeBadge = 'Negociabil';
        $priceTypeBadgeClass = 'bg-green-100 text-green-700 dark:bg-green-900/20 dark:text-green-200';
    } elseif ($service->price_type === 'fixed') {
        $priceTypeBadge = 'PREȚ FIX';
        $priceTypeBadgeClass = 'bg-red-100 text-red-700 dark:bg-red-900/20 dark:text-red-200';
    }

    // --- DATE AUTO (brand/model pe FK, cu fallback vechi discret) ---
    $brandName =
        optional($service->brandRel ?? null)->name
        ?: optional(optional(optional($service->generation)->model)->brand)->name
        ?: ($service->brand ?? null);

    $modelName =
        optional($service->modelRel ?? null)->name
        ?: optional(optional($service->generation)->model)->name
        ?: ($service->model ?? null);

    $year   = $service->an_fabricatie ?? $service->year;
    $km     = $service->km ?? $service->mileage;
    $engine = $service->capacitate_cilindrica ?? $service->engine_capacity ?? $service->engine_size;
    $power  = $service->putere ?? $service->power;

    $fuelName   = optional($service->combustibil)->nume ?? $service->fuel_type;
    $transName  = optional($service->cutieViteze)->nume ?? $service->transmission;
    $bodyName   = optional($service->caroserie)->nume ?? $service->body_type;
    $colorName  = optional($service->culoare)->nume ?? $service->color;

    // --- NOI ---
    $colorOptName = optional($service->culoareOpt ?? null)->nume;
    $tractionName = optional($service->tractiune ?? null)->nume;
    $euroName     = optional($service->normaPoluare ?? null)->nume;

    $doors = $service->numar_usi;
    $seats = $service->numar_locuri;

    $importantDetails = $service->important_details;

    // --- IMAGINI ---
    $images = [];
    if (!$isDeleted) {
        $images = is_string($service->images) ? json_decode($service->images, true) : ($service->images ?? []);
    }
    $images = is_array($images) ? array_values(array_filter($images)) : [];

    // URL-uri complete
    $fullImageUrls = array_map(function($img) {
        $path = is_string($img) ? $img : ($img['path'] ?? $img['url'] ?? $img->path ?? '');
        $path = ltrim((string) $path, '/');

        if ($path === '') {
            return null;
        }

        if (Str::startsWith($path, ['http://', 'https://'])) {
            return $path;
        }

        if (Str::startsWith($path, ['storage/', 'images/'])) {
            return asset($path);
        }

        return asset('storage/services/' . $path);
    }, $images);
    $fullImageUrls = array_values(array_filter($fullImageUrls));

    // fallback dacă nu sunt imagini (nu crăpăm swiper/mozaic)
    if (empty($fullImageUrls) && $service->main_image_url) {
        $fullImageUrls = [$service->main_image_url];
    }
    if (empty($fullImageUrls)) {
        $fullImageUrls = [asset('images/defaults/placeholder.png')];
    }

    // --- SEO / SHARE ---
    $currentUrl = url()->current();

    $seoLocation = $service->locality
        ? trim($service->locality->name . ', ' . ($service->county->name ?? ''))
        : ($service->county->name ?? 'România');

    $countyName = trim((string) ($service->county->name ?? ''));
    $localityName = trim((string) ($service->locality->name ?? $service->city ?? ''));
    $isBucharestCounty = $countyName !== '' && Str::lower(Str::ascii($countyName)) === 'bucuresti';
    $showLocationLabel = null;

    if ($countyName !== '' && $localityName !== '') {
        $showLocationLabel = ($isBucharestCounty ? $countyName : 'Județul ' . $countyName) . ', ' . $localityName;
    } elseif ($countyName !== '') {
        $showLocationLabel = $isBucharestCounty ? $countyName : 'Județul ' . $countyName;
    } elseif ($localityName !== '') {
        $showLocationLabel = $localityName;
    }

    $showPublishedLabel = $service->listing_date_label;

    $cleanTitleString = preg_replace('/[^\p{L}\p{N}\s]/u', ' ', (string) $service->title);
    $cleanTitleString = trim(preg_replace('/\s+/', ' ', $cleanTitleString));
    $titleWords = array_values(array_filter(explode(' ', $cleanTitleString), fn ($word) => $word !== ''));
    $shortUserTitle = trim(implode(' ', array_slice($titleWords, 0, 3)));

    $currencyUpper = strtoupper((string) $currency);
    $currencyLabel = $currencyUpper === 'EUR' ? '€' : $currencyUpper;
    $hasPriceForSeo = $service->price_value !== null && (float) $service->price_value > 0;
    $seoPrice = $hasPriceForSeo ? trim($formattedPrice . ' ' . $currencyLabel) : null;

    $seoVehicleTitle = trim(implode(' ', array_filter([$brandName, $modelName, $year])));
    $seoVehicleTitle = $seoVehicleTitle ?: ($shortUserTitle ?: 'Autoturism');

    $seoVehicleDescription = trim(implode(' ', array_filter([$brandName, $modelName, $year])));
    $seoVehicleDescription = $seoVehicleDescription ?: $seoVehicleTitle;

    $fullSeoTitle = $seoVehicleTitle . ' de vânzare';
    if ($seoLocation) {
        $fullSeoTitle .= ' în ' . $seoLocation;
    }
    if ($seoPrice) {
        $fullSeoTitle .= ' - ' . $seoPrice;
    }
    if (mb_strlen($fullSeoTitle . ' | ' . $siteBrand) <= 70) {
        $fullSeoTitle .= ' | ' . $siteBrand;
    }

    $imageAlt = $service->image_alt;

    $seoSpecs = [];
    if ($seoPrice) {
        $seoSpecs[] = $seoPrice;
    }
    if ($km) {
        $seoSpecs[] = number_format((float) $km, 0, ',', '.') . ' km';
    }
    if ($fuelName) {
        $seoSpecs[] = mb_strtolower($fuelName, 'UTF-8');
    }
    if ($transName) {
        $seoSpecs[] = 'cutie ' . mb_strtolower($transName, 'UTF-8');
    }

    $seoDescription = $seoVehicleDescription . ' de vânzare';
    if ($seoLocation && $seoLocation !== 'România') {
        $seoDescription .= ' în ' . $seoLocation;
    }
    $seoDescription .= '.';
    if (!empty($seoSpecs)) {
        $seoDescription .= ' Detalii: ' . implode(', ', $seoSpecs) . '.';
    }
    $seoDescription .= ' Vezi poze și contactează vânzătorul pe iaAuto.ro.';
    $seoDescription = Str::limit(strip_tags($seoDescription), 160, '');

    $rawShareImages = is_string($service->images)
        ? (json_decode($service->images, true) ?: [])
        : ($service->images ?? []);
    $rawShareImages = array_values(array_filter((array) $rawShareImages));

    $seoImage = (!$isDeleted && $service->main_image_url)
        ? $service->main_image_url
        : asset('images/social-share.webp');
    if (!$isDeleted && !empty($rawShareImages)) {
        $firstShareImage = $rawShareImages[0];
        if (Str::startsWith($firstShareImage, ['http://', 'https://'])) {
            $seoImage = $firstShareImage;
        } elseif (Str::startsWith($firstShareImage, ['/storage/', 'storage/', '/images/', 'images/'])) {
            $seoImage = asset(ltrim($firstShareImage, '/'));
        } else {
            $seoImage = asset('storage/services/' . ltrim($firstShareImage, '/'));
        }
    }

    $schemaData = [
        '@context' => 'https://schema.org',
        '@type' => 'Car',
        'name' => $fullSeoTitle,
        'description' => $seoDescription,
        'image' => $seoImage,
        'url' => $currentUrl,
        'brand' => $brandName ? ['@type' => 'Brand', 'name' => $brandName] : null,
        'model' => $modelName,
        'vehicleModelDate' => $year,
        'mileageFromOdometer' => $km ? [
            '@type' => 'QuantitativeValue',
            'value' => (int) $km,
            'unitCode' => 'KMT',
        ] : null,
        'offers' => (!$isDeleted && $hasPriceForSeo) ? [
            '@type' => 'Offer',
            'priceCurrency' => $currencyUpper,
            'price' => $service->price_value,
            'availability' => 'https://schema.org/InStock',
            'url' => $currentUrl,
        ] : null,
    ];
    $schemaData = array_filter($schemaData, fn ($value) => $value !== null && $value !== '');

    $autoListingUrl = static fn (...$segments): string => url('/' . implode('/', array_merge(
        ['anunturi-auto-de-vanzare'],
        array_map(fn ($segment) => Str::slug($segment), array_values(array_filter($segments)))
    )));

    $brandSlug = optional($service->brandRel ?? null)->slug
        ?: optional(optional(optional($service->generation)->model)->brand)->slug
        ?: ($brandName ? Str::slug($brandName) : null);
    $modelSlug = optional($service->modelRel ?? null)->slug
        ?: optional(optional($service->generation)->model)->slug
        ?: ($modelName ? Str::slug($modelName) : null);
    $countySlug = optional($service->county ?? null)->slug ?: ($countyName ? Str::slug($countyName) : null);
    $localitySlug = optional($service->locality ?? null)->slug ?: ($localityName ? Str::slug($localityName) : null);

    $breadcrumbItems = [
        ['name' => 'Acasă', 'item' => url('/')],
        ['name' => 'Autoturisme', 'item' => $autoListingUrl()],
    ];

    if ($brandName && $brandSlug) {
        $breadcrumbItems[] = ['name' => $brandName, 'item' => $autoListingUrl($brandSlug)];
    }

    if ($modelName && $brandSlug && $modelSlug) {
        $breadcrumbItems[] = ['name' => $modelName, 'item' => $autoListingUrl($brandSlug, $modelSlug)];
    }

    if ($countyName && $countySlug) {
        $breadcrumbItems[] = ['name' => $countyName, 'item' => $autoListingUrl($brandSlug, $brandSlug ? $modelSlug : null, $countySlug)];
    }

    if ($localityName && $countySlug && $localitySlug) {
        $breadcrumbItems[] = ['name' => $localityName, 'item' => $autoListingUrl($brandSlug, $brandSlug ? $modelSlug : null, $countySlug, $localitySlug)];
    }

    $breadcrumbItems[] = ['name' => trim((string) $service->title) ?: $seoVehicleTitle];

    $lastBreadcrumbIndex = array_key_last($breadcrumbItems);
    if ($lastBreadcrumbIndex !== null) {
        unset($breadcrumbItems[$lastBreadcrumbIndex]['item']);
    }

    $visualBreadcrumbItems = array_values(array_filter(
        $breadcrumbItems,
        static fn ($item) => filled($item['item'] ?? null)
    ));

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => array_map(
            static fn ($item, $index) => array_filter([
                '@type' => 'ListItem',
                'position' => $index + 1,
                'name' => $item['name'],
                'item' => $item['item'] ?? null,
            ], fn ($value) => $value !== null && $value !== ''),
            array_values($breadcrumbItems),
            array_keys(array_values($breadcrumbItems))
        ),
    ];
@endphp

@section('title', $fullSeoTitle)
@section('meta_title', $fullSeoTitle)
@section('meta_description', $seoDescription)
@section('meta_image', $seoImage)

@section('canonical')
    <link rel="canonical" href="{{ $currentUrl }}" />
@endsection

@section('schema')
<script type="application/ld+json">
{!! json_encode($schemaData, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<script type="application/ld+json">
@json($breadcrumbSchema)
</script>
@endsection

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    /* Swiper Zoom Container Styling */
    .swiper-slide { display: flex; align-items: center; justify-content: center; overflow: hidden; }
    .swiper-zoom-container { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; }

    /* Navigation Arrows Generale (Lightbox + Desktop) */
    .swiper-button-next, .swiper-button-prev {
        color: white !important;
        background: rgba(0,0,0,0.5);
        width: 50px; height: 50px;
        border-radius: 50%;
        backdrop-filter: blur(4px);
        z-index: 20;
    }
    .swiper-button-next:after, .swiper-button-prev:after { font-size: 20px !important; font-weight: bold; }

    /* Glass Effect */
    .glass { background: rgba(255, 255, 255, 0.95); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); }
    @media (prefers-color-scheme: dark) {
        .glass { background: rgba(24, 24, 27, 0.94); }
    }

    .important-details-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(min(100%, 180px), 180px));
        gap: 12px;
        justify-content: start;
        align-items: stretch;
    }

    @media (max-width: 429px) {
        .important-details-grid {
            grid-template-columns: repeat(2, minmax(0, 1fr));
        }
    }

    @media (min-width: 768px) {
        .important-details-grid {
            grid-template-columns: repeat(auto-fill, minmax(184px, 184px));
        }
    }
</style>

{{-- ===================== 1. LIGHTBOX MODAL ===================== --}}
<div id="gallery-lightbox" class="fixed inset-0 bg-black hidden flex-col transition-opacity duration-300 opacity-0" style="z-index: 2147483640;" role="dialog" aria-modal="true">

    {{-- Counter (Top Left) --}}
    <div class="absolute top-28 left-6 pointer-events-none" style="z-index: 2147483647;">
        <span class="text-white font-medium text-sm tracking-wide bg-black/60 px-4 py-1.5 rounded-full backdrop-blur-md border border-white/10 shadow-lg">
            <span id="lb-current">1</span> / {{ count($fullImageUrls) }}
        </span>
    </div>

    {{-- Close Button (Top Right) --}}
    <button onclick="closeGallery()" class="fixed top-28 right-6 md:right-12 p-3 rounded-full bg-white/10 text-white hover:bg-[#E03E2D] hover:border-[#E03E2D] active:scale-95 transition-all focus:outline-none backdrop-blur-md border border-white/30 shadow-2xl cursor-pointer" style="z-index: 2147483647;">
        <svg class="w-8 h-8 drop-shadow-md" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>

    {{-- Swiper Container --}}
    <div class="flex-1 w-full h-full relative overflow-hidden">
        <div class="swiper lightbox-swiper w-full h-full">
            <div class="swiper-wrapper">
                @foreach($fullImageUrls as $index => $url)
                    <div class="swiper-slide">
                        <div class="swiper-zoom-container">
                            <img 
                                data-gallery-image
                                data-gallery-index="{{ $index }}"
                                data-gallery-src="{{ $url }}"
                                class="max-h-full max-w-full object-contain select-none" 
                                alt="{{ $imageAlt }} - poza {{ $loop->iteration }}"
                                style="-webkit-user-drag: none; -webkit-touch-callout: none;" 
                                draggable="false"
                                loading="lazy"
                                decoding="async"
                            >
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Navigare (Doar Desktop Lightbox) --}}
            <div class="swiper-button-next hover:bg-black/70 transition"></div>
            <div class="swiper-button-prev hover:bg-black/70 transition"></div>
        </div>
    </div>
</div>

{{-- ===================== 2. PAGINA PROPRIU-ZISĂ ===================== --}}
<div class="bg-[#F8F9FA] dark:bg-[#121212] min-h-screen font-sans text-gray-800 dark:text-gray-200 pb-32 pt-2.5 lg:pb-12">

    <div class="mb-3 space-y-2">
        <x-breadcrumbs :items="$visualBreadcrumbItems" :mark-last-current="false" class="max-w-full" />

        <button onclick="history.back()" class="inline-flex items-center gap-2 rounded-full border border-gray-200 bg-white px-4 py-2 text-sm font-bold text-gray-700 shadow-sm transition-all hover:shadow-md dark:border-[#333] dark:bg-[#1E1E1E] dark:text-gray-200">
            <svg class="h-4 w-4 text-[#E03E2D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Înapoi
        </button>
    </div>

    <div class="w-full grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- === STÂNGA (Galerie, Info, Descriere) === --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- GALERIE MOZAIC (Desktop) / SLIDER (Mobil) --}}
            <div class="rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-[#1E1E1E] relative select-none border border-gray-100 dark:border-[#333]">

                {{-- Mobile: Slider Simplu --}}
                <div class="block md:hidden relative group">
                    <div class="swiper mobile-hero-swiper aspect-[4/3] bg-gray-100 dark:bg-black">
                        <div class="swiper-wrapper">
                            @foreach($fullImageUrls as $index => $url)
                                <div class="swiper-slide cursor-pointer" onclick="openGallery({{ $index }})">
                                    <img
                                        @if($index < 2) src="{{ $url }}" @else data-gallery-src="{{ $url }}" @endif
                                        data-gallery-image
                                        data-gallery-index="{{ $index }}"
                                        alt="{{ $imageAlt }} - poza {{ $index + 1 }}"
                                        class="w-full h-full object-cover object-center"
                                        loading="{{ $index === 0 ? 'eager' : 'lazy' }}"
                                        decoding="async"
                                        @if($index === 0) fetchpriority="high" @elseif($index === 1) fetchpriority="low" @endif>
                                </div>
                            @endforeach
                        </div>
                        
                        {{-- Counter --}}
                        <div class="absolute bottom-3 right-3 bg-black/60 text-white text-xs font-bold px-2.5 py-1 rounded-md z-10 backdrop-blur-sm pointer-events-none">
                            📷 <span class="mobile-counter">1</span> / {{ count($fullImageUrls) }}
                        </div>

                        {{-- Săgeți Mobile (Adăugate aici, stilizate mai mici cu Tailwind) --}}
                        <div class="swiper-button-next mobile-arrow-next !w-10 !h-10 !after:text-lg"></div>
                        <div class="swiper-button-prev mobile-arrow-prev !w-10 !h-10 !after:text-lg"></div>
                    </div>
                </div>

                {{-- Desktop: Mozaic 1+2 --}}
                <div class="hidden md:grid grid-cols-4 grid-rows-2 gap-2 h-[480px] cursor-pointer">
                    @if(isset($fullImageUrls[0]))
                        <div class="col-span-3 row-span-2 relative overflow-hidden group" onclick="openGallery(0)">
                            <img src="{{ $fullImageUrls[0] }}" alt="{{ $imageAlt }} - poza 1" class="w-full h-full object-cover object-center transition duration-500 group-hover:scale-105" loading="eager" decoding="async" fetchpriority="high">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition"></div>
                        </div>
                    @endif

                    @if(isset($fullImageUrls[1]))
                        <div class="col-span-1 row-span-1 relative overflow-hidden group" onclick="openGallery(1)">
                            <img src="{{ $fullImageUrls[1] }}" alt="{{ $imageAlt }} - poza 2" class="w-full h-full object-cover object-center transition duration-500 group-hover:scale-105" loading="eager" decoding="async" fetchpriority="low">
                        </div>
                    @endif

                    @if(isset($fullImageUrls[2]))
                        <div class="col-span-1 row-span-1 relative overflow-hidden group" onclick="openGallery(2)">
                            <img src="{{ $fullImageUrls[2] }}" alt="{{ $imageAlt }} - poza 3" class="w-full h-full object-cover object-center transition duration-500 group-hover:scale-105" loading="lazy" decoding="async" fetchpriority="low">
                            @if(count($fullImageUrls) > 3)
                                <div class="absolute inset-0 bg-black/60 flex items-center justify-center group-hover:bg-black/50 transition">
                                    <span class="text-white font-bold text-lg tracking-wide flex flex-col items-center">
                                        <span>+{{ count($fullImageUrls) - 3 }}</span>
                                        <span class="text-xs font-normal opacity-80">poze</span>
                                    </span>
                                </div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            {{-- SOCIAL SHARE BUTTONS --}}
            <div class="flex flex-wrap gap-2">
                <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode($currentUrl) }}" target="_blank" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#1877F2] hover:bg-[#166fe5] text-white rounded-lg text-sm font-bold transition shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Distribuie
                </a>
                <a href="https://wa.me/?text={{ urlencode($service->title . ' ' . $currentUrl) }}" target="_blank" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#25D366] hover:bg-[#20bd5a] text-white rounded-lg text-sm font-bold transition shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                    WhatsApp
                </a>
                <button onclick="copyLink()" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-bold transition shadow-sm hover:shadow-md dark:bg-[#252525] dark:text-gray-100 dark:hover:bg-[#333333]">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span id="copy-text">Copiază Link</span>
                </button>
            </div>

            {{-- Titlu principal & pret mobil --}}
            <div class="space-y-2 mt-4">
                <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white leading-tight">{{ $service->title }}</h1>
                <div class="md:hidden space-y-1.5 text-sm text-gray-500 dark:text-gray-400">
                    @if($showLocationLabel)
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-[#E03E2D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>{{ $showLocationLabel }}</span>
                        </div>
                    @endif
                    <div class="flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0 text-[#E03E2D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>Publicat: {{ $showPublishedLabel }}</span>
                    </div>
                </div>
                <div class="md:hidden flex flex-wrap items-center gap-2 pt-2">
                    <span class="text-3xl font-extrabold text-[#E03E2D]">{{ $formattedPrice }} {{ $currency }}</span>
                    @if($priceTypeBadge)
                        <span class="relative top-px inline-block px-2 py-1 text-xs font-bold rounded uppercase md:top-0 {{ $priceTypeBadgeClass }}">{{ $priceTypeBadge }}</span>
                    @endif
                </div>
            </div>

            {{-- CHIPS: Specificatii Cheie (incl. Tracțiune/Euro) --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-2">
                @php
                    $mainSpecs = [
                        ['label' => 'An', 'val' => $year, 'icon' => 'calendar'],
                        ['label' => 'Rulaj', 'val' => $km ? number_format($km, 0, '.', ' ').' km' : '-', 'icon' => 'road'],
                        ['label' => 'Tracțiune', 'val' => $tractionName ?: '-', 'icon' => 'drive'],
                        ['label' => 'Euro', 'val' => $euroName ?: '-', 'icon' => 'euro'],
                    ];
                @endphp
                @foreach($mainSpecs as $spec)
                    <div class="bg-white dark:bg-[#1E1E1E] p-4 rounded-xl border border-gray-100 dark:border-[#333] shadow-sm flex flex-col justify-center gap-1 hover:border-[#E03E2D]/30 transition group">
                        <span class="text-xs text-gray-400 uppercase font-bold tracking-wider group-hover:text-[#E03E2D] transition-colors">{{ $spec['label'] }}</span>
                        <div class="flex items-center gap-2 text-gray-900 dark:text-white font-bold text-base truncate">
                            {{ $spec['val'] ?? '-' }}
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- === MODIFICARE: VIN BOX AICI (Mutat sus) === --}}
            @if($service->vin)
                <div class="mt-4 bg-white dark:bg-[#1E1E1E] p-4 rounded-xl border border-gray-100 dark:border-[#333] shadow-sm flex flex-wrap items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 flex items-center justify-center bg-gray-50 dark:bg-[#333] rounded-lg text-gray-500 dark:text-gray-300">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="font-bold text-gray-500 dark:text-gray-400 text-sm uppercase tracking-wide">Serie Șasiu (VIN)</span>
                    </div>
                    <span class="font-mono font-bold text-lg text-gray-900 dark:text-white select-all break-all text-right">
                        {{ $service->vin }}
                    </span>
                </div>
            @endif

@if(!empty($importantDetails))
    <div class="bg-white dark:bg-[#1E1E1E] p-4 sm:p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-[#333]">
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-5 flex items-center gap-2">
            <span class="w-1 h-6 bg-[#E03E2D] rounded-full"></span>
            Detalii importante
        </h3>

        <div class="important-details-grid">
            @foreach($importantDetails as $detail)
                            @php
                                $isWarningDetail = $detail['type'] === 'warning';
                            @endphp
                            <div @class([
                                'min-h-16 rounded-xl border px-4 py-3 flex items-center gap-3 transition-colors',
                                'border-gray-200 bg-white text-gray-900 dark:border-[#333] dark:bg-[#242424] dark:text-white' => !$isWarningDetail,
                                'border-amber-200 bg-amber-50/70 text-amber-950 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-100' => $isWarningDetail,
                            ])>
                                <span @class([
                                    'flex h-8 w-8 shrink-0 items-center justify-center rounded-lg',
                                    'bg-emerald-50 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-300' => !$isWarningDetail,
                                    'bg-amber-100 text-amber-600 dark:bg-amber-500/15 dark:text-amber-300' => $isWarningDetail,
                                ])>
                                    @switch($detail['icon'])
                                        @case('gauge')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l3-3m5 3a8 8 0 10-16 0m16 0a8 8 0 01-1.1 4H5.1A8 8 0 014 14" /></svg>
                                            @break
                                        @case('shield')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3l7 3v5c0 4.5-2.9 8.7-7 10-4.1-1.3-7-5.5-7-10V6l7-3z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4" /></svg>
                                            @break
                                        @case('clipboard')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5h6m-7 3h8m-8 4h8m-8 4h5M8 4h8a2 2 0 012 2v13a2 2 0 01-2 2H8a2 2 0 01-2-2V6a2 2 0 012-2z" /></svg>
                                            @break
                                        @case('user')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 12a4 4 0 100-8 4 4 0 000 8zM4 20a8 8 0 0116 0" /></svg>
                                            @break
                                        @case('receipt')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 3h10a2 2 0 012 2v16l-3-2-3 2-3-2-3 2-3-2V5a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9h8M8 13h8M8 17h5" /></svg>
                                            @break
                                        @case('arrows')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h11l-3-3m3 3l-3 3M17 17H6l3 3m-3-3l3-3" /></svg>
                                            @break
                                        @case('key')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a4 4 0 11-3.5 6H9l-2 2H5v2H3v-3.5L7.5 9H11a4 4 0 014-2z" /></svg>
                                            @break
                                        @case('file')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 3h6l4 4v14H8a2 2 0 01-2-2V5a2 2 0 012-2z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 3v5h5M9 13h6M9 17h4" /></svg>
                                            @break
                                        @case('globe')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21a9 9 0 100-18 9 9 0 000 18zM3 12h18M12 3c2.2 2.4 3.2 5.4 3.2 9S14.2 18.6 12 21M12 3c-2.2 2.4-3.2 5.4-3.2 9S9.8 18.6 12 21" /></svg>
                                            @break
                                        @case('filter')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7h5m4 0h7M4 12h9m4 0h3M4 17h3m4 0h9M9 5v4m8 1v4m-8 1v4" /></svg>
                                            @break
                                        @case('warning')
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4l9 16H3L12 4zM12 9v5M12 17h.01" /></svg>
                                            @break
                                    @endswitch
                                </span>
                                <span class="min-w-0">
                                    <span class="block break-words text-[13px] font-bold leading-tight text-gray-700 dark:text-gray-200">{{ $detail['label'] }}</span>
                                    <span @class([
                                        'mt-1 block text-sm font-extrabold leading-tight',
                                        'text-gray-950 dark:text-white' => !$isWarningDetail,
                                        'text-amber-950 dark:text-amber-100' => $isWarningDetail,
                                    ])>Da</span>
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- DESCRIERE --}}
            <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 dark:border-[#333]">
                <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="w-1 h-6 bg-[#E03E2D] rounded-full"></span>
                    Descriere
                </h3>
                <div class="prose prose-slate dark:prose-invert max-w-none leading-relaxed whitespace-pre-line text-gray-600 dark:text-gray-300">
                    {{ $service->description }}
                </div>
            </div>

            {{-- TABEL DETALII --}}
            <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-sm border border-gray-100 dark:border-[#333] overflow-hidden">
                <div class="p-6 border-b border-gray-100 dark:border-[#333]">
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white">Specificații Tehnice</h3>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 divide-y md:divide-y-0 md:divide-x divide-gray-100 dark:divide-[#333]">
                    <div class="p-0">
                        @php
                            $specs1 = [
                                'Marcă' => $brandName,
                                'Model' => $modelName,
                                'An fabricație' => $year,
                                'Kilometraj' => $km ? number_format($km, 0, '.', ' ') . ' km' : null,

                                // ✅ NOI
                                'Număr uși' => $doors ?: null,
                                'Număr locuri' => $seats ?: null,
                            ];
                        @endphp
                        @foreach($specs1 as $k => $v)
                            @if(!is_null($v) && $v !== '')
                                <div class="flex justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-[#252525] transition">
                                    <span class="text-gray-500 dark:text-gray-400">{{ $k }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $v }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>

                    <div class="p-0">
                        @php
                            $specs2 = [
                                'Motorizare' => $engine ? $engine.' cm³' : null,
                                'Putere' => $power ? $power.' CP' : null,
                                'Combustibil' => $fuelName ?: null,
                                'Transmisie' => $transName ?: null,

                                // ✅ NOI
                                'Tracțiune' => $tractionName ?: null,
                                'Normă poluare' => $euroName ?: null,

                                'Caroserie' => $bodyName ?: null,
                                'Culoare' => $colorName ?: null,
                                'Finisaj culoare' => $colorOptName ?: null,
                            ];
                        @endphp
                        @foreach($specs2 as $k => $v)
                            @if(!is_null($v) && $v !== '')
                                <div class="flex justify-between px-6 py-4 hover:bg-gray-50 dark:hover:bg-[#252525] transition">
                                    <span class="text-gray-500 dark:text-gray-400">{{ $k }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $v }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- VIN Scos de aici --}}
            </div>

        </div>

        {{-- === DREAPTA (Sidebar Sticky) === --}}
        <div class="lg:col-span-4 relative">
            <div class="sticky top-6 space-y-6">

                {{-- CARD TITLU & PRET (Doar Desktop) --}}
                <div class="bg-white dark:bg-[#1E1E1E] p-6 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 dark:border-[#333] hidden md:block">
                    <p class="text-xl font-bold text-gray-900 dark:text-white mb-2 leading-snug">{{ $service->title }}</p>
                    <div class="mb-6 space-y-2 text-sm text-gray-500 dark:text-gray-400">
                        @if($showLocationLabel)
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 shrink-0 text-[#E03E2D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                <span>{{ $showLocationLabel }}</span>
                            </div>
                        @endif
                        <div class="flex items-center gap-2">
                            <svg class="w-4 h-4 shrink-0 text-[#E03E2D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <span>Publicat: {{ $showPublishedLabel }}</span>
                        </div>
                    </div>

                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $formattedPrice }}</span>
                        <span class="text-xl font-bold text-gray-500 dark:text-gray-400">{{ $currency }}</span>
                    </div>
                    @if($priceTypeBadge)
                        <span class="inline-block px-2 py-1 text-xs font-bold rounded uppercase {{ $priceTypeBadgeClass }}">{{ $priceTypeBadge }}</span>
                    @endif
                </div>

                {{-- CARD VANZATOR --}}
                <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 dark:border-[#333] overflow-hidden">
                    <div class="p-6">
                        @if($isDealer)
                            {{-- === DEALER MODE === --}}
                            <div class="flex items-center mb-4">
                                <span class="bg-[#E03E2D] text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Dealer Autorizat</span>
                            </div>

                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-14 h-14 shrink-0 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-xl font-black text-[#E03E2D] dark:border-[#333] dark:bg-[#252525]">
                                    {{ strtoupper(substr(($sellerUser->company_name ?? 'D'), 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="font-bold text-lg text-gray-900 dark:text-white leading-tight truncate">
                                        {{ $sellerUser->company_name ?? 'Parc Auto' }}
                                    </h4>
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">Dealer Auto • Înregistrat {{ optional($sellerUser->created_at)->format('Y') }}</p>
                                </div>
                            </div>

                            <div class="space-y-3 mb-6">
                                @if(!empty($sellerUser->cui))
                                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-[#252525]">
                                        <div class="w-6 h-6 flex items-center justify-center rounded-full bg-white dark:bg-[#333] text-gray-400 shrink-0 border border-gray-100 dark:border-[#444]">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z" /></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">CUI / CIF</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">{{ $sellerUser->cui }}</p>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($sellerUser->county) || !empty($sellerUser->city))
                                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-[#252525]">
                                        <div class="w-6 h-6 flex items-center justify-center rounded-full bg-white dark:bg-[#333] text-gray-400 shrink-0 border border-gray-100 dark:border-[#444]">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 111.314 0z" /></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">Locație</p>
                                            <p class="text-sm font-semibold text-gray-900 dark:text-white">
                                                {{ trim(($sellerUser->city ? $sellerUser->city.', ' : '') . ($sellerUser->county ?? '')) }}
                                            </p>
                                        </div>
                                    </div>
                                @endif

                                @if(!empty($sellerUser->address))
                                    <div class="flex items-start gap-3 p-3 rounded-lg bg-gray-50 dark:bg-[#252525]">
                                        <div class="w-6 h-6 flex items-center justify-center rounded-full bg-white dark:bg-[#333] text-gray-400 shrink-0 border border-gray-100 dark:border-[#444]">
                                            <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                                        </div>
                                        <div class="flex-1">
                                            <p class="text-[10px] text-gray-400 font-bold uppercase">Adresă Parc</p>
                                            <p class="text-xs font-semibold text-gray-900 dark:text-white leading-relaxed">
                                                {{ $sellerUser->address }}
                                            </p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                        @else
                            {{-- === PRIVATE SELLER === --}}
                            <div class="flex items-center gap-4 mb-4">
                                <div class="w-12 h-12 rounded-full bg-red-100 text-[#C81424] flex items-center justify-center font-bold text-lg">
                                    {{ $service->author_initial ?? 'U' }}
                                </div>
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase">Vânzător Privat</p>
                                    <h4 class="font-bold text-base text-gray-900 dark:text-white">{{ $service->author_name ?? 'Utilizator' }}</h4>
                                </div>
                            </div>
                        @endif

                        {{-- ACTION BUTTONS DESKTOP --}}
                        <div class="mt-4 space-y-3 hidden md:block">
                            @if($isDeleted)
                                <button disabled class="w-full bg-gray-200 text-gray-500 py-3.5 rounded-xl font-bold dark:bg-[#333333] dark:text-gray-400">Anunt indisponibil</button>
                                @if($isDealer && !empty($sellerUser->dealer_public_url))
                                    <a href="{{ $sellerUser->dealer_public_url }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#D7DEE7] bg-[#F8FAFC] px-4 py-3 text-sm font-bold text-[#172033] transition hover:bg-[#F1F5F9] active:bg-[#EAF0F6]">
                                        <span>Vezi portofoliu dealer</span>
                                        <svg class="h-4 w-4 flex-shrink-0 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endif
                            @else
                                <button onclick="revealPhone('desktop', '{{ $rawPhone }}', '{{ $formattedPhone }}')" id="btn-phone-desktop" class="w-full bg-[#E03E2D] hover:bg-[#c92a1b] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-red-500/20 transition transform active:scale-95 flex items-center justify-center gap-2 group">
                                    <svg class="w-5 h-5 group-hover:animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span id="txt-phone-desktop">Arată Numărul</span>
                                </button>
                                @if($canMessageSeller)
                                    <button type="button" onclick="openSellerMessageModal()" class="w-full rounded-xl border border-[#D7DEE7] bg-white py-3.5 font-bold text-[#172033] transition hover:bg-[#F8FAFC] active:bg-[#F1F5F9]">
                                        Trimite Mesaj
                                    </button>
                                @elseif(!auth()->check())
                                    <a href="{{ route('login') }}" class="inline-flex w-full items-center justify-center rounded-xl border border-[#D7DEE7] bg-white py-3.5 font-bold text-[#172033] transition hover:bg-[#F8FAFC] active:bg-[#F1F5F9]">
                                        Autentifică-te pentru mesaj
                                    </a>
                                @elseif($isOwnListing)
                                    <button disabled class="w-full bg-gray-100 border border-gray-200 text-gray-400 font-bold py-3.5 rounded-xl dark:border-[#333333] dark:bg-[#252525]">
                                        Este anunțul tău
                                    </button>
                                @else
                                    <button disabled class="w-full bg-gray-100 border border-gray-200 text-gray-400 font-bold py-3.5 rounded-xl dark:border-[#333333] dark:bg-[#252525]">
                                        Mesaj indisponibil
                                    </button>
                                @endif
                                @if($isDealer && !empty($sellerUser->dealer_public_url))
                                    <a href="{{ $sellerUser->dealer_public_url }}"
                                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#D7DEE7] bg-[#F8FAFC] px-4 py-3 text-sm font-bold text-[#172033] transition hover:bg-[#F1F5F9] active:bg-[#EAF0F6]">
                                        <span>Vezi portofoliu dealer</span>
                                        <svg class="h-4 w-4 flex-shrink-0 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                                    </a>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>

                @if($isDealer && !empty($sellerUser->dealer_public_url))
                    <a href="{{ $sellerUser->dealer_public_url }}"
                       class="inline-flex w-full items-center justify-center gap-2 rounded-xl border border-[#D7DEE7] bg-[#F8FAFC] px-4 py-3 text-sm font-bold text-[#172033] transition hover:bg-[#F1F5F9] active:bg-[#EAF0F6] md:hidden">
                        <span>Vezi portofoliu dealer</span>
                        <svg class="h-4 w-4 flex-shrink-0 text-current" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </a>
                @endif

                {{-- CARD SIGURANTA --}}
                <div class="flex items-start gap-3 rounded-xl border border-[#F1D38A] bg-[#FFF8E8] p-4">
                    <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-[#9A6700]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div>
                        <h5 class="text-sm font-bold text-[#9A6700]">Recomandare de siguranță</h5>
                        <p class="mt-1 text-xs leading-snug text-[#5F4B1F]">Verificați actele și informațiile vehiculului înainte de achiziție. Evitați plățile în avans și confirmați identitatea vânzătorului.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MOBILE BOTTOM BAR --}}
<div class="fixed bottom-0 left-0 right-0 z-40 lg:hidden glass border-t border-gray-200 dark:border-[#333333] px-4 py-3 safe-area-pb">
    <div class="flex gap-3">
        @if($isDeleted)
            <button class="w-full bg-gray-200 py-3 rounded-lg font-bold text-gray-500 dark:bg-[#333333] dark:text-gray-400" disabled>Anunt indisponibil</button>
        @else
            @if($canMessageSeller)
                <button type="button" onclick="openSellerMessageModal()" class="flex-1 rounded-xl border border-[#D7DEE7] bg-white py-3 font-bold text-[#172033] transition hover:bg-[#F8FAFC] active:bg-[#F1F5F9]">Mesaj</button>
            @elseif(!auth()->check())
                <a href="{{ route('login') }}" class="flex-1 rounded-xl border border-[#D7DEE7] bg-white py-3 text-center font-bold text-[#172033] transition hover:bg-[#F8FAFC] active:bg-[#F1F5F9]">Mesaj</a>
            @else
                <button class="flex-1 bg-gray-100 border border-gray-200 text-gray-400 font-bold py-3 rounded-xl dark:border-[#333333] dark:bg-[#252525]" disabled>Mesaj</button>
            @endif
            <button onclick="revealPhone('mobile', '{{ $rawPhone }}', '{{ $formattedPhone }}')" id="btn-phone-mobile" class="flex-[2] bg-[#E03E2D] active:bg-[#c92a1b] text-white font-bold py-3 rounded-xl shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span id="txt-phone-mobile">Sună</span>
            </button>
        @endif
    </div>
</div>

@if($canMessageSeller)
<div id="seller-message-modal"
     class="fixed inset-0 z-[2147483639] {{ $errors->has('body') ? 'flex' : 'hidden' }} items-center justify-center bg-black/60 px-4 py-8 backdrop-blur-sm"
     role="dialog"
     aria-modal="true"
     aria-labelledby="seller-message-title">
    <div class="w-full max-w-lg rounded-2xl bg-white shadow-2xl dark:bg-[#1E1E1E]">
        <div class="flex items-start justify-between gap-4 border-b border-gray-100 p-5 dark:border-[#333]">
            <div>
                <h2 id="seller-message-title" class="text-lg font-black text-gray-900 dark:text-white">Trimite mesaj</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    Către {{ $isDealer ? ($sellerUser->company_name ?? $sellerUser->name) : ($service->author_name ?? $sellerUser->name) }}
                </p>
            </div>
            <button type="button" onclick="closeSellerMessageModal()" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-gray-100 text-gray-500 transition hover:bg-gray-200 hover:text-gray-900 dark:bg-[#252525] dark:text-gray-300 dark:hover:bg-[#333]">
                <span class="sr-only">Închide</span>
                &times;
            </button>
        </div>

        <form method="POST" action="{{ route('messages.startFromService', $service) }}" class="p-5">
            @csrf
            <label for="seller-message-body" class="mb-2 block text-xs font-black uppercase tracking-wide text-gray-500 dark:text-gray-400">Mesajul tău</label>
            <textarea id="seller-message-body"
                      name="body"
                      rows="5"
                      maxlength="2000"
                      required
                      class="w-full resize-none rounded-xl border border-gray-300 bg-gray-50 px-4 py-3 text-sm text-gray-900 outline-none transition focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/20 dark:border-[#404040] dark:bg-[#252525] dark:text-white"
                      placeholder="Scrie întrebarea ta despre anunț...">{{ old('body') }}</textarea>
            @error('body')
                <p class="mt-2 text-sm font-semibold text-red-600">{{ $message }}</p>
            @enderror

            <div class="mt-4 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" onclick="closeSellerMessageModal()" class="inline-flex items-center justify-center rounded-xl border border-gray-200 px-5 py-3 text-sm font-bold text-gray-700 transition hover:bg-gray-50 dark:border-[#333] dark:text-gray-200 dark:hover:bg-[#252525]">
                    Renunță
                </button>
                <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#C81424] px-6 py-3 text-sm font-black text-white shadow-lg shadow-red-600/20 transition hover:bg-[#94111B] active:scale-95">
                    Trimite mesajul
                </button>
            </div>
        </form>
    </div>
</div>
@endif

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    function openSellerMessageModal() {
        const modal = document.getElementById('seller-message-modal');
        if (!modal) return;
        modal.classList.remove('hidden');
        modal.classList.add('flex');
        setTimeout(() => document.getElementById('seller-message-body')?.focus(), 50);
    }

    function closeSellerMessageModal() {
        const modal = document.getElementById('seller-message-modal');
        if (!modal) return;
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            let txt = document.getElementById('copy-text');
            let original = txt.innerText;
            txt.innerText = 'Copiat!';
            setTimeout(() => { txt.innerText = original; }, 2000);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {
        const galleryImageCount = {{ count($fullImageUrls) }};

        function normalizeGalleryIndex(index) {
            if (galleryImageCount < 1) return 0;

            return ((index % galleryImageCount) + galleryImageCount) % galleryImageCount;
        }

        function loadGalleryImage(index) {
            if (galleryImageCount < 1) return;

            const normalizedIndex = normalizeGalleryIndex(index);
            document.querySelectorAll(`[data-gallery-image][data-gallery-index="${normalizedIndex}"][data-gallery-src]`).forEach((image) => {
                if (image.getAttribute('src')) return;

                image.setAttribute('src', image.dataset.gallerySrc);
            });
        }

        function loadGalleryWindow(index) {
            loadGalleryImage(index);
            loadGalleryImage(index + 1);
        }

        function loadGalleryImageWhenIdle(index) {
            if (galleryImageCount <= index) return;

            if ('requestIdleCallback' in window) {
                window.requestIdleCallback(() => loadGalleryImage(index), { timeout: 2000 });
                return;
            }

            window.setTimeout(() => loadGalleryImage(index), 1200);
        }

        loadGalleryImage(0);
        loadGalleryImage(1);
        loadGalleryImageWhenIdle(2);

        // 1. Mobile Inline Slider (Actualizat cu Navigare)
        const mobileSwiper = new Swiper('.mobile-hero-swiper', {
            loop: true,
            pagination: false,
            navigation: {
                nextEl: ".mobile-arrow-next",
                prevEl: ".mobile-arrow-prev",
            },
            on: {
                slideChange: function () {
                    // Update counter
                    const index = this.realIndex + 1;
                    const counters = document.querySelectorAll('.mobile-counter');
                    counters.forEach(c => c.innerText = index);
                    loadGalleryWindow(this.realIndex);
                }
            }
        });

        // 2. Lightbox & Zoom Logic
        const lightbox = document.getElementById('gallery-lightbox');
        let lightboxSwiperInstance = null;

        window.openGallery = function(index) {
            loadGalleryImage(index - 1);
            loadGalleryImage(index);
            loadGalleryImage(index + 1);

            lightbox.classList.remove('hidden');
            lightbox.classList.add('flex');

            setTimeout(() => {
                lightbox.classList.remove('opacity-0');
            }, 10);

            document.body.style.overflow = 'hidden';

            if (!lightboxSwiperInstance) {
                lightboxSwiperInstance = new Swiper('.lightbox-swiper', {
                    initialSlide: index,
                    spaceBetween: 30,
                    grabCursor: true,
                    zoom: { maxRatio: 3, minRatio: 1 },
                    keyboard: { enabled: true },
                    navigation: { nextEl: ".swiper-button-next", prevEl: ".swiper-button-prev" },
                    on: {
                        slideChange: function () {
                            document.getElementById('lb-current').innerText = this.activeIndex + 1;
                            loadGalleryWindow(this.activeIndex);
                        }
                    }
                });
            } else {
                lightboxSwiperInstance.slideTo(index, 0);
            }

            document.getElementById('lb-current').innerText = index + 1;
        };

        window.closeGallery = function() {
            lightbox.classList.add('opacity-0');
            setTimeout(() => {
                lightbox.classList.add('hidden');
                lightbox.classList.remove('flex');
                document.body.style.overflow = '';
                if(lightboxSwiperInstance) lightboxSwiperInstance.zoom.out();
            }, 300);
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && !lightbox.classList.contains('hidden')) closeGallery();
        });

        // 3. Phone Reveal Logic
        window.revealPhone = function(type, raw, formatted) {
            const txtId = type === 'mobile' ? 'txt-phone-mobile' : 'txt-phone-desktop';
            const btnId = type === 'mobile' ? 'btn-phone-mobile' : 'btn-phone-desktop';
            const txtEl = document.getElementById(txtId);
            const btnEl = document.getElementById(btnId);

            if(!txtEl || !btnEl) return;

            if(txtEl.innerText.includes('Arată') || txtEl.innerText.includes('Sună')) {
                txtEl.innerText = formatted;
                if(type === 'mobile') window.location.href = 'tel:' + raw;
                else {
                    btnEl.classList.remove('bg-white', 'border-2', 'border-green-600', 'text-green-700');
                    btnEl.classList.add('bg-[#E03E2D]', 'text-white');
                    btnEl.onclick = function() { window.location.href = 'tel:' + raw; };
                }
            } else {
                window.location.href = 'tel:' + raw;
            }
        };
    });
</script>
@endsection
