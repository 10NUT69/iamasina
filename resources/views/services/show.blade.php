@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $siteBrand = 'MeseriasBun.ro';

    // --- 1. STATUS & FORMATĂRI SIMPLE ---

    $isDeleted = $service->trashed();
    $hasPhone  = !empty($service->phone);

    // Preț
    $formattedPrice = number_format($service->price_value ?? 0, 0, '.', ' ');
    $currency       = $service->currency ?? 'EUR';

    // Telefon
    $rawPhone       = preg_replace('/[^0-9]/', '', $service->phone ?? '');
    $formattedPhone = $service->phone;
    if (strlen($rawPhone) === 10) {
        $formattedPhone = preg_replace('/^(\d{4})(\d{3})(\d{3})$/', '$1 $2 $3', $rawPhone);
    }

    // --- 2. NORMALIZARE DATE AUTO (relații + fallback la câmpurile text) ---

    $brandName      = optional(optional(optional($service->generation)->model)->brand)->name ?: $service->brand;
    $modelName      = optional(optional($service->generation)->model)->name ?: $service->model;
    $generationName = optional($service->generation)->name;

    $year = $service->an_fabricatie ?? $service->year;
    $km   = $service->km ?? $service->mileage;

    $engine = $service->capacitate_cilindrica
        ?? $service->engine_capacity
        ?? $service->engine_size;

    $power = $service->putere ?? $service->power;

    $fuelName  = optional($service->combustibil)->nume ?? $service->fuel_type;
    $transName = optional($service->cutieViteze)->nume ?? $service->transmission;
    $bodyName  = optional($service->caroserie)->nume ?? $service->body_type;
    $colorName = optional($service->culoare)->nume ?? $service->color;

    // --- 3. IMAGINI ---
    // Dacă anunțul este șters: nu mai afișăm pozele userului, doar placeholder (prin main_image_url fallback în model)
    if ($isDeleted) {
        $images = [];
    } else {
        $images = is_string($service->images)
            ? json_decode($service->images, true)
            : ($service->images ?? []);
    }

    if ($service->main_image_url && !$isDeleted) {
        $images = is_array($images) ? $images : [];
        array_unshift($images, basename($service->main_image_url));
        $images = array_values(array_unique($images));
    }

    // --- 4. QUICK SPECS (iconițe) ---

    $quickSpecs = [];

    if ($year) {
        $quickSpecs[] = ['label' => 'An', 'value' => $year, 'icon' => 'calendar'];
    }
    if ($km) {
        $quickSpecs[] = [
            'label' => 'Km',
            'value' => number_format($km, 0, '.', ' ') . ' km',
            'icon'  => 'road'
        ];
    }
    if ($fuelName) {
        $quickSpecs[] = [
            'label' => 'Combustibil',
            'value' => ucfirst($fuelName),
            'icon'  => 'fuel'
        ];
    }
    if ($engine) {
        $quickSpecs[] = [
            'label' => 'Capacitate',
            'value' => $engine . ' cm³',
            'icon'  => 'engine'
        ];
    }
    if ($power) {
        $quickSpecs[] = [
            'label' => 'Putere',
            'value' => $power . ' CP',
            'icon'  => 'power'
        ];
    }
    if ($transName) {
        $quickSpecs[] = [
            'label' => 'Cutie',
            'value' => ucfirst($transName),
            'icon'  => 'transmission'
        ];
    }
    if (count($quickSpecs) < 6 && $bodyName) {
        $quickSpecs[] = [
            'label' => 'Caroserie',
            'value' => ucfirst($bodyName),
            'icon'  => 'body'
        ];
    }

    $quickSpecs = array_slice($quickSpecs, 0, 6);

    // --- 5. DETALII COMPLETE (tabel jos) ---

    $fullDetails = [
        'Marcă'                 => $brandName,
        'Model'                 => $modelName,
        'Generație'             => $generationName,
        'Anul producției'       => $year,
        'Kilometraj'            => $km ? number_format($km, 0, '.', ' ') . ' km' : null,
        'Combustibil'           => $fuelName ? ucfirst($fuelName) : null,
        'Putere'                => $power ? $power . ' CP' : null,
        'Capacitate cilindrică' => $engine ? $engine . ' cm³' : null,
        'Transmisie'            => $transName ? ucfirst($transName) : null,
        'Caroserie'             => $bodyName ? ucfirst($bodyName) : null,
        'Culoare'               => $colorName ? ucfirst($colorName) : null,
        'VIN (Serie șasiu)'     => $service->vin ?: null,
    ];

    $fullDetails = array_filter($fullDetails, fn($value) => !is_null($value) && $value !== '');

    // --- 6. SEO (logică din vechiul proiect adaptată la auto) ---

    $seoLocation = $service->county->name ?? 'România';

    $cleanTitleString = preg_replace('/[^\p{L}\p{N}\s]/u', '', $service->title);
    $cleanTitleString = trim(preg_replace('/\s+/', ' ', $cleanTitleString));
    $words = explode(' ', $cleanTitleString);
    $shortUserTitle = implode(' ', array_slice($words, 0, 3));

    $prefix = $isDeleted ? 'INDISPONIBIL - ' : '';
    $fullSeoTitle = $prefix . $shortUserTitle;

    if ($brandName) {
        $fullSeoTitle .= ' – ' . $brandName;
    }
    $fullSeoTitle .= ' în ' . $seoLocation;

    if (mb_strlen($fullSeoTitle) + mb_strlen(" | ".$siteBrand) <= 70) {
        $fullSeoTitle .= " | ".$siteBrand;
    }

    $introPart      = "Cauți {$brandName} {$modelName} în {$seoLocation}? ";
    $userDesc       = Str::limit(strip_tags($service->description), 120);
    $seoDescription = $isDeleted
        ? 'Acest anunț nu mai este valabil.'
        : $introPart . $userDesc;

    $pageUrl = $service->public_url;
    $seoImage = $service->main_image_url;

    $schemaData = [
        "@context" => "https://schema.org",
        "@type"    => "Vehicle",
        "name"        => $fullSeoTitle,
        "description" => $seoDescription,
        "image"       => $seoImage,
        "url"         => $pageUrl,
        "brand"       => $brandName,
        "model"       => $modelName,
        "vehicleModelDate" => $year,
        "offers"      => $isDeleted ? null : [
            "@type"         => "Offer",
            "priceCurrency" => $service->currency ?? 'EUR',
            "price"         => $service->price_value ?? '0',
            "availability"  => "https://schema.org/InStock"
        ],
    ];

    $breadcrumbSchema = [
        "@context" => "https://schema.org",
        "@type"    => "BreadcrumbList",
        "itemListElement" => [
            [
                "@type"    => "ListItem",
                "position" => 1,
                "name"     => "Acasă",
                "item"     => route('services.index')
            ],
            [
                "@type"    => "ListItem",
                "position" => 2,
                "name"     => trim(($brandName ? $brandName.' ' : '').$modelName.' '.$seoLocation),
                "item"     => url()->current()
            ],
            [
                "@type"    => "ListItem",
                "position" => 3,
                "name"     => Str::limit($service->title, 30)
            ],
        ]
    ];
