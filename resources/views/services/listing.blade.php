@extends('layouts.app')

@php
    $cleanMetaLabel = static function ($value) {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', \Illuminate\Support\Str::ascii($value)));
    };

    $brandName = $cleanMetaLabel(optional($currentBrand ?? null)->name);
    $modelName = $cleanMetaLabel(optional($currentModel ?? null)->name);
    $carLabel = trim(implode(' ', array_filter([$brandName, $modelName])));
    $carLabel = $carLabel !== '' ? $carLabel : null;
    $hasBrandModel = $brandName && $modelName;

    $countyName = $cleanMetaLabel(optional($currentCounty ?? null)->name);
    $localityName = $cleanMetaLabel(optional($currentLocality ?? null)->name);
    $locationLabel = $localityName && $countyName
        ? $localityName . ', ' . $countyName
        : ($localityName ?: $countyName);

    if ($hasBrandModel && $localityName && $countyName) {
        $listingMetaTitle = 'Anunturi auto ' . $carLabel . ' in ' . $locationLabel . ' | iaAuto.ro';
        $listingMetaDescription = 'Cauti ' . $carLabel . ' in ' . $locationLabel . '? Vezi anunturi auto cu masini ' . $carLabel . ' de vanzare, second hand sau noi, de la proprietari si parcuri auto.';
    } elseif ($hasBrandModel && $countyName) {
        $listingMetaTitle = 'Anunturi auto ' . $carLabel . ' in ' . $countyName . ' - ' . $carLabel . ' de vanzare | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunturi auto ' . $carLabel . ' de vanzare in judetul ' . $countyName . '. Masini second hand si noi, de la proprietari si parcuri auto.';
    } elseif ($hasBrandModel) {
        $listingMetaTitle = 'Anunturi auto ' . $carLabel . ' - ' . $carLabel . ' de vanzare | iaAuto.ro';
        $listingMetaDescription = 'Cauti ' . $carLabel . ' de vanzare? Vezi anunturi auto cu ' . $carLabel . ' second hand si noi din Romania, de la proprietari si parcuri auto.';
    } elseif ($brandName && $localityName && $countyName) {
        $listingMetaTitle = 'Anunturi auto ' . $brandName . ' in ' . $locationLabel . ' | iaAuto.ro';
        $listingMetaDescription = 'Cauti ' . $brandName . ' in ' . $locationLabel . '? Vezi anunturi auto cu masini ' . $brandName . ' de vanzare, second hand sau noi, de la proprietari si parcuri auto.';
    } elseif ($brandName && $countyName) {
        $listingMetaTitle = 'Anunturi auto ' . $brandName . ' in ' . $countyName . ' - Masini ' . $brandName . ' de vanzare | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunturi auto ' . $brandName . ' de vanzare in judetul ' . $countyName . '. Masini second hand si noi, de la proprietari si parcuri auto.';
    } elseif ($brandName) {
        $listingMetaTitle = 'Anunturi auto ' . $brandName . ' - Masini ' . $brandName . ' de vanzare | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunturi auto ' . $brandName . ' de vanzare in Romania. Masini second hand si noi, de la proprietari si parcuri auto. Filtreaza dupa model, pret si an.';
    } elseif ($localityName && $countyName) {
        $listingMetaTitle = 'Anunturi auto ' . $locationLabel . ' - Masini de vanzare | iaAuto.ro';
        $listingMetaDescription = 'Cauti masini de vanzare in ' . $locationLabel . '? Vezi anunturi auto second hand si noi, de la proprietari si parcuri auto.';
    } elseif ($countyName) {
        $listingMetaTitle = 'Anunturi auto ' . $countyName . ' - Masini de vanzare in ' . $countyName . ' | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunturi auto din ' . $countyName . '. Masini de vanzare second hand si noi, de la proprietari si parcuri auto. Publica gratuit pe iaAuto.ro.';
    } else {
        $listingMetaTitle = 'Anunturi auto - Masini de vanzare second hand | iaAuto.ro';
        $listingMetaDescription = 'Cauti o masina? Vezi anunturi auto cu masini de vanzare second hand si noi din Romania. Filtreaza dupa marca, model, pret, an si kilometri.';
    }

    $showEarlyStageBanners = $showEarlyStageBanners ?? true; // TEMP: seteaza false cand site-ul are suficiente anunturi.
    $listingHasActiveFilters = $listingHasActiveFilters ?? false;
    $earlyStageTotalListings = isset($totalCount)
        ? (int) $totalCount
        : (isset($services) && method_exists($services, 'total')
            ? (int) $services->total()
            : (isset($services) && method_exists($services, 'count') ? (int) $services->count() : 0));
    $listingPagination = $listingPagination ?? [];
    $listingCurrentPage = (int) ($listingPagination['currentPage'] ?? max(1, (int) request('page', 1)));
    $listingTotalPages = (int) ($listingPagination['totalPages'] ?? 1);
    $listingCanonicalUrl = $listingPagination['canonicalUrl'] ?? url()->current();
    $listingPrevUrl = $listingPagination['prevUrl'] ?? null;
    $listingNextUrl = $listingPagination['nextUrl'] ?? null;
    $listingPageLinks = $listingPagination['pages'] ?? [];

    if ($listingCurrentPage > 1) {
        $pageLabel = 'Pagina ' . $listingCurrentPage;
        $listingMetaTitle = str_contains($listingMetaTitle, ' | iaAuto.ro')
            ? str_replace(' | iaAuto.ro', ' - ' . $pageLabel . ' | iaAuto.ro', $listingMetaTitle)
            : $listingMetaTitle . ' - ' . $pageLabel;
        $listingMetaDescription .= ' ' . $pageLabel . ' din ' . $listingTotalPages . ' cu anunturi active.';
    }
@endphp

@section('title', 'Anunțuri Auto - Anunturi Auto Second Hand')
@section('meta_title', e($listingMetaTitle))
@section('meta_description', e($listingMetaDescription))
@section('meta_image', asset('images/social-share.webp'))
@section('canonical')
    <link rel="canonical" href="{{ $listingCanonicalUrl }}">
    @if($listingPrevUrl)
        <link rel="prev" href="{{ $listingPrevUrl }}">
    @endif
    @if($listingNextUrl)
        <link rel="next" href="{{ $listingNextUrl }}">
    @endif
@endsection

