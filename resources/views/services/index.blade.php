@extends('layouts.app')

@section('title', 'Anunturi Auto Second Hand & Noi - Masini de Vanzare')
@section('meta_title', 'Anunturi Auto Second Hand & Noi - Masini de Vanzare | iaAuto.ro')
@section('meta_description', 'Descopera mii de anunturi auto verificate. Cumpara masini second hand sau noi de la proprietari si parcuri auto din toata Romania. Filtreaza inteligent si gaseste-ti masina ideala pe iaAuto.ro!')
@section('meta_image', asset('images/social-share.webp'))

@php
    $showEarlyStageBanners = $showEarlyStageBanners ?? true; // TEMP: seteaza false cand site-ul are suficiente anunturi.
    $earlyStageTotalListings = isset($totalCount)
        ? (int) $totalCount
        : (isset($services) && method_exists($services, 'count') ? (int) $services->count() : 0);
@endphp

@section('hero')
{{-- HERO SECTION --}}
<div class="w-full bg-[linear-gradient(180deg,#fff7f8_0%,#ffffff_72%)] dark:bg-[linear-gradient(180deg,#171112_0%,#121212_76%)] pt-14 md:pt-20 lg:pt-20 pb-3 md:pb-4 lg:pb-3">
    <div class="homepage-hero-layout max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="homepage-hero-visual relative isolate -mx-4 flex min-h-[248px] items-center overflow-hidden rounded-none bg-white/70 px-4 py-6 sm:-mx-6 sm:min-h-[292px] sm:px-6 sm:py-8 md:min-h-[318px] md:py-9 lg:mx-0 lg:min-h-[300px] lg:rounded-3xl lg:px-10 lg:py-8 lg:shadow-[0_22px_70px_rgba(200,20,36,0.10)] dark:bg-[#171112] dark:lg:shadow-black/40 xl:min-h-[320px]"
             style="--homepage-hero-image: url('{{ asset('images/homepage-hero-car.webp') }}'); --homepage-hero-dark-image: url('{{ asset('images/homepage-hero-car-dark.webp') }}');">
            <div class="relative z-10 max-w-[20rem] sm:max-w-[26rem] md:max-w-[31rem] lg:max-w-[28rem]">
                <h1 class="text-[1.75rem] min-[360px]:text-[1.9rem] min-[430px]:text-[2rem] sm:text-[2.35rem] md:text-[2.9rem] lg:text-[2rem] xl:text-[2.35rem] 2xl:text-[2.6rem] font-black text-gray-950 dark:text-white tracking-tight leading-[0.98]">
                    Găsește mașina potrivită pentru tine
                </h1>
                <p class="homepage-hero-subtitle max-w-none whitespace-nowrap text-gray-500 dark:text-gray-400 mt-4 text-[0.82rem] min-[360px]:text-sm min-[430px]:text-[0.95rem] sm:text-base md:text-lg lg:text-base xl:text-lg font-bold leading-snug">
                    <span>Publică GRATUIT —</span>
                    <span>o mașină sau 100.</span>
                </p>
                <a href="{{ route('services.create') }}"
                   class="mt-5 inline-flex min-h-[48px] items-center justify-center rounded-lg bg-[#C81424] px-6 py-3 text-sm sm:text-base lg:min-h-[42px] lg:px-5 lg:py-2.5 lg:text-xs xl:text-sm font-black uppercase tracking-wide text-white shadow-lg shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98]">
                    PUBLICĂ ANUNȚ
                </a>
            </div>
        </div>

        <div class="homepage-filter-wrap -mt-3 md:-mt-5 lg:-mt-10 lg:px-8">

        {{-- 1. ZONA FILTRE (STÂNGA - aprox 60% - MAI LATĂ) --}}
        <div id="homepage-filter-panel" class="homepage-filter-panel w-full max-w-[920px] min-w-0 mx-auto bg-white/95 dark:bg-[#181516] rounded-xl shadow-[0_24px_70px_rgba(15,23,42,0.12)] dark:shadow-black/50 border border-red-100/70 dark:border-white/10 relative z-40 backdrop-blur">

            @php
                $populareNume = ['Audi', 'BMW', 'Dacia', 'Ford', 'Opel', 'Renault', 'Volkswagen', 'Mercedes-Benz', 'Skoda'];
                $brandsPopulare = $brands->whereIn('name', $populareNume)->sortBy('name');
                $toateMarcile = $brands->sortBy('name');
                $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
            @endphp

            <div class="homepage-quick-filters lg:hidden p-3">
                <div class="grid grid-cols-3 gap-2">
                    <div class="min-w-0">
                        <label for="homepage-quick-brand-filter" class="mb-1 block text-[13px] font-bold text-gray-900 dark:text-gray-100 sm:text-sm">Marcă</label>
                        <select id="homepage-quick-brand-filter" class="autovit-select homepage-quick-select w-full">
                            <option value="">Toate mărcile</option>
                            @if($brandsPopulare->isNotEmpty())
                                <optgroup label="Populari">
                                    @foreach($brandsPopulare as $brand)
                                        <option value="{{ $brand->id }}" @selected((string) $currentBrandId === (string) $brand->id)>{{ $brand->name }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                            <optgroup label="A-Z">
                                @foreach($toateMarcile as $brand)
                                    <option value="{{ $brand->id }}" @selected((string) $currentBrandId === (string) $brand->id)>{{ $brand->name }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label for="homepage-quick-model-filter" class="mb-1 block text-[13px] font-bold text-gray-900 dark:text-gray-100 sm:text-sm">Model</label>
                        <select id="homepage-quick-model-filter" class="autovit-select homepage-quick-select w-full bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                            <option value="">Toate modelele</option>
                        </select>
                    </div>

                    <div class="min-w-0">
                        <label for="homepage-quick-county-filter" class="mb-1 block text-[13px] font-bold text-gray-900 dark:text-gray-100 sm:text-sm">Județ</label>
                        <select id="homepage-quick-county-filter" class="autovit-select homepage-quick-select w-full">
                            <option value="">Toată țara</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}" @selected((string) request('county_id') === (string) $county->id)>{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="mt-3 grid grid-cols-[0.9fr_1.1fr] gap-2">
                    <button type="button" id="homepage-more-filters-toggle"
                        class="inline-flex h-11 min-w-0 items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white px-2 text-sm font-bold text-gray-800 shadow-sm transition hover:border-[#C81424] hover:bg-[#fff4f5] hover:text-[#C81424] dark:border-white/10 dark:bg-[#201d1e] dark:text-gray-100">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M4 7h10" />
                            <path d="M18 7h2" />
                            <path d="M16 5v4" />
                            <path d="M4 17h2" />
                            <path d="M10 17h10" />
                            <path d="M8 15v4" />
                        </svg>
                        <span class="homepage-more-filters-label truncate">Mai multe filtre</span>
                    </button>

                    <button type="button" id="homepage-quick-submit"
                        class="inline-flex h-11 min-w-0 items-center justify-center gap-2 rounded-lg bg-[#C81424] px-2 text-sm font-extrabold uppercase tracking-wide text-white shadow-md shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98]">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="11" cy="11" r="7" />
                            <path d="m20 20-3.5-3.5" />
                        </svg>
                        <span class="truncate">Afișează<span class="homepage-quick-submit-extra"> rezultatele</span></span>
                    </button>
                </div>
            </div>

            <div class="homepage-advanced-filters">

            {{-- HEADER FILTRE: Sursă Anunț --}}
            <div class="search-panel-header flex flex-col sm:flex-row sm:items-stretch sm:justify-between border-b border-red-100/70 dark:border-white/10">
                <div class="flex min-h-12 flex-1 items-center px-7 py-3 sm:py-0">
                    <span class="text-sm font-extrabold text-gray-700 dark:text-gray-200 uppercase tracking-wide">
                        De unde vrei sa cumperi?
                    </span>
                </div>

                {{-- Toggle Tabs --}}
                <div class="seller-tabs grid min-w-0 grid-cols-3 bg-white dark:bg-[#151515] border-t border-red-100/80 dark:border-white/10 sm:border-t-0 sm:border-l w-full sm:w-[430px] rounded-t-xl sm:rounded-tl-lg sm:rounded-tr-xl">
                    <button type="button" data-seller="all"
                    class="seller-tab min-w-0 px-2 sm:px-4 py-3 text-[11px] sm:text-xs font-bold transition-all duration-200 text-gray-900 bg-[#fff0f2] dark:text-white dark:bg-[#3a171c] rounded-tl-xl sm:rounded-tl-lg">
                        Toate
                    </button>
                    <button type="button" data-seller="individual"
                        class="seller-tab min-w-0 px-2 sm:px-4 py-3 text-[11px] sm:text-xs font-bold transition-all duration-200 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">
                        Proprietari
                    </button>
                    <button type="button" data-seller="dealer"
                        class="seller-tab min-w-0 px-2 sm:px-4 py-3 text-[11px] sm:text-xs font-bold transition-all duration-200 text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white rounded-tr-xl">
                        Parcuri Auto
                    </button>
                </div>
            </div>

            {{-- ZONA FORMULAR --}}
            <div class="px-6 pt-6 pb-4">
                <form id="search-form">
                    <input type="hidden" name="vehicle_type" id="vehicle-type" value="anunturi-auto-de-vanzare">
                    <input type="hidden" name="seller_type" id="seller-type" value="{{ request('seller_type', 'all') }}">

                    {{-- GRID FILTRE (2 coloane pe mobile, 4 pe wide) --}}
                    <div class="grid grid-cols-2 lg:grid-cols-2 xl:grid-cols-4 gap-3">

                        {{-- RÂNDUL 1 --}}
                        <div class="col-span-2 lg:col-span-1">
                            <label class="autovit-label">Marca</label>
                            <select id="brand-filter" name="brand_id" class="autovit-select">
                                <option value="">Toate mărcile</option>
                                @if($brandsPopulare->isNotEmpty())
                                    <optgroup label="Populari">
                                        @foreach($brandsPopulare as $brand)
                                            <option value="{{ $brand->id }}" data-slug="{{ $brand->slug }}" @selected($currentBrandId === $brand->id)>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif
                                <optgroup label="A-Z">
                                    @foreach($toateMarcile as $brand)
                                        <option value="{{ $brand->id }}" data-slug="{{ $brand->slug }}" @selected($currentBrandId === $brand->id)>
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="autovit-label">Model</label>
                            <select id="model-filter" name="model_id" class="autovit-select bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                                <option value="">Alege model</option>
                            </select>
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <label class="autovit-label">Generație</label>
                            <select id="generation-filter" name="car_generation_id" class="autovit-select bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                                <option value="">Generație</option>
                            </select>
                        </div>

                        <div class="col-span-2 lg:col-span-1">
                            <label class="autovit-label">Caroserie</label>
                            <select id="body-filter" name="caroserie_id" class="autovit-select">
                                <option value="">Oricare</option>
                                @foreach($bodies as $body)
                                    <option value="{{ $body->id }}">{{ $body->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RÂNDUL 2 --}}
                        <div class="col-span-1">
                            <label class="autovit-label">Combustibil</label>
                            <select id="fuel-filter" name="combustibil_id" class="autovit-select">
                                <option value="">Oricare</option>
                                @foreach($fuels as $fuel)
                                    <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-1">
                            <label class="autovit-label">Cutie</label>
                            <select id="gearbox-filter" name="cutie_viteze_id" class="autovit-select">
                                <option value="">Oricare</option>
                                @foreach($transmissions as $trans)
                                    <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- LOCAȚIE --}}
                        <div class="col-span-2">
                            <div class="grid grid-cols-2 gap-3">
                                <div class="col-span-1">
                                    <label class="autovit-label">Județ</label>
                                    <select id="county-input" name="county_id" class="autovit-select">
                                        <option value="">Toată țara</option>
                                        @foreach($counties as $county)
                                            <option value="{{ $county->id }}" data-slug="{{ $county->slug }}" @selected((string)request('county_id') === (string)$county->id)>{{ $county->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-1">
                                    <label class="autovit-label">Oraș</label>
                                    <select id="locality-input" name="locality_id" class="autovit-select" disabled>
                                        <option value="">Oraș</option>
                                    </select>
                                </div>
                            </div>
                        </div>

{{-- BUTOANE ACȚIUNE --}}
                        <div class="col-span-2 lg:col-span-2 xl:col-span-4 mt-1 flex items-center justify-between pt-4 border-t border-gray-100 dark:border-[#333]">

                            {{-- Buton Reset (Stânga) --}}
                            <button type="button" id="reset-btn" onclick="resetFilters()" disabled
                            class="h-[42px] px-4 rounded-lg text-[#C81424] font-bold text-xs
                                           transition-all duration-300 flex items-center gap-2 hover:bg-[#fff4f5] dark:hover:bg-[#2a1013]
                                           opacity-0 invisible pointer-events-none transform -translate-x-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                <span class="hidden md:inline">Reset filtre</span>
                            </button>

                            {{-- Buton Submit (Dreapta, mai mic) --}}
                            <button type="submit" class="h-[42px] px-8 bg-[#C81424] hover:bg-[#94111B] text-white font-bold text-sm rounded-lg shadow-md shadow-red-700/20 transition-all flex items-center gap-2 uppercase tracking-wide transform active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Afișează Rezultatele
                            </button>
                        </div>

                    </div>
                </form>
            </div>
            </div>
        </div>
        </div>

    </div>
</div>
@endsection

@section('content')

<div class="w-full mt-0">
    <div class="flex items-center justify-between mb-5 pb-3 border-b border-gray-100 dark:border-[#2C2C2C] md:mb-6">
        <div class="flex items-center gap-3">
             <div class="w-1.5 h-8 bg-[#C81424] rounded-full shadow-sm"></div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Anunțuri recente</h2>
        </div>

        <a href="{{ url('/anunturi-auto-de-vanzare') }}"
           class="inline-flex shrink-0 items-center gap-1.5 text-base font-extrabold text-[#C81424] transition hover:text-[#94111B] hover:underline sm:text-sm"
           aria-label="Vezi toate anunțurile auto">
            Vezi toate
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>

    {{-- GRID CARDURI VERTICALE --}}
    <div id="services-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 pb-6 relative z-0">
        @forelse($services as $service)
            @php
                $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
                $loc = $service->locality->name ?? '';
                $jud = $service->county->name ?? '';
                $locationLabel = $loc ? "$loc, $jud" : $jud;
                $listingTitle = $service->title ?: trim(($service->brandRel->name ?? '') . ' ' . ($service->modelRel->name ?? ''));
                $img = $service->main_image_url;
                $price = $service->price_value ? number_format($service->price_value, 0, ',', '.') : null;
                $priceBadge = $price ? ($service->price_type === 'negotiable' ? 'NEGOCIABIL' : 'PRET FIX') : null;
                $priceBadgeClass = 'bg-[#C81424] text-white';
                $yearLabel = $service->an_fabricatie ?: '-';
                $kmLabel = $service->km ? number_format($service->km, 0, '.', '.') . ' km' : '-';
                $fuelLabel = $service->combustibil->nume ?? '-';
                $gearboxLabel = $service->cutieViteze->nume ?? '-';
            @endphp

             <article class="group relative bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333] rounded-2xl overflow-hidden hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">
                <a href="{{ $service->public_url }}" class="block relative w-full aspect-[4/3] overflow-hidden bg-gray-200 dark:bg-[#121212]">
                    <img src="{{ $img }}" alt="{{ $listingTitle }}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out" loading="lazy">
                    <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-80"></div>

                    {{-- Preț --}}
                    <div class="absolute bottom-3 left-3 sm:bottom-4 sm:left-4 flex flex-col items-start z-10">
                        @if($price)
                            <span class="text-xl font-black text-white drop-shadow-lg tracking-tight sm:text-xl">{{ $price }} <span class="text-sm font-bold sm:text-sm">{{ $service->currency }}</span></span>
                            <span class="text-[10px] uppercase font-bold {{ $priceBadgeClass }} px-2 py-0.5 rounded shadow-sm mt-1 sm:text-[9px] sm:px-1.5">{{ $priceBadge }}</span>
                        @else
                            <span class="text-xl font-bold text-white drop-shadow-md sm:text-lg">La cerere</span>
                        @endif
                    </div>

                    {{-- Favorite --}}
                    <button onclick="event.preventDefault(); toggleHeart(this, {{ $service->id }})" class="absolute top-2 right-2 sm:top-3 sm:right-3 p-2.5 sm:p-2 rounded-full bg-black/20 hover:bg-white backdrop-blur-md transition-all duration-300 group/heart shadow-lg">
                        <svg class="w-6 h-6 sm:w-5 sm:h-5 transition-colors {{ $isFav ? 'text-[#C81424] fill-[#C81424]' : 'text-white fill-none group-hover/heart:text-[#C81424]' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                </a>

                <div class="p-3 sm:p-5 flex-1 flex flex-col">
                    <a href="{{ $service->public_url }}" class="block mb-4">
                        <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-tight line-clamp-2 sm:line-clamp-1 group-hover:text-[#C81424] transition-colors">{{ $listingTitle }}</h3>
                         <p class="text-sm font-semibold text-gray-500 dark:text-gray-400 mt-1 truncate uppercase tracking-wide sm:text-xs">{{ $service->putere ?? '-' }} CP • {{ $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, '', '.') : '-' }} cm³</p>
                    </a>

                    {{-- Grid Specificații --}}
                    <div class="grid grid-cols-4 gap-x-1 sm:gap-x-2 mb-4">
                        <div class="spec-pill">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>{{ $yearLabel }}</span>
                        </div>
                        <div class="spec-pill">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 13l3.8-4.4M5.6 18.4a9 9 0 1112.8 0" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14H6m12 0h-1.5M12 6.5V5" /></svg>
                            <span>{{ $kmLabel }}</span>
                        </div>
                         <div class="spec-pill">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21V5a2 2 0 012-2h5a2 2 0 012 2v16M6 21h11M9 8h5M16 7h1.5L20 9.5V18a2 2 0 01-2 2h-1" /><path stroke-linecap="round" stroke-linejoin="round" d="M20 10h-3" /></svg>
                            <span>{{ $fuelLabel }}</span>
                        </div>
                        <div class="spec-pill">
                            <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 5v14m10-14v14M7 12h10M12 5v7" /><circle cx="7" cy="5" r="2" /><circle cx="17" cy="5" r="2" /><circle cx="7" cy="19" r="2" /><circle cx="17" cy="19" r="2" /></svg>
                            <span>{{ $gearboxLabel }}</span>
                        </div>
                    </div>

                    <div class="mt-auto pt-4 border-t border-gray-100 dark:border-[#333] flex items-start justify-between gap-3 text-sm text-gray-500 dark:text-gray-400 sm:text-xs">
                        <div class="flex min-w-0 flex-1 items-start">
                            <svg class="w-3.5 h-3.5 mr-1.5 mt-0.5 shrink-0 text-[#C81424]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span class="leading-snug">{{ $locationLabel }}</span>
                        </div>
                        <a href="{{ $service->public_url }}"
                           class="inline-flex shrink-0 items-center gap-1 font-bold text-[#C81424] transition hover:text-[#94111B] hover:underline dark:text-red-300 dark:hover:text-red-200"
                           aria-label="Vezi detalii pentru {{ $listingTitle }}">
                            Vezi detalii &rarr;
                        </a>
                    </div>
                </div>
            </article>
        @empty
             <div class="col-span-full py-20 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#252525] mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Nu am găsit anunțuri</h3>
                <p class="text-gray-500 dark:text-gray-400 mt-2">Încearcă să resetezi filtrele pentru a vedea mai multe rezultate.</p>
            </div>
        @endforelse
    </div>

    @if($showEarlyStageBanners && $earlyStageTotalListings < 50)
        {{-- EARLY STAGE BANNER START --}}
        <section class="mb-8 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#18181B] sm:p-6 lg:flex lg:items-center lg:justify-between lg:gap-8">
            <div class="min-w-0">
                <span class="inline-flex items-center rounded-full bg-red-50 px-3 py-1 text-xs font-black uppercase tracking-wide text-[#C81424] dark:bg-[#2a1013] dark:text-red-200">
                    Mesaj iaAuto.ro
                </span>
                <h3 class="mt-3 text-xl font-black leading-tight text-gray-950 dark:text-white sm:text-2xl">
                    Cauți mai multe mașini? 🚗 Suntem la început de drum și creștem organic!
                </h3>
                <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-gray-600 dark:text-gray-300 sm:text-base">
                    Momentan avem puține anunțuri pentru că refuzăm să taxăm utilizatorii. Pe iaAuto.ro publici GRATUIT și NELIMITAT, mereu. Ajută-ne să umplem această pagină!
                </p>
            </div>

            <a href="{{ route('services.create') }}"
               class="mt-5 inline-flex w-full items-center justify-center rounded-xl bg-[#C81424] px-5 py-3 text-sm font-black uppercase tracking-wide text-white shadow-lg shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98] lg:mt-0 lg:w-auto lg:shrink-0">
                + Publică anunțul tău acum
            </a>
        </section>
        {{-- EARLY STAGE BANNER END --}}
    @endif

    @if($services->isNotEmpty())
        <div class="flex justify-center pb-12">
            <a href="{{ url('/anunturi-auto-de-vanzare') }}"
               class="inline-flex items-center gap-2 text-lg font-extrabold text-[#C81424] transition hover:text-[#94111B] hover:underline sm:text-base"
               aria-label="Vezi toate anunțurile auto">
                Vezi toate
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    @endif
</div>

<script>
    const baseUrl = "{{ url('/') }}";
    const carData = @json($carData ?? []);
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const initialLocalityId = @json(optional($currentLocality)->id);

    const domElements = {
        brand: document.getElementById('brand-filter'),
        model: document.getElementById('model-filter'),
        gen: document.getElementById('generation-filter'),
        body: document.getElementById('body-filter'),
        fuel: document.getElementById('fuel-filter'),
        gear: document.getElementById('gearbox-filter'),
        county: document.getElementById('county-input'),
        locality: document.getElementById('locality-input'),
        // Am eliminat radius
        resetBtn: document.getElementById('reset-btn'),
        vehicleType: document.getElementById('vehicle-type'),
        sellerType: document.getElementById('seller-type'),
        quickBrand: document.getElementById('homepage-quick-brand-filter'),
        quickModel: document.getElementById('homepage-quick-model-filter'),
        quickCounty: document.getElementById('homepage-quick-county-filter'),
        quickSubmit: document.getElementById('homepage-quick-submit'),
        quickToggle: document.getElementById('homepage-more-filters-toggle'),
        quickToggleLabel: document.querySelector('.homepage-more-filters-label'),
        homepageFilterPanel: document.getElementById('homepage-filter-panel'),
    };

    function resetSelect(el, placeholder) {
        if (!el) return;
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled = true;
        el.classList.add('bg-gray-50', 'text-gray-400', 'cursor-not-allowed', 'dark:bg-[#1a1a1a]', 'dark:text-gray-500');
        el.classList.remove('bg-white', 'text-gray-900', 'dark:bg-[#2d2d2d]', 'dark:text-gray-100');
        el.value = "";
        syncCustomSelect(el);
    }

    function enableSelect(el) {
        if (!el) return;
        el.disabled = false;
        el.classList.remove('bg-gray-50', 'text-gray-400', 'cursor-not-allowed', 'dark:bg-[#1a1a1a]', 'dark:text-gray-500');
        el.classList.add('bg-white', 'text-gray-900', 'dark:bg-[#2d2d2d]', 'dark:text-gray-100');
        syncCustomSelect(el);
    }

    const customSelects = new Map();

    function closeCustomSelects(except = null) {
        customSelects.forEach(({ root, button }) => {
            if (root === except) return;
            root.classList.remove('is-open');
            button.setAttribute('aria-expanded', 'false');
        });
    }

    function getSelectLabel(select) {
        const option = select.selectedOptions?.[0] || select.options?.[0];
        return option ? option.textContent.trim() : '';
    }

    function createCustomOption(select, option) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'custom-select-option';
        item.textContent = option.textContent.trim();
        item.dataset.value = option.value;
        item.setAttribute('role', 'option');
        item.setAttribute('aria-selected', option.selected ? 'true' : 'false');

        if (option.selected) item.classList.add('is-selected');
        if (option.value === '') item.classList.add('is-placeholder');
        if (option.disabled) item.disabled = true;

        item.addEventListener('click', () => {
            if (select.disabled || option.disabled) return;
            select.value = option.value;
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncCustomSelect(select);
            closeCustomSelects();
            customSelects.get(select)?.button.focus();
        });

        item.addEventListener('keydown', (event) => {
            const options = Array.from(customSelects.get(select)?.menu.querySelectorAll('.custom-select-option:not(:disabled)') || []);
            const index = options.indexOf(item);

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                options[Math.min(index + 1, options.length - 1)]?.focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                options[Math.max(index - 1, 0)]?.focus();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeCustomSelects();
                customSelects.get(select)?.button.focus();
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                item.click();
            }
        });

        return item;
    }

    function syncCustomSelect(select) {
        const state = customSelects.get(select);
        if (!state) return;

        const { root, button, label, menu } = state;
        label.textContent = getSelectLabel(select);
        button.disabled = select.disabled;
        button.setAttribute('aria-disabled', select.disabled ? 'true' : 'false');
        root.classList.toggle('is-disabled', select.disabled);

        menu.innerHTML = '';
        Array.from(select.children).forEach(child => {
            if (child.tagName === 'OPTGROUP') {
                const group = document.createElement('div');
                group.className = 'custom-select-group';

                const groupLabel = document.createElement('div');
                groupLabel.className = 'custom-select-group-label';
                groupLabel.textContent = child.label;
                group.appendChild(groupLabel);

                Array.from(child.children).forEach(option => {
                    group.appendChild(createCustomOption(select, option));
                });
                menu.appendChild(group);
                return;
            }

            if (child.tagName === 'OPTION') {
                menu.appendChild(createCustomOption(select, child));
            }
        });
    }

    function syncAllCustomSelects() {
        customSelects.forEach((_, select) => syncCustomSelect(select));
    }

    function enhanceSelect(select) {
        if (!select || customSelects.has(select)) return;

        const root = document.createElement('div');
        root.className = 'custom-select';

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'custom-select-trigger';
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');

        const label = document.createElement('span');
        label.className = 'custom-select-label';

        const icon = document.createElement('span');
        icon.className = 'custom-select-chevron';
        icon.innerHTML = '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M6 8l4 4 4-4"/></svg>';

        const menu = document.createElement('div');
        menu.className = 'custom-select-menu';
        menu.setAttribute('role', 'listbox');

        button.append(label, icon);
        root.append(button, menu);

        select.classList.add('native-select-hidden');
        select.setAttribute('tabindex', '-1');
        select.insertAdjacentElement('afterend', root);

        customSelects.set(select, { root, button, label, menu });
        syncCustomSelect(select);

        button.addEventListener('click', () => {
            if (select.disabled) return;
            const willOpen = !root.classList.contains('is-open');
            closeCustomSelects(root);
            root.classList.toggle('is-open', willOpen);
            button.setAttribute('aria-expanded', willOpen ? 'true' : 'false');

            if (willOpen) {
                const selected = menu.querySelector('.custom-select-option.is-selected:not(:disabled)');
                const first = menu.querySelector('.custom-select-option:not(:disabled)');
                setTimeout(() => (selected || first)?.focus(), 0);
            }
        });

        button.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowDown' && event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            button.click();
        });

        select.addEventListener('change', () => syncCustomSelect(select));

        const observer = new MutationObserver(() => syncCustomSelect(select));
        observer.observe(select, {
            attributes: true,
            childList: true,
            subtree: true,
            attributeFilter: ['disabled', 'class'],
        });
    }

    function setHomepageQuickModelEnabled(enabled) {
        if (!domElements.quickModel) return;
        domElements.quickModel.disabled = !enabled;
        domElements.quickModel.classList.toggle('bg-gray-50', !enabled);
        domElements.quickModel.classList.toggle('text-gray-400', !enabled);
        domElements.quickModel.classList.toggle('cursor-not-allowed', !enabled);
        syncCustomSelect(domElements.quickModel);
    }

    function populateHomepageQuickModels(selectedId = '') {
        if (!domElements.quickModel) return;

        const brandId = domElements.quickBrand?.value || domElements.brand?.value || '';
        domElements.quickModel.innerHTML = '<option value="">Toate modelele</option>';

        if (!brandId || !carData[brandId]?.length) {
            domElements.quickModel.value = '';
            setHomepageQuickModelEnabled(false);
            return;
        }

        carData[brandId].forEach((model) => {
            const option = document.createElement('option');
            option.value = model.id;
            option.textContent = model.name;
            domElements.quickModel.appendChild(option);
        });

        if (selectedId && Array.from(domElements.quickModel.options).some(option => String(option.value) === String(selectedId))) {
            domElements.quickModel.value = selectedId;
        }

        setHomepageQuickModelEnabled(true);
        syncCustomSelect(domElements.quickModel);
    }

    function syncHomepageQuickFiltersFromMain() {
        if (domElements.quickBrand && domElements.brand) {
            domElements.quickBrand.value = domElements.brand.value || '';
            syncCustomSelect(domElements.quickBrand);
        }

        populateHomepageQuickModels(domElements.model?.value || '');

        if (domElements.quickCounty && domElements.county) {
            domElements.quickCounty.value = domElements.county.value || '';
            syncCustomSelect(domElements.quickCounty);
        }
    }

    function applyHomepageQuickFiltersToMain() {
        const previousCounty = domElements.county?.value || '';

        if (domElements.brand && domElements.quickBrand && domElements.brand.value !== domElements.quickBrand.value) {
            domElements.brand.value = domElements.quickBrand.value || '';
            domElements.brand.dispatchEvent(new Event('change', { bubbles: true }));
            syncCustomSelect(domElements.brand);
        }

        if (domElements.model && domElements.quickModel && domElements.model.value !== domElements.quickModel.value) {
            domElements.model.value = domElements.quickModel.value || '';
            domElements.model.dispatchEvent(new Event('change', { bubbles: true }));
            syncCustomSelect(domElements.model);
        }

        if (domElements.county && domElements.quickCounty && domElements.county.value !== domElements.quickCounty.value) {
            domElements.county.value = domElements.quickCounty.value || '';
            if (previousCounty !== domElements.county.value) {
                resetLocalities();
            }
            domElements.county.dispatchEvent(new Event('change', { bubbles: true }));
            syncCustomSelect(domElements.county);
        }

        window.checkResetVisibility();
    }

    function resetLocalities() {
        if(domElements.locality) {
            domElements.locality.innerHTML = '<option value="">Oraș</option>';
            domElements.locality.disabled = true;
        }
    }

    async function loadLocalities(countyId, selectedId = null) {
        if (!countyId) { resetLocalities(); return; }
        try {
            const response = await fetch(`${localityBaseUrl}/${countyId}`);
            const data = await response.json();
            if(domElements.locality) {
                domElements.locality.innerHTML = '<option value="">Oraș</option>';
                data.forEach(l => {
                    const opt = document.createElement('option');
                    opt.value = l.id; opt.textContent = l.name;
                    opt.dataset.slug = l.slug;
                    if(String(selectedId) === String(l.id)) opt.selected = true;
                    domElements.locality.appendChild(opt);
                });
                domElements.locality.disabled = false;
            }
        } catch (error) { console.error(error); resetLocalities(); }
    }

    window.checkResetVisibility = function() {
        const btn = domElements.resetBtn;
        if (!btn) return;
        // Am scos domElements.radius din lista de verificat
        const filters = [domElements.brand, domElements.model, domElements.gen, domElements.body, domElements.fuel, domElements.gear, domElements.county, domElements.locality];
        const hasAnyFilter = filters.some(el => el && el.value !== '');

        btn.disabled = !hasAnyFilter;
        if(hasAnyFilter) {
             btn.classList.remove('opacity-0', 'invisible', 'translate-x-0', 'pointer-events-none');
             btn.classList.add('opacity-100', 'visible', 'translate-x-0');
        } else {
             btn.classList.add('opacity-0', 'invisible', '-translate-x-2', 'pointer-events-none');
             btn.classList.remove('opacity-100', 'visible', 'translate-x-0');
        }
    };

    window.resetFilters = function() {
        if (domElements.brand) domElements.brand.value = '';
        resetSelect(domElements.model, 'Alege model');
        resetSelect(domElements.gen, 'Generație');
        ['body','fuel','gear','county'].forEach(k => { if(domElements[k]) domElements[k].value = ''; });
        resetLocalities();
        syncAllCustomSelects();
        syncHomepageQuickFiltersFromMain();
        window.checkResetVisibility();
    };

    function buildSearchUrl() {
        const brandOption = domElements.brand?.selectedOptions?.[0];
        const modelOption = domElements.model?.selectedOptions?.[0];
        const countyOption = domElements.county?.selectedOptions?.[0];
        const localityOption = domElements.locality?.selectedOptions?.[0];
        const brandSlug = brandOption?.dataset?.slug;
        const modelSlug = modelOption?.dataset?.slug;
        const countySlug = countyOption?.dataset?.slug;
        const citySlug = localityOption?.dataset?.slug;
        const countyInPath = !!countySlug;
        const cityInPath = !!(countySlug && citySlug);

        let path = '/anunturi-auto-de-vanzare';
        if (brandSlug) path += `/${brandSlug}`;
        if (brandSlug && modelSlug) path += `/${modelSlug}`;
        if (countySlug) path += `/${countySlug}`;
        if (countySlug && citySlug) {
            path += `/${citySlug}`;
        }

        const params = new URLSearchParams();
        const addParam = (key, value, defaultValue = '') => {
            if (value && value !== defaultValue) params.set(key, value);
        };

        addParam('seller_type', domElements.sellerType?.value || '', 'all');
        addParam('brand_id', brandSlug ? '' : (domElements.brand?.value || ''));
        addParam('model_id', modelSlug ? '' : (domElements.model?.value || ''));
        addParam('county_id', countyInPath ? '' : (domElements.county?.value || ''));
        addParam('locality_id', cityInPath ? '' : (domElements.locality?.value || ''));
        addParam('car_generation_id', domElements.gen?.value || '');
        addParam('caroserie_id', domElements.body?.value || '');
        addParam('combustibil_id', domElements.fuel?.value || '');
        addParam('cutie_viteze_id', domElements.gear?.value || '');

        const queryString = params.toString();
        return `${baseUrl}${path}${queryString ? `?${queryString}` : ''}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('select.autovit-select').forEach(enhanceSelect);

        window.checkResetVisibility();

        const sellerTabs = document.querySelectorAll('.seller-tab');
        const sellerInput = domElements.sellerType;
        const setActiveSellerTab = (val) => {
            sellerTabs.forEach(tab => {
                const isActive = tab.dataset.seller === val;
                if (isActive) {
                    tab.classList.remove('text-gray-500', 'text-gray-900', 'hover:text-gray-900', 'bg-transparent', 'bg-white', 'dark:text-gray-400', 'dark:hover:text-white', 'dark:bg-[#333]');
                    tab.classList.add('text-[#8f111a]', 'bg-[#fff0f2]', 'dark:text-white', 'dark:bg-[#3a171c]');
                } else {
                    tab.classList.add('text-gray-500', 'hover:text-gray-900', 'bg-transparent', 'dark:text-gray-400', 'dark:hover:text-white');
                    tab.classList.remove('text-[#8f111a]', 'bg-[#fff0f2]', 'dark:text-white', 'dark:bg-[#3a171c]');
                }
            });
        };
        if (sellerTabs.length && sellerInput) {
            setActiveSellerTab(sellerInput.value || 'all');
            sellerTabs.forEach(tab => tab.addEventListener('click', () => {
                const val = tab.dataset.seller;
                sellerInput.value = val;
                setActiveSellerTab(val);
            }));
        }

        if (domElements.county) domElements.county.addEventListener('change', () => {
            loadLocalities(domElements.county.value);
            syncHomepageQuickFiltersFromMain();
            window.checkResetVisibility();
        });
        // Am eliminat listener-ul care activa radius
        if (domElements.locality) domElements.locality.addEventListener('change', () => { window.checkResetVisibility(); });

        const searchForm = document.getElementById('search-form');
        if (searchForm) searchForm.addEventListener('submit', (e) => { e.preventDefault(); window.location.href = buildSearchUrl(); });

        if (domElements.quickToggle && domElements.homepageFilterPanel) {
            domElements.quickToggle.addEventListener('click', () => {
                const isOpen = domElements.homepageFilterPanel.classList.toggle('is-expanded');
                if (domElements.quickToggleLabel) {
                    domElements.quickToggleLabel.textContent = isOpen ? 'Ascunde filtrele' : 'Mai multe filtre';
                }
                if (!isOpen) {
                    syncHomepageQuickFiltersFromMain();
                }
            });
        }

        if (domElements.quickBrand) {
            domElements.quickBrand.addEventListener('change', () => {
                populateHomepageQuickModels('');
                applyHomepageQuickFiltersToMain();
            });
        }

        if (domElements.quickModel) {
            domElements.quickModel.addEventListener('change', () => {
                applyHomepageQuickFiltersToMain();
            });
        }

        if (domElements.quickCounty) {
            domElements.quickCounty.addEventListener('change', () => {
                applyHomepageQuickFiltersToMain();
            });
        }

        if (domElements.quickSubmit) {
            domElements.quickSubmit.addEventListener('click', () => {
                applyHomepageQuickFiltersToMain();
                window.location.href = buildSearchUrl();
            });
        }

        const handleBrandChange = () => {
            const brandId = domElements.brand.value;
            resetSelect(domElements.model, 'Alege model');
            resetSelect(domElements.gen, 'Generație');
            if (brandId && carData[brandId]) {
                enableSelect(domElements.model);
                carData[brandId].forEach(m => domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}">${m.name}</option>`);
            }
            syncHomepageQuickFiltersFromMain();
            window.checkResetVisibility();
        };

        if (domElements.brand) {
            if(domElements.brand.value) handleBrandChange();
            domElements.brand.addEventListener('change', handleBrandChange);
        }

        if (domElements.model) {
            domElements.model.addEventListener('change', function() {
                const brandId = domElements.brand.value;
                const modelId = this.value;
                resetSelect(domElements.gen, 'Generație');
                if (brandId && modelId && carData[brandId]) {
                    const modelObj = carData[brandId].find(x => String(x.id) === String(modelId));
                    if (modelObj?.generations?.length) {
                        enableSelect(domElements.gen);
                        modelObj.generations.forEach(g => domElements.gen.innerHTML += `<option value="${g.id}">${g.name} (${g.start}-${g.end||'Prezent'})</option>`);
                    }
                }
                syncHomepageQuickFiltersFromMain();
                window.checkResetVisibility();
            });
        }

        [domElements.gen, domElements.body, domElements.fuel, domElements.gear].forEach(el => el && el.addEventListener('change', window.checkResetVisibility));

        if (domElements.county && domElements.county.value) {
            loadLocalities(domElements.county.value, initialLocalityId);
        }

        syncHomepageQuickFiltersFromMain();
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.custom-select')) {
            closeCustomSelects();
        }
    });

    window.toggleHeart = function(btn, serviceId) {
        @if(!auth()->check()) window.location.href = "{{ route('login') }}"; return; @endif
        const icon = btn.querySelector('svg');
        const isLiked = icon.classList.contains('text-[#C81424]');
        if (isLiked) {
            icon.classList.remove('text-[#C81424]', 'fill-[#C81424]');
            icon.classList.add('text-white', 'fill-none');
        } else {
            icon.classList.remove('text-white', 'fill-none');
            icon.classList.add('text-[#C81424]', 'fill-[#C81424]');
        }
        fetch("{{ route('favorite.toggle') }}", {
            method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ service_id: serviceId })
        }).catch(err => console.error(err));
    }
