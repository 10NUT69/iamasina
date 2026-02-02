@extends('layouts.app')

@php
    use Illuminate\Support\Str;

    $siteBrand = 'MeseriasBun.ro';
    $isDeleted = $service->trashed();

    // --- DEALER DETECT ---
    $sellerUser = $service->user;
    $isDealer   = $sellerUser && ($sellerUser->user_type === 'dealer');

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

    // --- DATE AUTO (cu suport: generation -> brandRel/modelRel -> text fallback) ---
    $brandName =
        optional(optional(optional($service->generation)->model)->brand)->name
        ?: optional($service->brandRel ?? null)->name
        ?: ($service->brand ?? null);

    $modelName =
        optional(optional($service->generation)->model)->name
        ?: optional($service->modelRel ?? null)->name
        ?: ($service->model ?? null);

    $generationName = optional($service->generation)->name;

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

    $isImported = (bool) $service->importata;
    $isDamaged  = (bool) $service->avariata;
    $hasDpf     = (bool) $service->filtru_particule;

    // --- IMAGINI ---
    $images = [];
    if (!$isDeleted) {
        $images = is_string($service->images) ? json_decode($service->images, true) : ($service->images ?? []);
        if ($service->main_image_url) {
            $images = is_array($images) ? $images : [];
            array_unshift($images, basename($service->main_image_url));
            $images = array_values(array_unique($images));
        }
    }

    // URL-uri complete
    $fullImageUrls = array_map(function($img) {
        return Str::startsWith($img, ['http://', 'https://']) ? $img : asset('storage/services/'.$img);
    }, $images);

    // fallback dacă nu sunt imagini (nu crăpăm swiper/mozaic)
    if (empty($fullImageUrls) && $service->main_image_url) {
        $fullImageUrls = [$service->main_image_url];
    }
    if (empty($fullImageUrls)) {
        $fullImageUrls = [asset('images/defaults/placeholder.png')];
    }

    $seoLocation  = $service->locality
        ? trim($service->locality->name . ', ' . ($service->county->name ?? ''))
        : ($service->county->name ?? 'România');
    $fullSeoTitle = ($isDeleted ? 'INDISPONIBIL - ' : '') . $service->title;
    $currentUrl   = url()->current();
@endphp