@section('content')
<div class="listing-page-shell w-full pt-0 lg:pt-6 pb-12">
    <div class="flex flex-col gap-0 lg:flex-row lg:gap-6">
        {{-- Sidebar filtre (desktop) --}}
        <aside class="lg:w-[300px] lg:shrink-0">
            <div class="hidden lg:block mb-5">
                <nav class="flex items-center gap-1.5 text-sm" aria-label="Breadcrumb">
                    <a href="{{ route('services.index') }}"
                       class="rounded bg-blue-50 px-2 py-1 font-medium text-gray-800 transition hover:bg-blue-100 hover:text-[#C81424] dark:bg-[#1f2937] dark:text-gray-100 dark:hover:bg-[#2d3748] dark:hover:text-red-200">
                        Acasă
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="font-medium text-gray-900 dark:text-gray-100">Autoturisme</span>
                </nav>
                <p class="mt-4 text-3xl font-extrabold leading-tight text-gray-950 dark:text-white">Autoturisme</p>
                <p class="mt-1 text-sm leading-snug text-gray-600 dark:text-gray-300">
                    Autoturisme de vânzare - Găsește mașina potrivită pentru tine
                </p>
                <p class="mt-2 text-xs font-semibold text-gray-500 dark:text-gray-400">
                    {{ number_format($totalCount, 0, ',', '.') }} anunțuri disponibile
                </p>
            </div>

            <div id="filters-overlay" class="fixed inset-0 bg-black/40 z-[1000] hidden lg:hidden"></div>
            <div id="filters-panel"
                 class="fixed inset-0 z-[1001] hidden pointer-events-none lg:static lg:block lg:z-auto lg:pointer-events-auto">
                <div class="filters-panel-sheet pointer-events-auto bg-white dark:bg-[#1E1E1E] h-full lg:h-auto w-full max-w-md lg:max-w-none lg:rounded-2xl lg:shadow-md border border-gray-200 dark:border-[#333333] overflow-y-auto">
                    <div class="sticky top-0 z-20 flex items-center justify-between px-4 py-4 border-b border-gray-200 bg-white dark:bg-[#1E1E1E] dark:border-[#333333] lg:hidden">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Filtrează</h2>
                        <button type="button" id="close-filters" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#2d2d2d] dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <form id="search-form" class="p-4 space-y-4">
                        <input type="hidden" name="vehicle_type" id="vehicle-type" value="anunturi-auto-de-vanzare">
                        <input type="hidden" name="seller_type" id="seller-type" value="{{ request('seller_type', 'all') }}">

                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">De unde vrei să cumperi?</p>
                            <select id="seller-type-select" class="autovit-select listing-filter">
                                <option value="all" @selected(request('seller_type', 'all') === 'all')>Parcuri + Proprietari</option>
                                <option value="individual" @selected(request('seller_type') === 'individual')>Proprietari</option>
                                <option value="dealer" @selected(request('seller_type') === 'dealer')>Parcuri</option>
                            </select>
                        </div>

                        <select id="brand-filter" name="brand_id" class="autovit-select listing-filter">
                            <option value="">Marcă</option>
                            @php
                                $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
                            @endphp

                            @include('services.partials.brand_options', [
                                'brands' => $brands,
                                'selectedBrandId' => $currentBrandId,
                                'popularLabel' => 'Mărci Populare',
                                'allLabel' => 'Toate Mărcile',
                            ])
                        </select>

                        <select id="model-filter" name="model_id" class="autovit-select listing-filter bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                            <option value="">Model</option>
                        </select>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">An fabricație</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="year-min" name="year_min" placeholder="Min" value="{{ request('year_min', request('an_min')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                                <input type="number" id="year-max" name="year_max" placeholder="Max" value="{{ request('year_max', request('an_max')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Km</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="km-min" name="km_min" placeholder="Min" value="{{ request('km_min') }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                                <input type="number" id="km-max" name="km_max" placeholder="Max" value="{{ request('km_max') }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Preț (EUR)</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="price-min" name="price_min" placeholder="Min" value="{{ request('price_min', request('pret_min')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                                <input type="number" id="price-max" name="price_max" placeholder="Max" value="{{ request('price_max', request('pret_max')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                            </div>
                        </div>

                        <select id="body-filter" name="caroserie_id" class="autovit-select listing-filter">
                            <option value="">Caroserie</option>
                            @foreach($bodies as $body)
                                <option value="{{ $body->id }}">{{ $body->nume }}</option>
                            @endforeach
                        </select>

                        <select id="fuel-filter" name="combustibil_id" class="autovit-select listing-filter">
                            <option value="">Combustibil</option>
                            @foreach($fuels as $fuel)
                                <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                            @endforeach
                        </select>

                        <select id="gearbox-filter" name="cutie_viteze_id" class="autovit-select listing-filter">
                            <option value="">Cutie viteze</option>
                            @foreach($transmissions as $trans)
                                <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                            @endforeach
                        </select>

                        <select id="county-input" name="county_id" class="autovit-select listing-filter">
                            <option value="">Toată țara</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}" data-slug="{{ $county->slug }}" @selected((string)(request('county_id') ?: optional($currentCounty)->id) === (string)$county->id)>{{ $county->name }}</option>
                            @endforeach
                        </select>

                        <select id="locality-input" name="locality_id" class="autovit-select listing-filter" disabled>
                            <option value="">Oraș</option>
                        </select>

                        <div class="flex gap-2">
                            <button type="button" id="reset-btn" onclick="resetFilters()" disabled
                                    class="h-[46px] w-[46px] flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 transition-all duration-200 cursor-not-allowed dark:border-[#333333] dark:bg-[#252525] dark:text-gray-600"
                                    title="Șterge toate filtrele">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <button type="submit" class="h-[46px] flex-1 bg-[#C81424] hover:bg-[#94111B] text-white font-bold text-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Afișează rezultatele
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <div class="listing-mobile-heading lg:hidden mb-3">
                <nav class="flex items-center gap-1.5 text-sm" aria-label="Breadcrumb">
                    <a href="{{ route('services.index') }}"
                       class="rounded bg-blue-50 px-2 py-1 font-medium text-gray-800 transition hover:bg-blue-100 hover:text-[#C81424] dark:bg-[#1f2937] dark:text-gray-100 dark:hover:bg-[#2d3748] dark:hover:text-red-200">
                        Acasă
                    </a>
                    <span class="text-gray-400">/</span>
                    <span class="px-1 font-medium text-gray-900 dark:text-gray-100">Autoturisme</span>
                </nav>

                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-gray-950 dark:text-white">Autoturisme</h1>
                <p class="mt-1 max-w-2xl text-base leading-snug text-gray-700 dark:text-gray-300">
                    Autoturisme de vânzare - Găsește mașina potrivită pentru tine
                </p>
            </div>

            <div id="listing-actions-bar" class="sticky z-40 -mx-4 mb-4 bg-[#f6f7fb]/95 px-4 py-2.5 shadow-sm ring-1 ring-gray-200/80 backdrop-blur dark:bg-[#121212]/95 dark:ring-gray-800 sm:-mx-6 sm:px-6 lg:static lg:top-auto lg:z-auto lg:mx-0 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0 lg:backdrop-blur-0">
                <div class="listing-actions-row grid grid-cols-[0.58fr_0.68fr_0.9fr_1.28fr] items-stretch gap-2 lg:flex lg:items-center lg:justify-between lg:gap-3">
                    <button type="button" id="scroll-to-listing-top"
                        class="listing-action-button inline-flex h-11 min-w-0 items-center justify-center gap-1 rounded-md border border-gray-300 bg-white px-2 text-[13px] font-bold text-[#BA1422] shadow-sm transition hover:border-[#BA1422] hover:bg-[#fff7f8] hover:text-[#BA1422] dark:border-[#333333] dark:bg-[#1E1E1E] dark:text-red-200 dark:hover:border-red-700 dark:hover:bg-[#2a1013] lg:hidden">
                        <span class="shrink-0 text-base leading-none">↑</span>
                        <span class="truncate">Sus</span>
                    </button>

                    <button type="button" id="open-filters"
                        class="listing-action-button inline-flex h-11 min-w-0 items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white px-2 text-[13px] font-semibold text-[#C81424] shadow-sm transition hover:border-[#C81424] hover:bg-[#fff4f5] dark:border-[#333333] dark:bg-[#1E1E1E] dark:text-red-200 dark:hover:border-red-700 dark:hover:bg-[#2a1013] lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 7h10" />
                            <path d="M18 7h2" />
                            <path d="M16 5v4" />
                            <path d="M4 17h2" />
                            <path d="M10 17h10" />
                            <path d="M8 15v4" />
                        </svg>
                        <span class="truncate">Filtre</span>
                    </button>
                    <button type="button"
                        class="listing-action-button inline-flex h-11 min-w-0 items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white px-2 text-[13px] font-semibold text-gray-700 shadow-sm transition hover:border-[#C81424] hover:bg-[#fff4f5] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#1E1E1E] dark:text-gray-100 dark:hover:border-red-700 dark:hover:bg-[#2a1013] dark:hover:text-red-200 lg:w-auto lg:px-3 lg:text-sm lg:text-[#0F5CC0] lg:border-[#0F5CC0] lg:dark:text-red-200 lg:dark:border-red-900/50">
                        <svg xmlns="http://www.w3.org/2000/svg" class="hidden h-4 w-4 shrink-0 lg:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                        </svg>
                        <span class="truncate">Salvează</span>
                    </button>

                    <div class="listing-sort-compact min-w-0 lg:ml-auto lg:flex lg:w-auto lg:items-center lg:gap-2">
                        <label for="sort-select" class="sr-only text-sm font-semibold text-gray-600 dark:text-gray-300 lg:not-sr-only">Sortare</label>
                        <select id="sort-select" class="autovit-select listing-filter w-full lg:w-56">
                            <option value="newest" @selected(request('sort', 'newest') === 'newest')>Recomandată</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>Ieftine</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>Scumpe</option>
                            <option value="km_asc" @selected(request('sort') === 'km_asc')>Km crescător</option>
                            <option value="power_asc" @selected(request('sort') === 'power_asc')>Putere crescător</option>
                        </select>
                    </div>
                </div>
            </div>

            <div id="services-container" class="flex flex-col gap-4">
                @if($showEarlyStageBanners && !$listingHasActiveFilters && $earlyStageTotalListings < 500)
                    {{-- EARLY STAGE BANNER START --}}
                    <article class="relative flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#27272a] dark:bg-[#18181B] md:flex-row">
                        <div class="relative hidden w-full overflow-hidden bg-gradient-to-br from-[#fff4f5] via-white to-[#f4f6f8] p-5 dark:from-[#2a1013] dark:via-[#18181B] dark:to-[#111113] md:flex md:min-h-[220px] md:w-[320px] lg:w-[340px]">
                            <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-[#C81424]/12 to-transparent"></div>
                            <div class="relative flex min-h-[190px] w-full flex-col justify-between rounded-2xl border border-white/70 bg-white/70 p-4 shadow-[0_18px_50px_rgba(15,23,42,0.10)] backdrop-blur-sm dark:border-white/10 dark:bg-white/5">
                                <div class="flex items-center justify-between gap-3">
                                    <span class="rounded-full bg-[#C81424] px-3 py-1 text-[10px] font-black uppercase tracking-wide text-white shadow-sm">
                                        Start gratuit
                                    </span>
                                    <span class="rounded-full border border-red-100 bg-white px-3 py-1 text-[10px] font-black uppercase tracking-wide text-[#C81424] dark:border-white/10 dark:bg-white/10 dark:text-red-100">
                                        IaAuto.ro
                                    </span>
                                </div>

                                <div class="relative mt-4 flex flex-1 items-end justify-center">
                                    <div class="absolute bottom-2 h-10 w-4/5 rounded-full bg-slate-900/10 blur-xl dark:bg-black/40"></div>
                                    <img src="{{ asset('images/homepage-hero-car.webp') }}"
                                         alt="Autoturism de vanzare pe iaAuto.ro"
                                         class="relative z-10 max-h-32 w-full object-contain drop-shadow-2xl"
                                         loading="lazy">
                                </div>

                                <div class="mt-4 grid grid-cols-2 gap-2 text-[11px] font-extrabold text-slate-700 dark:text-slate-200">
                                    <div class="rounded-xl border border-slate-200/70 bg-white/80 px-3 py-2 dark:border-white/10 dark:bg-white/10">
                                        0 lei
                                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-400">taxa anunt</span>
                                    </div>
                                    <div class="rounded-xl border border-slate-200/70 bg-white/80 px-3 py-2 dark:border-white/10 dark:bg-white/10">
                                        Pe viata
                                        <span class="block text-[10px] font-bold text-slate-400 dark:text-slate-400">anunturi gratuite</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="flex min-w-0 flex-1 flex-col p-5 sm:p-6">
                            <div>
                                <span class="inline-flex w-fit items-center rounded-full bg-red-50 px-3 py-1 text-[11px] font-black uppercase tracking-wide text-[#C81424] dark:bg-[#2a1013] dark:text-red-200">
                                    Mesaj iaAuto.ro
                                </span>
                                <h2 class="mt-3 text-xl font-black leading-tight text-gray-950 dark:text-white sm:text-2xl">
                                    De ce sunt doar {{ number_format($earlyStageTotalListings, 0, ',', '.') }} mașini aici?
                                </h2>
                                <p class="mt-2 max-w-3xl text-sm font-semibold leading-relaxed text-gray-600 dark:text-gray-300 sm:text-base">
    Pentru că am lansat platforma recent și am ales să oferim anunțuri
    <strong class="font-extrabold text-gray-900 dark:text-white">GRATUIT pe viață</strong>
    și
    <strong class="font-extrabold text-gray-900 dark:text-white">fără taxe ascunse</strong>.
    Dacă vinzi
    <strong class="font-extrabold text-gray-900 dark:text-white">o mașină sau 1000</strong>,
    pune anunțuri aici și fii unul dintre
    <strong class="font-extrabold text-gray-900 dark:text-white">pionierii iaAuto</strong>!
</p>
                            </div>

                            <div class="mt-5 border-t border-gray-100 pt-4 dark:border-[#333]">
                                <a href="{{ route('services.create') }}"
                                   class="inline-flex w-full items-center justify-center rounded-xl bg-[#C81424] px-5 py-3 text-sm font-black uppercase tracking-wide text-white shadow-lg shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98] sm:w-auto">
                                    + Publică anunțul tău acum
                                </a>
                            </div>
                        </div>
                    </article>
                    {{-- EARLY STAGE BANNER END --}}
                @endif

                @include('services.partials.service_cards_horizontal', ['services' => $services])
            </div>

            <div id="loading-indicator" class="text-center py-8 {{ $services->isEmpty() || !$hasMore ? 'hidden' : '' }}">
                <svg class="animate-spin h-8 w-8 text-[#C81424] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3.003 7.91l2.997-2.619z"></path>
                </svg>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-2">Se încarcă...</p>
            </div>

            <div id="load-more-trigger" data-next-page="{{ $listingCurrentPage + 1 }}" data-has-more="{{ $hasMore ? 'true' : 'false' }}" style="height: 1px;"></div>

            @if($listingTotalPages > 1)
                <nav id="listing-pagination"
                     class="mt-6 rounded-xl border border-gray-200 bg-white px-4 py-4 text-sm text-gray-700 shadow-sm dark:border-[#333333] dark:bg-[#1E1E1E] dark:text-gray-200"
                     aria-label="Paginare anunturi"
                     data-current-page="{{ $listingCurrentPage }}"
                     data-total-pages="{{ $listingTotalPages }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p id="listing-pagination-summary" class="text-sm font-black text-gray-900 dark:text-white">
                                Pagina {{ $listingCurrentPage }} din {{ $listingTotalPages }}
                            </p>
                            <p class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                {{ number_format($earlyStageTotalListings, 0, ',', '.') }} anunturi gasite
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                        <a id="listing-pagination-prev"
                           @if($listingPrevUrl) href="{{ $listingPrevUrl }}" rel="prev" @else aria-disabled="true" @endif
                           class="inline-flex min-h-10 items-center justify-center rounded-lg border px-3 text-sm font-black transition {{ $listingPrevUrl ? 'border-gray-200 bg-white text-gray-700 hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#202024] dark:text-gray-100 dark:hover:border-red-700 dark:hover:text-red-200' : 'pointer-events-none cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300 dark:border-[#333333] dark:bg-[#18181B] dark:text-gray-600' }}">
                            Inapoi
                        </a>

                            <div id="listing-pagination-pages" class="flex flex-wrap items-center gap-1.5">
                                @foreach($listingPageLinks as $item)
                                    @if($item['isGap'] ?? false)
                                        <span class="inline-flex h-10 min-w-10 items-center justify-center px-1 text-sm font-black text-gray-400 dark:text-gray-500">...</span>
                                    @else
                                        <a href="{{ $item['url'] }}"
                                           @if($item['isCurrent']) aria-current="page" @endif
                                           class="inline-flex h-10 min-w-10 items-center justify-center rounded-lg border px-3 text-sm font-black transition {{ $item['isCurrent'] ? 'border-[#C81424] bg-[#C81424] text-white shadow-sm shadow-red-700/20' : 'border-gray-200 bg-white text-gray-700 hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#202024] dark:text-gray-100 dark:hover:border-red-700 dark:hover:text-red-200' }}">
                                            {{ $item['page'] }}
                                        </a>
                                    @endif
                                @endforeach
                            </div>

                        <a id="listing-pagination-next"
                           @if($listingNextUrl) href="{{ $listingNextUrl }}" rel="next" @else aria-disabled="true" @endif
                           class="inline-flex min-h-10 items-center justify-center rounded-lg border px-3 text-sm font-black transition {{ $listingNextUrl ? 'border-[#C81424] bg-[#C81424] text-white shadow-sm shadow-red-700/20 hover:bg-[#94111B]' : 'pointer-events-none cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300 dark:border-[#333333] dark:bg-[#18181B] dark:text-gray-600' }}">
                            Inainte
                        </a>
                        </div>
                    </div>
                </nav>
            @endif
        </div>
    </div>
</div>

<script>
    const homeUrl = "{{ route('cars.index') }}";
    const listUrl = "{{ url()->current() }}";
    const baseUrl = "{{ url('/') }}";
    const initialModelId = @json(optional($currentModel)->id);

    let isLoading = false;
    let currentPage = Number(document.getElementById('load-more-trigger')?.dataset.nextPage || {{ $listingCurrentPage + 1 }});
    let hasMore = document.getElementById('load-more-trigger')?.dataset.hasMore === 'true';

    const carData = @json($carData ?? []);
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const initialLocalityId = @json(optional($currentLocality)->id);
    const mobileQuery = window.matchMedia('(max-width: 1023px)');

    const domElements = {
        brand: document.getElementById('brand-filter'),
        model: document.getElementById('model-filter'),
        body: document.getElementById('body-filter'),
        fuel: document.getElementById('fuel-filter'),
        gear: document.getElementById('gearbox-filter'),
        county: document.getElementById('county-input'),
        locality: document.getElementById('locality-input'),
        priceMin: document.getElementById('price-min'),
        priceMax: document.getElementById('price-max'),
        kmMin: document.getElementById('km-min'),
        kmMax: document.getElementById('km-max'),
        yearMin: document.getElementById('year-min'),
        yearMax: document.getElementById('year-max'),
        sort: document.getElementById('sort-select'),
        scrollTopBtn: document.getElementById('scroll-to-listing-top'),
        resetBtn: document.getElementById('reset-btn'),
        container: document.getElementById('services-container'),
        loader: document.getElementById('loading-indicator'),
        trigger: document.getElementById('load-more-trigger'),
        vehicleType: document.getElementById('vehicle-type'),
        sellerType: document.getElementById('seller-type'),
        pagination: document.getElementById('listing-pagination'),
        paginationSummary: document.getElementById('listing-pagination-summary'),
        paginationPages: document.getElementById('listing-pagination-pages'),
        paginationPrev: document.getElementById('listing-pagination-prev'),
        paginationNext: document.getElementById('listing-pagination-next'),
    };

    function isMobileView() {
        return mobileQuery.matches;
    }

    function resetSelect(el, placeholder) {
        if (!el) return;
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled = true;
        el.classList.add('bg-gray-50', 'text-gray-400', 'cursor-not-allowed');
        el.value = "";
    }

    function enableSelect(el) {
        if (!el) return;
        el.disabled = false;
        el.classList.remove('bg-gray-50', 'text-gray-400', 'cursor-not-allowed');
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

    function enhanceSelect(select) {
        if (!select || customSelects.has(select)) return;

        const root = document.createElement('div');
        root.className = 'custom-select';
        ['listing-filter', 'w-full', 'sm:w-56'].forEach(className => {
            if (select.classList.contains(className)) root.classList.add(className);
        });

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

    function resetLocalities() {
        if (!domElements.locality) return;
        domElements.locality.innerHTML = '<option value="">Oraș</option>';
        domElements.locality.disabled = true;
    }

    function populateLocalities(localities, selectedId) {
        if (!domElements.locality) return;
        domElements.locality.innerHTML = '<option value="">Oraș</option>';
        localities.forEach(locality => {
            const option = document.createElement('option');
            option.value = locality.id;
            option.textContent = locality.name;
            option.dataset.slug = locality.slug;
            if (String(selectedId) === String(locality.id)) {
                option.selected = true;
            }
            domElements.locality.appendChild(option);
        });
        domElements.locality.disabled = false;
    }

    async function loadLocalities(countyId, selectedId = null) {
        if (!countyId) {
            resetLocalities();
            return;
        }

        try {
            const response = await fetch(`${localityBaseUrl}/${countyId}`);
            const data = await response.json();
            populateLocalities(data, selectedId);
        } catch (error) {
            console.error(error);
            resetLocalities();
        }
    }

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
        if (brandSlug) {
            path += `/${brandSlug}`;
        }
        if (brandSlug && modelSlug) {
            path += `/${modelSlug}`;
        }
        if (countySlug) {
            path += `/${countySlug}`;
        }
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
        addParam('caroserie_id', domElements.body?.value || '');
        addParam('combustibil_id', domElements.fuel?.value || '');
        addParam('cutie_viteze_id', domElements.gear?.value || '');
        addParam('pret_min', domElements.priceMin?.value || '');
        addParam('pret_max', domElements.priceMax?.value || '');
        addParam('km_min', domElements.kmMin?.value || '');
        addParam('km_max', domElements.kmMax?.value || '');
        addParam('an_min', domElements.yearMin?.value || '');
        addParam('an_max', domElements.yearMax?.value || '');
        addParam('sort', domElements.sort?.value || '', 'newest');

        const queryString = params.toString();
        return `${baseUrl}${path}${queryString ? `?${queryString}` : ''}`;
    }

    window.checkResetVisibility = function() {
        const btn = domElements.resetBtn;
        if (!btn) return;

        const filters = [
            domElements.brand, domElements.model,
            domElements.body, domElements.fuel, domElements.gear, domElements.county,
            domElements.locality, domElements.priceMin,
            domElements.priceMax, domElements.kmMin, domElements.kmMax,
            domElements.yearMin, domElements.yearMax
        ];

        const hasSellerFilter = domElements.sellerType && domElements.sellerType.value !== 'all';
        const hasAnyFilter = hasSellerFilter || filters.some(el => el && el.value !== '');

        if (hasAnyFilter) {
            btn.disabled = false;
            btn.classList.remove('bg-gray-50', 'text-gray-300', 'cursor-not-allowed', 'dark:bg-[#252525]', 'dark:text-gray-600');
            btn.classList.add('bg-white', 'text-[#C81424]', 'border-[#C81424]', 'hover:bg-red-50', 'cursor-pointer', 'shadow-sm', 'dark:bg-[#1E1E1E]', 'dark:text-red-200', 'dark:hover:bg-[#2a1013]');
        } else {
            btn.disabled = true;
            btn.classList.remove('bg-white', 'text-[#C81424]', 'border-[#C81424]', 'hover:bg-red-50', 'cursor-pointer', 'shadow-sm', 'dark:bg-[#1E1E1E]', 'dark:text-red-200', 'dark:hover:bg-[#2a1013]');
            btn.classList.add('bg-gray-50', 'text-gray-300', 'cursor-not-allowed', 'dark:bg-[#252525]', 'dark:text-gray-600');
        }
    };

    window.resetFilters = function() {
        window.location.href = homeUrl;
    };

    const paginationButtonBase = 'inline-flex min-h-10 items-center justify-center rounded-lg border px-3 text-sm font-black transition';
    const paginationButtonEnabled = 'border-gray-200 bg-white text-gray-700 hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#202024] dark:text-gray-100 dark:hover:border-red-700 dark:hover:text-red-200';
    const paginationButtonPrimary = 'border-[#C81424] bg-[#C81424] text-white shadow-sm shadow-red-700/20 hover:bg-[#94111B]';
    const paginationButtonDisabled = 'pointer-events-none cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300 dark:border-[#333333] dark:bg-[#18181B] dark:text-gray-600';
    const paginationPageBase = 'inline-flex h-10 min-w-10 items-center justify-center rounded-lg border px-3 text-sm font-black transition';
    const paginationPageEnabled = 'border-gray-200 bg-white text-gray-700 hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#202024] dark:text-gray-100 dark:hover:border-red-700 dark:hover:text-red-200';
    const paginationPageCurrent = 'border-[#C81424] bg-[#C81424] text-white shadow-sm shadow-red-700/20';
    const paginationGapClass = 'inline-flex h-10 min-w-10 items-center justify-center px-1 text-sm font-black text-gray-400 dark:text-gray-500';

    function updatePaginationLink(link, url, enabledClass = paginationButtonEnabled, relName = null) {
        if (!link) return;

        if (url) {
            link.href = url;
            link.removeAttribute('aria-disabled');
            if (relName) link.setAttribute('rel', relName);
            link.className = `${paginationButtonBase} ${enabledClass}`;
        } else {
            link.removeAttribute('href');
            if (relName) link.removeAttribute('rel');
            link.setAttribute('aria-disabled', 'true');
            link.className = `${paginationButtonBase} ${paginationButtonDisabled}`;
        }
    }

    function renderPaginationPages(pages) {
        if (!domElements.paginationPages || !Array.isArray(pages)) return;

        domElements.paginationPages.innerHTML = '';

        pages.forEach((item) => {
            if (item.isGap) {
                const gap = document.createElement('span');
                gap.className = paginationGapClass;
                gap.textContent = '...';
                domElements.paginationPages.appendChild(gap);
                return;
            }

            const link = document.createElement('a');
            link.href = item.url;
            link.textContent = item.page;
            link.className = `${paginationPageBase} ${item.isCurrent ? paginationPageCurrent : paginationPageEnabled}`;

            if (item.isCurrent) {
                link.setAttribute('aria-current', 'page');
            }

            domElements.paginationPages.appendChild(link);
        });
    }

    function updateListingPagination(meta) {
        if (!domElements.pagination || !meta) return;

        const current = Number(meta.currentPage || 1);
        const total = Number(meta.totalPages || 1);

        if (total <= 1) {
            domElements.pagination.classList.add('hidden');
            return;
        }

        domElements.pagination.classList.remove('hidden');
        domElements.pagination.dataset.currentPage = String(current);
        domElements.pagination.dataset.totalPages = String(total);

        if (domElements.paginationSummary) {
            domElements.paginationSummary.textContent = `Pagina ${current} din ${total}`;
        }

        updatePaginationLink(domElements.paginationPrev, meta.prevUrl, paginationButtonEnabled, 'prev');
        updatePaginationLink(domElements.paginationNext, meta.nextUrl, paginationButtonPrimary, 'next');
        renderPaginationPages(meta.pages);
    }

    window.loadServices = function(page) {
        const isNewFilter = page === 1;
        if (isLoading) return;
        if (!hasMore && !isNewFilter) return;

        if (isNewFilter) {
            currentPage = 2;
            hasMore = true;
            if (domElements.container) domElements.container.style.opacity = '0.5';
            if (domElements.trigger) domElements.trigger.dataset.hasMore = 'true';
            window.checkResetVisibility();
        } else {
            if (domElements.loader) domElements.loader.classList.remove('hidden');
        }

        isLoading = true;

        const params = new URLSearchParams({
            page: page,
            ajax: 1,
            vehicle_type: domElements.vehicleType?.value || '',
            seller_type: domElements.sellerType?.value || 'all',
            brand_id: domElements.brand?.value || '',
            model_id: domElements.model?.value || '',
            caroserie_id: domElements.body?.value || '',
            combustibil_id: domElements.fuel?.value || '',
            cutie_viteze_id: domElements.gear?.value || '',
            county_id: domElements.county?.value || '',
            locality_id: domElements.locality?.value || '',
            price_min: domElements.priceMin?.value || '',
            price_max: domElements.priceMax?.value || '',
            km_min: domElements.kmMin?.value || '',
            km_max: domElements.kmMax?.value || '',
            year_min: domElements.yearMin?.value || '',
            year_max: domElements.yearMax?.value || '',
            sort: domElements.sort?.value || '',
        });

        fetch(`${listUrl}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (isNewFilter) {
                if (domElements.container) {
                    domElements.container.innerHTML = data.html;
                    domElements.container.style.opacity = '1';
                }
            } else {
                if (domElements.container) domElements.container.insertAdjacentHTML('beforeend', data.html);
            }

            hasMore = !!data.hasMore;
            if (domElements.trigger) domElements.trigger.dataset.hasMore = hasMore ? 'true' : 'false';
            updateListingPagination(data.pagination);

            if (hasMore) currentPage++;

            if (hasMore && domElements.trigger) {
                observer.unobserve(domElements.trigger);
                observer.observe(domElements.trigger);
            }
        })
        .catch(err => console.error(err))
        .finally(() => {
            isLoading = false;
            if (domElements.loader) domElements.loader.classList.add('hidden');
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('select.autovit-select').forEach(enhanceSelect);

        window.checkResetVisibility();

        const filterOverlay = document.getElementById('filters-overlay');
        const filterPanel = document.getElementById('filters-panel');
        const openFilters = document.getElementById('open-filters');
        const closeFilters = document.getElementById('close-filters');
        const sellerSelect = document.getElementById('seller-type-select');

        const nav = document.getElementById('main-nav');
        let cachedNavHeight = nav ? Math.ceil(nav.offsetHeight) : 56;
        let currentNavVisibleHeight = Number(nav?.dataset.mobileVisibleHeight || cachedNavHeight);

        const applyMobileFiltersOffset = () => {
            document.documentElement.style.setProperty('--mobile-filters-top', `${Math.max(0, currentNavVisibleHeight)}px`);
        };

        const setMobileFiltersOffset = (height = null) => {
            if (height !== null) {
                cachedNavHeight = height;
            } else if (nav) {
                cachedNavHeight = Math.ceil(nav.offsetHeight) || cachedNavHeight;
            }

            currentNavVisibleHeight = Number(nav?.dataset.mobileVisibleHeight || cachedNavHeight);
            applyMobileFiltersOffset();
        };

        const closeMobileFilters = () => {
            filterOverlay?.classList.add('hidden');
            filterPanel?.classList.add('hidden');
            document.body.style.overflow = '';
            closeCustomSelects();
        };

        const openMobileFilters = () => {
            setMobileFiltersOffset();
            filterOverlay?.classList.remove('hidden');
            filterPanel?.classList.remove('hidden');
            filterPanel?.querySelector('.filters-panel-sheet')?.scrollTo({ top: 0 });
            document.body.style.overflow = 'hidden';
        };

        if (openFilters && filterOverlay && filterPanel) {
            openFilters.addEventListener('click', openMobileFilters);
        }

        domElements.scrollTopBtn?.addEventListener('click', () => {
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });

        [filterOverlay, closeFilters].forEach((el) => {
            if (el) {
                el.addEventListener('click', closeMobileFilters);
            }
        });

        filterPanel?.addEventListener('click', (event) => {
            if (event.target === filterPanel) {
                closeMobileFilters();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !filterPanel?.classList.contains('hidden')) {
                closeMobileFilters();
            }
        });

        mobileQuery.addEventListener?.('change', (event) => {
            setMobileFiltersOffset();
            if (!event.matches) {
                closeMobileFilters();
            }
        });

        window.addEventListener('main-nav-visibility-change', (event) => {
            currentNavVisibleHeight = Number(event.detail?.visibleHeight ?? cachedNavHeight);
            applyMobileFiltersOffset();
        });

        window.addEventListener('resize', () => setMobileFiltersOffset(), { passive: true });
        if (nav && 'ResizeObserver' in window) {
            new ResizeObserver((entries) => {
                setMobileFiltersOffset(Math.ceil(entries[0].contentRect.height));
            }).observe(nav);
        }
        setMobileFiltersOffset();

        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                if (isMobileView()) {
                    closeMobileFilters();
                }
                window.location.href = buildSearchUrl();
            });
        }

        if (domElements.county) {
            domElements.county.addEventListener('change', () => {
                loadLocalities(domElements.county.value);
                window.checkResetVisibility();
            });
        }

        if (domElements.locality) {
            domElements.locality.addEventListener('change', () => {
                window.checkResetVisibility();
            });
        }

        if (domElements.sort) {
            domElements.sort.addEventListener('change', () => {
                if (isMobileView()) {
                    window.location.href = buildSearchUrl();
                    return;
                }
                window.checkResetVisibility();
            });
        }

        if (sellerSelect) {
            sellerSelect.addEventListener('change', () => {
                if (domElements.sellerType) {
                    domElements.sellerType.value = sellerSelect.value || 'all';
                }
                window.checkResetVisibility();
            });
        }

        if (domElements.brand && domElements.brand.value) {
            const brandId = domElements.brand.value;

            resetSelect(domElements.model, 'Model');

            if (carData[brandId]) {
                enableSelect(domElements.model);
                carData[brandId].forEach(m => {
                    const selected = initialModelId && String(initialModelId) === String(m.id) ? 'selected' : '';
                    domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}" ${selected}>${m.name}</option>`;
                });

                if (initialModelId && domElements.model.value) {
                    domElements.model.dispatchEvent(new Event('change'));
                }
            }
        }

        if (domElements.brand) {
            domElements.brand.addEventListener('change', function () {
                const brandId = this.value;

                if (!brandId) {
                    resetSelect(domElements.model, 'Model');
                    window.checkResetVisibility();
                    return;
                }

                resetSelect(domElements.model, 'Model');

                if (carData[brandId]) {
                    enableSelect(domElements.model);
                    carData[brandId].forEach(m => {
                        domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}">${m.name}</option>`;
                    });
                }

                window.checkResetVisibility();
            });
        }

        if (domElements.model) {
            domElements.model.addEventListener('change', function () {
                window.checkResetVisibility();
            });
        }

        if (domElements.county && domElements.county.value) {
            loadLocalities(domElements.county.value, initialLocalityId);
        } else {
            resetLocalities();
        }

        [domElements.body, domElements.fuel, domElements.gear].forEach(el => {
            if (el) {
                el.addEventListener('change', () => {
                    window.checkResetVisibility();
                });
            }
        });

        [domElements.priceMin, domElements.priceMax, domElements.kmMin, domElements.kmMax, domElements.yearMin, domElements.yearMax].forEach(el => {
            if (el) {
                el.addEventListener('input', () => {
                    window.checkResetVisibility();
                });
            }
        });

        if (domElements.trigger) observer.observe(domElements.trigger);
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.custom-select')) {
            closeCustomSelects();
        }
    });

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            window.loadServices(currentPage);
        }
    }, { rootMargin: '0px 0px 400px 0px' });

    window.toggleHeart = function(btn, serviceId) {
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif

        const icon = btn.querySelector('svg');
        const isLiked = btn.getAttribute('aria-pressed') === 'true'
            || icon.classList.contains('text-[#C81424]')
            || icon.classList.contains('text-red-500');
        const nextLiked = !isLiked;

        if (isLiked) {
            icon.classList.remove('text-[#C81424]', 'fill-[#C81424]', 'text-red-500', 'fill-red-500', 'scale-110');
            icon.classList.add('fill-none');
            if (btn.classList.contains('md:hidden')) {
                icon.classList.add('text-white');
            } else {
                icon.classList.add('text-gray-600', 'dark:text-gray-300');
            }
        } else {
            icon.classList.remove('text-gray-600', 'dark:text-gray-300', 'text-white', 'fill-none');
            icon.classList.add('text-[#C81424]', 'fill-[#C81424]', 'scale-125');
            setTimeout(() => { icon.classList.remove('scale-125'); icon.classList.add('scale-110'); }, 200);
        }
        btn.setAttribute('aria-pressed', nextLiked ? 'true' : 'false');
        const labelTarget = (btn.getAttribute('aria-label') || '').split(':').slice(1).join(':').trim();
        btn.setAttribute('aria-label', `${nextLiked ? 'Scoate de la favorite' : 'Adauga la favorite'}${labelTarget ? ': ' + labelTarget : ''}`);

        fetch("{{ route('favorite.toggle') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}",
                "Accept": "application/json"
            },
            body: JSON.stringify({ service_id: serviceId })
        }).catch(err => console.error(err));
    }