</script>
<style>
    .homepage-hero-visual {
        background-image:
            linear-gradient(90deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 255, 255, 0.97) 36%, rgba(255, 255, 255, 0.82) 48%, rgba(255, 255, 255, 0.24) 70%, rgba(255, 255, 255, 0.02) 100%),
            var(--homepage-hero-image);
        background-repeat: no-repeat;
        background-size: cover, cover;
        background-position: center, center right;
    }

    @media (max-width: 1023px) {
        .homepage-hero-visual {
            background-size: cover, auto 100%;
            background-position: center, 38% center;
        }

        .homepage-hero-subtitle {
            text-shadow: 0 1px 12px rgba(255, 255, 255, 0.92);
        }

        .homepage-filter-panel {
            border-radius: 1.25rem;
            box-shadow: 0 16px 44px rgba(15, 23, 42, 0.10);
        }

        .homepage-filter-panel:not(.is-expanded) .homepage-advanced-filters {
            display: none;
        }

        .homepage-filter-panel.is-expanded .homepage-advanced-filters {
            display: block;
            border-top: 1px solid rgba(229, 231, 235, 0.85);
        }

        .homepage-filter-panel.is-expanded .search-panel-header {
            border-bottom-color: rgba(229, 231, 235, 0.85);
        }

        .homepage-filter-panel .homepage-advanced-filters > .px-6 {
            padding: 0.85rem;
        }

        .homepage-quick-filters .custom-select-trigger {
            height: 42px;
            gap: 0.35rem;
            padding: 0 0.55rem 0 0.65rem;
            border-color: #d6dbe3;
            border-radius: 0.7rem;
            font-size: 0.86rem;
            font-weight: 800;
            box-shadow: none;
        }

        .homepage-quick-filters .custom-select-menu {
            z-index: 240;
            min-width: 100%;
            max-width: calc(100vw - 2rem);
        }

        .homepage-quick-filters .custom-select-label {
            font-size: inherit;
        }
    }

    @media (min-width: 640px) and (max-width: 1023px) {
        .homepage-hero-visual {
            background-position: center, 40% center;
        }

        .homepage-quick-filters {
            padding: 1rem;
        }

        .homepage-quick-filters .custom-select-trigger {
            font-size: 0.9rem;
            height: 46px;
        }
    }

    @media (max-width: 480px) {
        .homepage-hero-visual {
            background-position: center, 22% center;
        }

        .homepage-quick-submit-extra {
            display: none;
        }
    }

    @media (max-width: 390px) {
        .homepage-hero-visual {
            background-position: center, 16% center;
        }

        .homepage-quick-filters {
            padding: 0.65rem;
        }

        .homepage-quick-filters label {
            font-size: 0.8rem;
        }

        .homepage-quick-filters .custom-select-trigger {
            height: 40px;
            padding-left: 0.45rem;
            padding-right: 0.35rem;
            font-size: 0.8rem;
        }

        .homepage-quick-filters button {
            font-size: 0.82rem;
        }
    }

    @media (min-width: 1024px) {
        .homepage-hero-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(0, 0.95fr);
            align-items: start;
            gap: 2rem;
        }

        .homepage-filter-wrap {
            grid-column: 1;
            grid-row: 1;
            margin-top: 0 !important;
            padding-left: 0 !important;
            padding-right: 0 !important;
        }

        .homepage-hero-visual {
            grid-column: 2;
            grid-row: 1;
            align-items: flex-start;
            min-width: 0;
            border-radius: 0.85rem;
            box-shadow: none !important;
            background-color: transparent;
            background-size: cover, auto 100%;
            background-position: center, center right;
            padding-top: 1.75rem;
        }

        .homepage-hero-visual > div {
            max-width: 28rem;
        }

        .homepage-filter-panel {
            max-width: none;
            margin-left: 0;
            margin-right: 0;
        }

        .homepage-filter-panel .search-panel-header > .flex {
            padding-left: 1.5rem;
            padding-right: 1rem;
        }

        .homepage-filter-panel .search-panel-header span {
            white-space: nowrap;
            font-size: 0.78rem;
        }

        .homepage-filter-panel .seller-tabs {
            width: min(390px, 62%);
        }

        .homepage-filter-panel .homepage-advanced-filters > .px-6 {
            padding: 1.1rem 1.5rem 0.95rem;
        }

        .homepage-filter-panel .autovit-select,
        .homepage-filter-panel .custom-select-trigger {
            height: 42px;
            border-radius: 0.65rem;
            font-size: 0.82rem;
        }

        .homepage-filter-panel button[type="submit"] {
            height: 38px;
        }

        .homepage-quick-filters {
            display: none !important;
        }

        .homepage-advanced-filters {
            display: block !important;
        }
    }

    .autovit-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 800;
        text-transform: uppercase;
        color: #6b7280;
        margin-bottom: 0.25rem;
        margin-left: 0.25rem;
        letter-spacing: 0.05em;
    }

    .autovit-select {
        display: block; width: 100%; height: 48px; padding: 0 2rem 0 1rem;
        font-size: 0.9rem; font-weight: 600; color: #1f2937;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;
        border: 1px solid #e5e7eb; border-radius: 0.75rem; appearance: none; transition: all 0.2s ease;
        text-overflow: ellipsis; white-space: nowrap; overflow: hidden;
    }

    .autovit-select:hover { border-color: #C81424; }
    .autovit-select:focus { outline: none; border-color: #C81424; box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.12); }

    .native-select-hidden {
        display: none !important;
    }

    .custom-select {
        position: relative;
        width: 100%;
    }

    .custom-select.is-open {
        z-index: 220;
    }

    .custom-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 48px;
        gap: 0.75rem;
        padding: 0 0.9rem 0 1rem;
        border: 1px solid #e5e7eb;
        border-radius: 0.75rem;
        background: #ffffff;
        color: #1f2937;
        font-size: 0.9rem;
        font-weight: 700;
        text-align: left;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease;
    }

    .custom-select-label {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .custom-select-chevron {
        display: inline-flex;
        flex: 0 0 auto;
        color: #6b7280;
        transition: transform 0.18s ease, color 0.18s ease;
    }

    .custom-select-chevron svg {
        width: 1.15rem;
        height: 1.15rem;
        fill: none;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.8;
    }

    .custom-select.is-open .custom-select-trigger {
        border-color: #C81424;
        box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.13);
    }

    .custom-select.is-open .custom-select-chevron {
        color: #C81424;
        transform: rotate(180deg);
    }

    .custom-select.is-disabled .custom-select-trigger {
        background: #f9fafb;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .custom-select-menu {
        position: absolute;
        top: calc(100% + 0.35rem);
        left: 0;
        right: 0;
        z-index: 230;
        display: none;
        max-height: min(20rem, 48vh);
        overflow-y: auto;
        padding: 0.35rem;
        border: 1px solid rgba(200, 20, 36, 0.24);
        border-radius: 0.75rem;
        background: #ffffff;
        box-shadow: 0 18px 36px rgba(15, 23, 42, 0.16);
    }

    .custom-select.is-open .custom-select-menu {
        display: block;
    }

    .custom-select-group + .custom-select-group {
        margin-top: 0.25rem;
        padding-top: 0.25rem;
        border-top: 1px solid #f3f4f6;
    }

    .custom-select-group-label {
        padding: 0.45rem 0.65rem 0.3rem;
        color: #C81424;
        font-size: 0.72rem;
        font-weight: 800;
        letter-spacing: 0;
        text-transform: uppercase;
    }

    .custom-select-option {
        display: block;
        width: 100%;
        min-height: 38px;
        padding: 0.55rem 0.65rem;
        border-radius: 0.5rem;
        color: #111827;
        background: transparent;
        font-size: 0.9rem;
        font-weight: 500;
        text-align: left;
        transition: background-color 0.16s ease, color 0.16s ease;
    }

    .custom-select-option:hover,
    .custom-select-option:focus-visible {
        outline: none;
        background: #fff4f5;
        color: #94111B;
    }

    .custom-select-option.is-selected {
        background: #C81424;
        color: #ffffff;
        font-weight: 700;
    }

    .custom-select-option.is-selected:hover,
    .custom-select-option.is-selected:focus-visible {
        background: #94111B;
        color: #ffffff;
    }

    .custom-select-option.is-placeholder:not(.is-selected) {
        color: #6b7280;
    }

    .custom-select-option:disabled {
        color: #9ca3af;
        cursor: not-allowed;
    }


    .spec-pill {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: flex-start;
        gap: 0.35rem;
        min-width: 0;
        padding: 0.125rem;
        font-size: 0.875rem;
        font-weight: 600;
        line-height: 1.2;
        text-align: center;
        color: #2d3138;
    }
    .spec-pill svg {
        width: 1.4rem;
        height: 1.4rem;
        flex-shrink: 0;
        color: #16181d;
        stroke-width: 1.9;
    }
    .spec-pill span {
        min-width: 0;
        max-width: 100%;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    @media (min-width: 640px) {
        .spec-pill {
            font-size: 0.75rem;
            font-weight: 500;
        }

        .spec-pill svg {
            width: 1.25rem;
            height: 1.25rem;
        }
    }
    optgroup { font-weight: 700; color: #C81424; background-color: #f9fafb; }

    @media (prefers-color-scheme: dark) {
        .homepage-hero-visual {
            background-image:
                linear-gradient(90deg, rgba(18, 18, 18, 0.99) 0%, rgba(18, 18, 18, 0.94) 34%, rgba(18, 18, 18, 0.72) 52%, rgba(18, 18, 18, 0.18) 78%, rgba(18, 18, 18, 0.02) 100%),
                var(--homepage-hero-dark-image);
        }

        .autovit-label {
            color: #a3a3a3;
        }

        .autovit-select {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #e5e7eb;
        }

        .autovit-select:disabled {
            background-color: #1a1a1a;
            color: #6b7280;
        }

        .custom-select-trigger {
            border-color: #404040;
            background: #2d2d2d;
            color: #e5e7eb;
        }

        .custom-select.is-disabled .custom-select-trigger {
            background: #1a1a1a;
            color: #6b7280;
        }

        .custom-select-menu {
            border-color: rgba(200, 20, 36, 0.38);
            background: #252525;
            box-shadow: 0 18px 36px rgba(0, 0, 0, 0.36);
        }

        .custom-select-group + .custom-select-group {
            border-top-color: #333333;
        }

        .custom-select-option {
            color: #e5e7eb;
        }

        .custom-select-option:hover,
        .custom-select-option:focus-visible {
            background: rgba(200, 20, 36, 0.18);
            color: #ffffff;
        }

        .custom-select-option.is-selected {
            background: #C81424;
            color: #ffffff;
        }

        .spec-pill {
            color: #e5e7eb;
        }

        .spec-pill svg {
            color: #f4f4f5;
        }

        optgroup,
        option {
            background-color: #252525;
            color: #e5e7eb;
        }
    }

    @media (prefers-color-scheme: dark) and (max-width: 1023px) {
        .homepage-hero-subtitle {
            text-shadow: 0 1px 12px rgba(0, 0, 0, 0.68);
        }

        .homepage-filter-panel.is-expanded .homepage-advanced-filters,
        .homepage-filter-panel.is-expanded .search-panel-header {
            border-color: rgba(255, 255, 255, 0.10);
        }
    }
</style>
@endsection