@section('title', $fullSeoTitle)

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

    /* Custom Scrollbar */
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
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
                @foreach($fullImageUrls as $url)
                    <div class="swiper-slide">
                        <div class="swiper-zoom-container">
                            <img 
                                src="{{ $url }}" 
                                class="max-h-full max-w-full object-contain select-none" 
                                alt="Gallery Image"
                                style="-webkit-user-drag: none; -webkit-touch-callout: none;" 
                                draggable="false"
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
<div class="bg-[#F8F9FA] dark:bg-[#121212] min-h-screen font-sans text-gray-800 dark:text-gray-200 pb-32 lg:pb-12 pt-6">

    @if($isDeleted)
        <div class="max-w-[1280px] mx-auto px-4 mb-4">
            <div class="bg-red-50 text-red-600 px-4 py-3 rounded-xl border border-red-100 flex items-center gap-3 font-semibold shadow-sm">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                Anunț Indisponibil
            </div>
        </div>
    @endif

    <div class="max-w-[1280px] mx-auto px-4 grid grid-cols-1 lg:grid-cols-12 gap-8">

        {{-- === STÂNGA (Galerie, Info, Descriere) === --}}
        <div class="lg:col-span-8 space-y-6">

            {{-- NAVIGATION TOP BAR (Back + Breadcrumbs) --}}
            <div class="flex flex-col md:flex-row md:items-center gap-4 mb-2">
                <button onclick="history.back()" class="self-start inline-flex items-center gap-2 px-4 py-2 bg-white dark:bg-[#1E1E1E] text-gray-700 dark:text-gray-200 rounded-full shadow-sm hover:shadow-md border border-gray-200 dark:border-[#333] transition-all text-sm font-bold">
                    <svg class="w-4 h-4 text-[#E03E2D]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Înapoi
                </button>

                <div class="flex items-center text-sm overflow-x-auto no-scrollbar whitespace-nowrap gap-2">
                    <a href="/" class="font-bold text-gray-400 hover:text-[#E03E2D] transition">Acasă</a>
                    <span class="text-gray-300">/</span>
                    @if($brandName)
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $brandName }}</span>
                    @endif
                    @if($modelName)
                        <span class="text-gray-300">/</span>
                        <span class="font-bold text-blue-600 dark:text-blue-400">{{ $modelName }}</span>
                    @endif
                    <span class="text-gray-300">/</span>
                    <span class="text-gray-800 dark:text-gray-200 truncate max-w-[150px]">{{ Str::limit($service->title, 20) }}</span>
                </div>
            </div>

            {{-- GALERIE MOZAIC (Desktop) / SLIDER (Mobil) --}}
            <div class="rounded-2xl overflow-hidden shadow-sm bg-white dark:bg-[#1E1E1E] relative select-none border border-gray-100 dark:border-[#333]">

                {{-- Mobile: Slider Simplu --}}
                <div class="block md:hidden relative group">
                    <div class="swiper mobile-hero-swiper aspect-[4/3] bg-gray-100 dark:bg-black">
                        <div class="swiper-wrapper">
                            @foreach($fullImageUrls as $index => $url)
                                <div class="swiper-slide cursor-pointer" onclick="openGallery({{ $index }})">
                                    <img src="{{ $url }}" class="w-full h-full object-cover">
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
                            <img src="{{ $fullImageUrls[0] }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                            <div class="absolute inset-0 bg-black/0 group-hover:bg-black/10 transition"></div>
                        </div>
                    @endif

                    @if(isset($fullImageUrls[1]))
                        <div class="col-span-1 row-span-1 relative overflow-hidden group" onclick="openGallery(1)">
                            <img src="{{ $fullImageUrls[1] }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
                        </div>
                    @endif

                    @if(isset($fullImageUrls[2]))
                        <div class="col-span-1 row-span-1 relative overflow-hidden group" onclick="openGallery(2)">
                            <img src="{{ $fullImageUrls[2] }}" class="w-full h-full object-cover transition duration-500 group-hover:scale-105">
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
                <button onclick="copyLink()" class="flex-1 md:flex-none inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-lg text-sm font-bold transition shadow-sm hover:shadow-md">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    <span id="copy-text">Copiază Link</span>
                </button>
            </div>

            {{-- Mobile Title & Price (Vizibil doar pe mobil) --}}
            <div class="md:hidden space-y-2 mt-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white leading-tight">{{ $service->title }}</h1>
                <div class="flex items-center gap-2 text-gray-500 text-sm">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 111.314 0z"/></svg>
                    {{ $seoLocation }}
                </div>
                <div class="pt-2">
                    <span class="text-3xl font-extrabold text-[#E03E2D]">{{ $formattedPrice }} {{ $currency }}</span>
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
                        <div class="w-10 h-10 flex items-center justify-center bg-gray-50 dark:bg-[#333] rounded-lg text-gray-500">
                             <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5l5 5v11a2 2 0 01-2 2z"/></svg>
                        </div>
                        <span class="font-bold text-gray-500 text-sm uppercase tracking-wide">Serie Șasiu (VIN)</span>
                    </div>
                    <span class="font-mono font-bold text-lg text-gray-900 dark:text-white select-all break-all text-right">
                        {{ $service->vin }}
                    </span>
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
                                'Generație' => $generationName,
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
                                    <span class="text-gray-500">{{ $k }}</span>
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
                                    <span class="text-gray-500">{{ $k }}</span>
                                    <span class="font-semibold text-gray-900 dark:text-white">{{ $v }}</span>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>

                {{-- BADGES: Importată / Avariată / DPF --}}
                @if($isImported || $isDamaged || $hasDpf)
                    <div class="px-6 py-4 border-t border-gray-100 dark:border-[#333] flex flex-wrap gap-2 bg-white dark:bg-[#1E1E1E]">
                        @if($isImported)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-100">
                                Importată
                            </span>
                        @endif
                        @if($isDamaged)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-50 text-red-700 border border-red-100">
                                Avariată
                            </span>
                        @endif
                        @if($hasDpf)
                            <span class="px-3 py-1 rounded-full text-xs font-bold bg-green-50 text-green-700 border border-green-100">
                                Filtru particule (DPF)
                            </span>
                        @endif
                    </div>
                @endif
                
                {{-- VIN Scos de aici --}}
            </div>

        </div>

        {{-- === DREAPTA (Sidebar Sticky) === --}}
        <div class="lg:col-span-4 relative">
            <div class="sticky top-6 space-y-6">

                {{-- CARD TITLU & PRET (Doar Desktop) --}}
                <div class="bg-white dark:bg-[#1E1E1E] p-6 rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 dark:border-[#333] hidden md:block">
                    <h1 class="text-xl font-bold text-gray-900 dark:text-white mb-2 leading-snug">{{ $service->title }}</h1>
                    <div class="text-sm text-gray-500 mb-6 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 111.314 0z"/></svg>
                        {{ $seoLocation }}
                    </div>

                    <div class="flex items-baseline gap-2 mb-2">
                        <span class="text-4xl font-extrabold text-gray-900 dark:text-white">{{ $formattedPrice }}</span>
                        <span class="text-xl font-bold text-gray-500">{{ $currency }}</span>
                    </div>
                    @if($service->price_type === 'negotiable')
                        <span class="inline-block px-2 py-1 bg-green-100 text-green-700 text-xs font-bold rounded uppercase">Negociabil</span>
                    @endif
                </div>

                {{-- CARD VANZATOR --}}
                <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-[0_4px_20px_rgba(0,0,0,0.05)] border border-gray-100 dark:border-[#333] overflow-hidden">
                    <div class="p-6">
                        @if($isDealer)
                            {{-- === DEALER MODE === --}}
                            <div class="flex items-center justify-between mb-4">
                                <span class="bg-[#E03E2D] text-white text-[10px] font-bold px-2 py-1 rounded uppercase tracking-wider">Dealer Autorizat</span>
                                <div class="flex items-center text-green-600 text-xs font-bold gap-1">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                    Verificat
                                </div>
                            </div>

                            <div class="flex items-center gap-4 mb-5">
                                <div class="w-14 h-14 shrink-0 rounded-xl bg-gray-50 border border-gray-200 flex items-center justify-center text-xl font-black text-[#E03E2D]">
                                    {{ strtoupper(substr(($sellerUser->company_name ?? 'D'), 0, 1)) }}
                                </div>
                                <div class="overflow-hidden">
                                    <h4 class="font-bold text-lg text-gray-900 dark:text-white leading-tight truncate">
                                        {{ $sellerUser->company_name ?? 'Parc Auto' }}
                                    </h4>
                                    <p class="text-xs text-gray-500 mt-0.5 truncate">Dealer Auto • Înregistrat {{ optional($sellerUser->created_at)->format('Y') }}</p>
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
                                <div class="w-12 h-12 rounded-full bg-blue-100 text-blue-600 flex items-center justify-center font-bold text-lg">
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
                                <button disabled class="w-full bg-gray-200 text-gray-500 py-3.5 rounded-xl font-bold">Anunț Dezactivat</button>
                            @else
                                <button onclick="revealPhone('desktop', '{{ $rawPhone }}', '{{ $formattedPhone }}')" id="btn-phone-desktop" class="w-full bg-[#E03E2D] hover:bg-[#c92a1b] text-white font-bold py-3.5 rounded-xl shadow-lg shadow-red-500/20 transition transform active:scale-95 flex items-center justify-center gap-2 group">
                                    <svg class="w-5 h-5 group-hover:animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                    <span id="txt-phone-desktop">Arată Numărul</span>
                                </button>
                                <button class="w-full bg-white border border-gray-300 hover:bg-gray-50 text-gray-700 font-bold py-3.5 rounded-xl transition">
                                    Trimite Mesaj
                                </button>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- CARD SIGURANTA --}}
                <div class="bg-blue-50 dark:bg-blue-900/20 p-4 rounded-xl border border-blue-100 dark:border-blue-800 flex gap-3 items-start">
                    <svg class="w-5 h-5 text-blue-600 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                    <div>
                        <h5 class="font-bold text-blue-800 dark:text-blue-300 text-sm">Siguranță</h5>
                        <p class="text-xs text-blue-700 dark:text-blue-400 mt-1 leading-snug">Verificați istoricul mașinii înainte de achiziție. Nu plătiți în avans.</p>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