</script>

<style>
    :root {
        --mobile-filters-top: 56px;
    }

    @media (max-width: 1023px) {
        .listing-page-shell {
            margin-top: -0.25rem;
        }

    #listing-actions-bar {
        position: sticky;
        top: var(--mobile-filters-top);
        z-index: 40;
        width: auto;
        max-width: none;
        margin-right: -1rem;
        margin-left: -1rem;
        padding: 3px 1rem;
        background: rgba(248, 249, 251, 0.58);
        border-top: 1px solid rgba(229, 231, 235, 0.28);
        border-bottom: 1px solid rgba(229, 231, 235, 0.28);
        box-shadow: 0 6px 14px rgba(15, 23, 42, 0.025);
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
    }

        .listing-actions-row {
            display: grid;
            grid-template-columns: minmax(0, 0.58fr) minmax(0, 0.68fr) minmax(0, 0.9fr) minmax(0, 1.28fr);
            align-items: stretch;
            gap: 0.5rem;
        }

    .listing-action-button {
        height: 42px;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
        line-height: 1.1;
        white-space: nowrap;
    }

    #listing-actions-bar #scroll-to-listing-top,
    #listing-actions-bar #open-filters {
        color: #BA1422;
    }

    #listing-actions-bar #scroll-to-listing-top:hover,
    #listing-actions-bar #open-filters:hover {
        border-color: #BA1422;
        background: #fff7f8;
        color: #BA1422;
    }

    .listing-sort-compact .custom-select-trigger {
        height: 42px;
        gap: 0.35rem;
        padding: 0 0.45rem 0 0.55rem;
        border: 1px solid #e5e7eb;
        border-radius: 8px;
        background: #ffffff;
        font-size: 0.78rem;
        font-weight: 600;
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.08);
    }

        .listing-sort-compact .custom-select-menu {
            left: auto;
            right: 0;
            min-width: min(220px, calc(100vw - 2rem));
        }

        #filters-overlay,
        #filters-panel {
            top: var(--mobile-filters-top);
            bottom: 0;
            height: auto;
        }

        #filters-panel .filters-panel-sheet {
            height: calc(100dvh - var(--mobile-filters-top));
            max-height: calc(100dvh - var(--mobile-filters-top));
            border-top-left-radius: 0;
            border-top-right-radius: 0;
        }
    }

    @media (min-width: 640px) and (max-width: 1023px) {
        #listing-actions-bar {
            margin-right: -1.5rem;
            margin-left: -1.5rem;
            padding-right: 1.5rem;
            padding-left: 1.5rem;
        }
    }

    @media (min-width: 768px) and (max-width: 1023px) {
        #listing-actions-bar {
            top: 56px;
        }
    }

    @media (max-width: 390px) {
        .listing-actions-row {
            gap: 0.375rem;
        }

        #scroll-to-listing-top > span:first-child,
        #open-filters svg,
        .listing-sort-compact .custom-select-chevron {
            display: none;
        }

        #scroll-to-listing-top,
        #open-filters,
        .listing-sort-compact .custom-select-trigger {
            gap: 0;
        }

        .listing-sort-compact .custom-select-trigger {
            justify-content: center;
        }

        .listing-action-button {
            gap: 0.3rem;
            padding-left: 0.3rem;
            padding-right: 0.3rem;
            font-size: 0.72rem;
        }

        .listing-sort-compact .custom-select-trigger {
            padding-left: 0.35rem;
            padding-right: 0.3rem;
            font-size: 0.7rem;
        }
    }

    @media (min-width: 1024px) {
        #listing-actions-bar {
            position: static;
            z-index: auto;
            margin-right: 0;
            margin-left: 0;
            padding: 0;
            background: transparent;
            border: 0;
            box-shadow: none;
            backdrop-filter: none;
        }

        .listing-actions-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 0.75rem;
        }

        .listing-sort-compact .custom-select {
            width: 14rem;
        }
    }

    .autovit-select {
        display: block;
        width: 100%;
        height: 46px;
        padding: 0 2rem 0 1rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #1f2937;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center;
        background-repeat: no-repeat;
        background-size: 1.25em 1.25em;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        appearance: none;
        transition: all 0.2s ease;
        text-overflow: ellipsis;
        white-space: nowrap;
        overflow: hidden;
    }

    .autovit-select:focus {
        outline: none;
        border-color: #C81424;
        box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.1);
    }

    .autovit-select.listing-filter {
        width: 100%;
    }

    .native-select-hidden {
        display: none !important;
    }

    .custom-select {
        position: relative;
        width: 100%;
    }

    .custom-select-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 100%;
        height: 46px;
        gap: 0.75rem;
        padding: 0 0.85rem 0 1rem;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        background: #ffffff;
        color: #111827;
        font-size: 0.9rem;
        font-weight: 600;
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
        box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.12);
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
        z-index: 80;
        display: none;
        max-height: min(20rem, 48vh);
        overflow-y: auto;
        padding: 0.35rem;
        border: 1px solid rgba(200, 20, 36, 0.22);
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
        background: #fff1f1;
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

    optgroup {
        font-weight: 700;
        color: #C81424;
        font-style: normal;
        background-color: #f9fafb;
    }
    option {
        color: #1f2937;
        padding: 4px;
        background-color: #fff;
    }

    @media (prefers-color-scheme: dark) {
        .autovit-select {
            background-color: #2d2d2d;
            border-color: #404040;
            color: #e5e7eb;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        }

        .autovit-select:disabled {
            background-color: #1a1a1a;
            color: #6b7280;
            cursor: not-allowed;
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
            border-color: rgba(200, 20, 36, 0.35);
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
            background: rgba(200, 20, 36, 0.16);
            color: #ffffff;
        }

        .custom-select-option.is-selected {
            background: #C81424;
            color: #ffffff;
        }

        optgroup,
        option {
            background-color: #252525;
            color: #e5e7eb;
        }
    }

@media (prefers-color-scheme: dark) and (max-width: 1023px) {
    #listing-actions-bar {
        background: rgba(18, 18, 18, 0.5);
        border-color: rgba(55, 65, 81, 0.28);
    }
}
</style>
@endsection