@endphp

@section('title', $fullSeoTitle)
@section('meta_title', $fullSeoTitle)
@section('meta_description', $seoDescription)
@section('meta_image', $seoImage)

@section('canonical')
    <link rel="canonical" href="{{ $pageUrl }}" />
@endsection

@section('schema')
<script type="application/ld+json">
{!! json_encode(array_filter($schemaData), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
<script type="application/ld+json">
{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endsection

@section('content')

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

<style>
    .swiper-button-next, .swiper-button-prev {
        color: white !important;
        background: rgba(0,0,0,0.3);
        width: 44px !important;
        height: 44px !important;
        border-radius: 50%;
        backdrop-filter: blur(4px);
        transition: background 0.2s;
    }
    .swiper-button-next:hover, .swiper-button-prev:hover { background: rgba(0,0,0,0.6); }
    .swiper-button-next:after, .swiper-button-prev:after { font-size: 18px !important; font-weight: bold; }

    .thumbs-swiper .swiper-slide { opacity: 0.6; transition: all 0.2s; border: 2px solid transparent; }
    .thumbs-swiper .swiper-slide-thumb-active { opacity: 1; border-color: #E03E2D; }

    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>

{{-- MOBILE STICKY BAR --}}
<div class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white border-t border-gray-200 shadow-[0_-4px_15px_rgba(0,0,0,0.08)] px-4 py-3 flex items-center justify-between gap-3 safe-area-bottom">
    <div class="flex flex-col">
        @if($service->price_value)
            <div class="flex items-baseline gap-1">
                <span class="text-xl font-extrabold text-gray-900">{{ $formattedPrice }}</span>
                <span class="text-xs font-bold text-gray-500">{{ $currency }}</span>
            </div>
        @else
            <span class="text-lg font-bold text-blue-600">Preț la cerere</span>
        @endif
    </div>

    @if($isDeleted)
        <button disabled class="flex-1 bg-gray-200 text-gray-400 font-bold h-11 rounded-lg text-sm">Indisponibil</button>
    @elseif($hasPhone)
        <button onclick="revealPhone('mobile', '{{ $rawPhone }}', '{{ $formattedPhone }}')" id="btn-phone-mobile" class="flex-1 bg-[#E03E2D] active:bg-[#c92a1b] text-white font-bold h-11 rounded-lg flex items-center justify-center gap-2 shadow-lg transition-transform active:scale-95 text-sm">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
            <span id="txt-phone-mobile">Sună Vânzătorul</span>
        </button>
    @endif
</div>

<div class="bg-[#F2F4F8] dark:bg-[#121212] min-h-screen pb-24 lg:pb-16 font-sans text-sm">

    {{-- BREADCRUMBS --}}
    <div class="max-w-[1200px] mx-auto px-4 py-4">
        <nav class="flex items-center text-xs text-gray-500 overflow-x-auto whitespace-nowrap gap-2 no-scrollbar">
            <a href="/" class="hover:text-[#E03E2D] transition">Acasă</a>
            <span class="text-gray-300">/</span>
            @if($brandName)
                <a href="#" class="hover:text-[#E03E2D] transition">{{ $brandName }}</a>
            @endif
            @if($modelName)
                <span class="text-gray-300">/</span>
                <a href="#" class="hover:text-[#E03E2D] transition">{{ $modelName }}</a>
            @endif
            <span class="text-gray-300">/</span>
            <span class="text-gray-900 dark:text-gray-300 font-medium truncate max-w-[200px]">{{ $service->title }}</span>
        </nav>
    </div>

    {{-- BANNER ANUNȚ DEZACTIVAT --}}
    @if($isDeleted)
        <div class="max-w-[1200px] mx-auto px-4 mb-3">
            <div class="p-3 bg-red-50 border border-red-100 text-red-600 rounded-lg shadow-sm flex items-center gap-2 text-sm font-semibold">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
                Acest anunț a fost dezactivat și nu mai este disponibil. Informațiile rămân vizibile pentru referință.
            </div>
        </div>
    @endif

    <div class="max-w-[1200px] mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-6 {{ $isDeleted ? 'opacity-60 grayscale' : '' }}">

        {{-- LEFT --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- 1. GALERIE FOTO --}}
            <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] overflow-hidden">
                <div class="swiper main-swiper relative w-full aspect-[4/3] md:aspect-[16/10] bg-gray-100 dark:bg-black group">
                    <div class="swiper-wrapper">
                        @if(empty($images))
                            <div class="swiper-slide">
                                <img src="{{ $service->main_image_url }}" class="w-full h-full object-cover">
                            </div>
                        @else
                            @foreach($images as $img)
                                @php
                                    $imgUrl = Str::startsWith($img, ['http://', 'https://'])
                                        ? $img
                                        : asset('storage/services/'.$img);
                                @endphp
                                <div class="swiper-slide relative flex items-center justify-center bg-[#000] overflow-hidden">
                                    <div class="absolute inset-0 bg-cover bg-center blur-2xl opacity-40 scale-110" style="background-image: url('{{ $imgUrl }}')"></div>
                                    <img src="{{ $imgUrl }}" class="relative w-full h-full object-contain z-10" alt="{{ $service->title }}">
                                </div>
                            @endforeach
                        @endif
                    </div>
                    <div class="swiper-button-next !hidden md:!flex opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="swiper-button-prev !hidden md:!flex opacity-0 group-hover:opacity-100 transition-opacity"></div>

                    <div class="absolute bottom-4 right-4 z-20 bg-black/70 text-white text-xs font-bold px-3 py-1.5 rounded flex items-center gap-1.5 pointer-events-none">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        <span class="fraction-pagination">1 / {{ max(1, count($images) ?: 1) }}</span>
                    </div>
                </div>

                @if(!$isDeleted && count($images) > 1)
                    <div class="swiper thumbs-swiper px-3 py-3 bg-white dark:bg-[#1E1E1E] border-t border-gray-100 dark:border-[#333]">
                        <div class="swiper-wrapper">
                            @foreach($images as $img)
                                @php
                                    $thumbUrl = Str::startsWith($img, ['http://', 'https://'])
                                        ? $img
                                        : asset('storage/services/'.$img);
                                @endphp
                                <div class="swiper-slide !w-24 !h-[4.5rem] rounded-md cursor-pointer overflow-hidden bg-gray-100">
                                    <img src="{{ $thumbUrl }}" class="w-full h-full object-cover">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>

            {{-- 2. ASPECTE IMPORTANTE --}}
            @if(count($quickSpecs) > 0)
                <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] p-6">
                    <h3 class="text-base font-bold text-gray-900 dark:text-white mb-6">Aspecte importante</h3>
                    <div class="grid grid-cols-3 md:grid-cols-6 gap-y-6 gap-x-2">
                        @foreach($quickSpecs as $spec)
                            <div class="flex flex-col items-center text-center gap-2 group">
                                <div class="w-12 h-12 rounded-full bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 flex items-center justify-center group-hover:text-[#E03E2D] group-hover:bg-red-50 transition-colors duration-300">
                                    @if($spec['icon'] == 'calendar')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    @elseif($spec['icon'] == 'road')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @elseif($spec['icon'] == 'fuel')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/></svg>
                                    @elseif($spec['icon'] == 'transmission')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                                    @elseif($spec['icon'] == 'engine')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    @elseif($spec['icon'] == 'power')
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                    @else
                                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/></svg>
                                    @endif
                                </div>
                                <span class="text-[10px] text-gray-400 uppercase font-bold tracking-wider">{{ $spec['label'] }}</span>
                                <span class="text-sm font-bold text-gray-900 dark:text-white leading-tight">{{ $spec['value'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- 3. DESCRIERE --}}
            <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] p-6">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white">Descriere</h3>
                    <div class="text-xs text-gray-400">ID: {{ $service->id }}</div>
                </div>
                <div class="prose prose-sm dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 leading-relaxed whitespace-pre-line">
                    {{ $service->description }}
                </div>
            </div>

            {{-- 4. DETALII COMPLETE --}}
            @if(count($fullDetails) > 0)
                <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-[0_2px_10px_rgba(0,0,0,0.03)] p-6">
                    <h3 class="text-lg font-bold text-gray-900 dark:text-white mb-6">Detalii complete</h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-x-16 gap-y-0 text-sm">
                        @foreach($fullDetails as $label => $val)
                            <div class="flex justify-between py-3 border-b border-gray-100 dark:border-[#333]">
                                <span class="text-gray-500 dark:text-gray-400">{{ $label }}</span>
                                <span class="text-gray-900 dark:text-white font-medium text-right">{{ $val }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

        </div>

        {{-- RIGHT (SIDEBAR) --}}
        <div class="lg:col-span-4 space-y-6">
            <div class="sticky top-6 space-y-6">
                <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-[0_2px_15px_rgba(0,0,0,0.06)] border border-gray-100 dark:border-[#333] p-6">
                    <h1 class="text-lg font-bold text-gray-900 dark:text-white mb-2 leading-snug">{{ $service->title }}</h1>
                    <div class="flex items-center gap-1 text-xs text-gray-500 mb-6">
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 111.314 0z"/></svg>
                        {{ $service->county->name ?? 'România' }}
                    </div>

                    @if($service->price_value)
                        <div class="flex items-end gap-2 mb-1">
                            <span class="text-3xl font-black text-gray-900 dark:text-white">{{ $formattedPrice }}</span>
                            <span class="text-xl font-bold text-gray-500 mb-1">{{ $currency }}</span>
                        </div>
                        @if($service->price_type === 'negotiable')
                            <span class="inline-block text-[10px] bg-green-50 text-green-700 font-bold px-2 py-0.5 rounded uppercase tracking-wide">Negociabil</span>
                        @else
                            <span class="inline-block text-[10px] bg-gray-100 text-gray-500 font-bold px-2 py-0.5 rounded uppercase tracking-wide">Preț Fix</span>
                        @endif
                    @else
                        <div class="text-2xl font-bold text-blue-600">Preț la cerere</div>
                    @endif

                    <hr class="border-gray-100 dark:border-[#333] my-6">

                    <div class="flex items-start gap-4 mb-6">
                        <div class="w-10 h-10 rounded-full bg-blue-50 dark:bg-blue-900 text-[#E03E2D] flex items-center justify-center font-bold text-lg">
                            {{ substr($service->author_name ?? 'U', 0, 1) }}
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">Vânzător</p>
                            <h4 class="font-bold text-sm text-gray-900 dark:text:white">{{ $service->author_name ?? 'Utilizator' }}</h4>
                            <p class="text-[10px] text-gray-400">Membru din {{ $service->created_at->format('Y') }}</p>
                        </div>
                    </div>

                    <div class="space-y-3">
                        @if($isDeleted)
                            <div class="w-full bg-gray-100 py-3 rounded-lg text-center text-gray-400 font-bold text-sm">Anunț Dezactivat</div>
                        @elseif($hasPhone)
                            <button onclick="revealPhone('desktop', '{{ $rawPhone }}', '{{ $formattedPhone }}')" id="btn-phone-desktop" class="w-full bg-[#E03E2D] hover:bg-[#c92a1b] text-white font-bold py-3 rounded-lg shadow-lg shadow-red-500/20 transition-all hover:-translate-y-0.5 flex items-center justify-center gap-2 group">
                                <svg class="w-5 h-5 group-hover:rotate-12 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                <span id="txt-phone-desktop">Arată numărul</span>
                            </button>
                            <button class="w-full bg-white border-2 border-[#E03E2D] text-[#E03E2D] hover:bg-red-50 font-bold py-3 rounded-lg transition-colors text-sm">
                                Trimite mesaj
                            </button>
                        @else
                            <button class="w-full bg-[#E03E2D] text-white font-bold py-3 rounded-lg text-sm">
                                Trimite mesaj
                            </button>
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-[#1E1E1E] border border-blue-100 dark:border-blue-900 rounded-xl p-5 shadow-sm">
                    <h5 class="text-xs font-bold text-blue-800 dark:text-blue-300 flex items-center gap-2 mb-3 uppercase tracking-wide">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Siguranță
                    </h5>
                    <ul class="text-[11px] text-gray-600 dark:text-gray-400 space-y-2">
                        <li>• Nu trimiteți bani înainte de a vedea mașina.</li>
                        <li>• Verificați istoricul mașinii (VIN).</li>
                        <li>• Întâlniți-vă în locuri publice populate.</li>
                    </ul>
                </div>

            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function () {

        @if(!$isDeleted && count($images) > 1)
        var thumbsSwiper = new Swiper(".thumbs-swiper", {
            spaceBetween: 8,
            slidesPerView: 4.5,
            freeMode: true,
            watchSlidesProgress: true,
            breakpoints: {
                640: { slidesPerView: 5.5 },
                1024: { slidesPerView: 5.5 }
            }
        });
        @endif

        var mainSwiper = new Swiper(".main-swiper", {
            spaceBetween: 10,
            navigation: {
                nextEl: ".swiper-button-next",
                prevEl: ".swiper-button-prev",
            },
            @if(!$isDeleted && count($images) > 1)
            thumbs: {
                swiper: thumbsSwiper,
            },
            @endif
            on: {
                slideChange: function () {
                    const current = this.activeIndex + 1;
                    const total   = this.slides.length;
                    const counter = document.querySelector('.fraction-pagination');
                    if (counter) counter.innerText = `${current} / ${total}`;
                }
            }
        });

        window.revealPhone = function(type, raw, formatted) {
            const btnId = type === 'mobile' ? 'btn-phone-mobile' : 'btn-phone-desktop';
            const txtId = type === 'mobile' ? 'txt-phone-mobile' : 'txt-phone-desktop';
            const btn   = document.getElementById(btnId);
            const txt   = document.getElementById(txtId);

            if (btn && txt) {
                if (txt.innerText.includes('Arată') || txt.innerText.includes('Sună')) {
                    txt.innerText = formatted;
                    if (type === 'mobile') {
                        window.location.href = 'tel:' + raw;
                    } else {
                        btn.onclick = function() { window.location.href = 'tel:' + raw; };
                        btn.classList.remove('bg-[#E03E2D]', 'text-white');
                        btn.classList.add('bg-white', 'border-2', 'border-green-600', 'text-green-700');
                    }
                } else {
                    window.location.href = 'tel:' + raw;
                }
            }
        }
    });
</script>

@endsection