{{-- MOBILE BOTTOM BAR --}}
<div class="fixed bottom-0 left-0 right-0 z-40 lg:hidden glass border-t border-gray-200 px-4 py-3 safe-area-pb">
    <div class="flex gap-3">
        @if($isDeleted)
            <button class="w-full bg-gray-200 py-3 rounded-lg font-bold text-gray-500" disabled>Indisponibil</button>
        @else
            <button class="flex-1 bg-white border border-gray-300 text-gray-800 font-bold py-3 rounded-xl">Mesaj</button>
            <button onclick="revealPhone('mobile', '{{ $rawPhone }}', '{{ $formattedPhone }}')" id="btn-phone-mobile" class="flex-[2] bg-[#E03E2D] active:bg-[#c92a1b] text-white font-bold py-3 rounded-xl shadow-lg flex items-center justify-center gap-2">
                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                <span id="txt-phone-mobile">Sună</span>
            </button>
        @endif
    </div>
</div>

{{-- SCRIPTS --}}
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
    function copyLink() {
        navigator.clipboard.writeText(window.location.href).then(function() {
            let txt = document.getElementById('copy-text');
            let original = txt.innerText;
            txt.innerText = 'Copiat!';
            setTimeout(() => { txt.innerText = original; }, 2000);
        });
    }

    document.addEventListener('DOMContentLoaded', function () {

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
                }
            }
        });

        // 2. Lightbox & Zoom Logic
        const lightbox = document.getElementById('gallery-lightbox');
        let lightboxSwiperInstance = null;

        window.openGallery = function(index) {
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
                    btnEl.classList.remove('bg-[#E03E2D]', 'text-white');
                    btnEl.classList.add('bg-white', 'border-2', 'border-green-600', 'text-green-700');
                    btnEl.onclick = function() { window.location.href = 'tel:' + raw; };
                }
            } else {
                window.location.href = 'tel:' + raw;
            }
        };
    });
</script>
@endsection