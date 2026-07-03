@extends('layouts.app')

@php
    $cleanMetaLabel = static function ($value) {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
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
        $listingMetaTitle = 'Anunțuri auto ' . $carLabel . ' în ' . $locationLabel . ' | iaAuto.ro';
        $listingMetaDescription = 'Cauți ' . $carLabel . ' în ' . $locationLabel . '? Vezi anunțuri auto cu mașini ' . $carLabel . ' de vânzare, second hand sau noi, de la proprietari și parcuri auto.';
    } elseif ($hasBrandModel && $countyName) {
        $listingMetaTitle = 'Anunțuri auto ' . $carLabel . ' în ' . $countyName . ' - ' . $carLabel . ' de vânzare | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunțuri auto ' . $carLabel . ' de vânzare în județul ' . $countyName . '. Mașini second hand și noi, de la proprietari și parcuri auto.';
    } elseif ($hasBrandModel) {
        $listingMetaTitle = 'Anunțuri auto ' . $carLabel . ' - ' . $carLabel . ' de vânzare | iaAuto.ro';
        $listingMetaDescription = 'Cauți ' . $carLabel . ' de vânzare? Vezi anunțuri auto cu ' . $carLabel . ' second hand și noi din România, de la proprietari și parcuri auto.';
    } elseif ($brandName && $localityName && $countyName) {
        $listingMetaTitle = 'Anunțuri auto ' . $brandName . ' în ' . $locationLabel . ' | iaAuto.ro';
        $listingMetaDescription = 'Cauți ' . $brandName . ' în ' . $locationLabel . '? Vezi anunțuri auto cu mașini ' . $brandName . ' de vânzare, second hand sau noi, de la proprietari și parcuri auto.';
    } elseif ($brandName && $countyName) {
        $listingMetaTitle = 'Anunțuri auto ' . $brandName . ' în ' . $countyName . ' - Mașini ' . $brandName . ' de vânzare | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunțuri auto ' . $brandName . ' de vânzare în județul ' . $countyName . '. Mașini second hand și noi, de la proprietari și parcuri auto.';
    } elseif ($brandName) {
        $listingMetaTitle = 'Anunțuri auto ' . $brandName . ' - Mașini ' . $brandName . ' de vânzare | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunțuri auto ' . $brandName . ' de vânzare în România. Mașini second hand și noi, de la proprietari și parcuri auto. Filtrează după model, preț și an.';
    } elseif ($localityName && $countyName) {
        $listingMetaTitle = 'Anunțuri auto ' . $locationLabel . ' - Mașini de vânzare | iaAuto.ro';
        $listingMetaDescription = 'Cauți mașini de vânzare în ' . $locationLabel . '? Vezi anunțuri auto second hand și noi, de la proprietari și parcuri auto.';
    } elseif ($countyName) {
        $listingMetaTitle = 'Anunțuri auto ' . $countyName . ' - Mașini de vânzare în ' . $countyName . ' | iaAuto.ro';
        $listingMetaDescription = 'Vezi anunțuri auto din ' . $countyName . '. Mașini de vânzare second hand și noi, de la proprietari și parcuri auto. Publică gratuit pe iaAuto.ro.';
    } else {
        $listingMetaTitle = 'Anunțuri auto - Mașini de vânzare second hand | iaAuto.ro';
        $listingMetaDescription = 'Cauți o mașină? Vezi anunțuri auto cu mașini de vânzare second hand și noi din România. Filtrează după marcă, model, preț, an și kilometri.';
    }

    $listingTotalCount = isset($totalCount)
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
        $listingMetaDescription .= ' ' . $pageLabel . ' din ' . $listingTotalPages . ' cu anunțuri active.';
    }

    $autoListingUrl = static fn (...$segments): string => url('/' . implode('/', array_merge(
        ['anunturi-auto-de-vanzare'],
        array_map(fn ($segment) => \Illuminate\Support\Str::slug($segment), array_values(array_filter($segments)))
    )));

    $brandSlug = optional($currentBrand ?? null)->slug ?: ($brandName ? \Illuminate\Support\Str::slug($brandName) : null);
    $modelSlug = optional($currentModel ?? null)->slug ?: ($modelName ? \Illuminate\Support\Str::slug($modelName) : null);
    $countySlug = optional($currentCounty ?? null)->slug ?: ($countyName ? \Illuminate\Support\Str::slug($countyName) : null);
    $localitySlug = optional($currentLocality ?? null)->slug ?: ($localityName ? \Illuminate\Support\Str::slug($localityName) : null);

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

    $lastBreadcrumbIndex = array_key_last($breadcrumbItems);
    if ($lastBreadcrumbIndex !== null) {
        unset($breadcrumbItems[$lastBreadcrumbIndex]['item']);
    }

    $visualBreadcrumbItems = $breadcrumbItems;
    $visualLastBreadcrumbIndex = array_key_last($visualBreadcrumbItems);
    if ($visualLastBreadcrumbIndex !== null) {
        $visualBreadcrumbItems[$visualLastBreadcrumbIndex]['item'] = url()->full();
    }

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

@section('title', 'Anunțuri Auto - Anunțuri Auto Second Hand')
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

@section('schema')
<script type="application/ld+json">
@json($breadcrumbSchema)
</script>
@endsection

@section('content')
<div class="listing-page-shell w-full pt-3.5 pb-12 lg:pt-2.5">
    <div class="mb-3 hidden lg:block">
        <x-breadcrumbs :items="$visualBreadcrumbItems" data-listing-breadcrumb class="max-w-full" />
    </div>

    <div class="flex flex-col gap-0 lg:flex-row lg:gap-6">
        {{-- Sidebar filtre (desktop) --}}
        <aside class="lg:w-[340px] lg:shrink-0">
            <div class="hidden lg:block mb-5">
                <p class="text-3xl font-extrabold leading-tight text-gray-950 dark:text-white">Autoturisme</p>
                <p class="mt-1 text-sm leading-snug text-gray-600 dark:text-gray-300">
                    Autoturisme de vânzare - Găsește mașina potrivită pentru tine
                </p>
            </div>

            <div id="filters-overlay" class="fixed inset-0 bg-black/40 z-[1000] hidden lg:hidden"></div>
            <div id="filters-panel"
                 class="fixed inset-0 z-[1001] hidden pointer-events-none lg:static lg:block lg:z-auto lg:pointer-events-auto">
                <div class="filters-panel-sheet pointer-events-auto bg-white dark:bg-[#1E1E1E] h-full lg:h-auto w-full max-w-md lg:max-w-none lg:rounded-2xl lg:shadow-md border border-gray-200 dark:border-[#333333] overflow-y-auto lg:overflow-visible">
                    <div class="sticky top-0 z-20 flex items-center justify-between px-4 py-2.5 border-b border-gray-200 bg-white dark:bg-[#1E1E1E] dark:border-[#333333] lg:hidden">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Filtrează</h2>
                        <button type="button" id="close-filters" class="inline-flex h-9 w-9 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#2d2d2d] dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <form id="search-form" class="space-y-3 p-3 sm:space-y-4 sm:p-4">
                        @php
                            $selectedSellerType = in_array(request('seller_type'), ['all', 'individual', 'dealer'], true)
                                ? request('seller_type')
                                : 'all';
                            $sellerTypeOptions = [
                                ['value' => 'all', 'label' => 'Toți'],
                                ['value' => 'individual', 'label' => 'Proprietari'],
                                ['value' => 'dealer', 'label' => 'Parcuri'],
                            ];
                        @endphp

                        <input type="hidden" name="vehicle_type" id="vehicle-type" value="anunturi-auto-de-vanzare" hidden>
                        <input type="hidden" name="seller_type" id="seller-type" value="{{ $selectedSellerType }}" hidden>

                        <div>
                            <span class="mb-1 block text-xs font-bold uppercase tracking-wide text-gray-500 dark:text-gray-300">Tip Vânzător</span>
                            <div class="grid grid-cols-3 overflow-hidden rounded-lg border border-[#E6E8EC] bg-white dark:border-[#3a414b] dark:bg-[#20242b]">
                                @foreach($sellerTypeOptions as $sellerTypeOption)
                                    @php
                                        $isSelectedSellerType = $selectedSellerType === $sellerTypeOption['value'];
                                    @endphp
                                    <button
                                        type="button"
                                        data-seller-filter="{{ $sellerTypeOption['value'] }}"
                                        aria-pressed="{{ $isSelectedSellerType ? 'true' : 'false' }}"
                                        class="seller-filter-tab h-10 min-w-0 border-b-2 px-2 text-xs font-bold sm:text-sm {{ $isSelectedSellerType ? 'border-b-[#C81424] bg-white text-[#C81424] hover:bg-white hover:text-[#C81424] dark:bg-[#2a1013] dark:text-red-300 dark:hover:bg-[#3a171c] dark:hover:text-red-200' : 'border-b-transparent bg-white text-[#687080] hover:bg-[#F7F8FA] hover:text-[#30323A] dark:bg-transparent dark:text-gray-400 dark:hover:bg-[#2a2f36] dark:hover:text-white' }}"
                                    >
                                        {{ $sellerTypeOption['label'] }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        @php
                            $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
                            $currentModelId = isset($currentModel) ? $currentModel->id : null;
                            $selectedBrandId = request('brand_id', $currentBrandId);
                            $selectedModelId = request('model_id', $currentModelId);
                            $selectedCountyId = request('county_id', optional($currentCounty)->id);
                            $selectedLocalityId = request('locality_id', optional($currentLocality)->id);
                            $numericFilterGroups = [
                                [
                                    [
                                        'id' => 'year-min',
                                        'name' => 'year_min',
                                        'placeholder' => 'Anul de la',
                                        'value' => request('year_min', request('an_min')),
                                    ],
                                    [
                                        'id' => 'year-max',
                                        'name' => 'year_max',
                                        'placeholder' => 'Anul până la',
                                        'value' => request('year_max', request('an_max')),
                                    ],
                                ],
                                [
                                    [
                                        'id' => 'km-min',
                                        'name' => 'km_min',
                                        'placeholder' => 'Km de la',
                                        'value' => request('km_min'),
                                    ],
                                    [
                                        'id' => 'km-max',
                                        'name' => 'km_max',
                                        'placeholder' => 'Km până la',
                                        'value' => request('km_max'),
                                    ],
                                ],
                                [
                                    [
                                        'id' => 'price-min',
                                        'name' => 'price_min',
                                        'placeholder' => 'Preț de la',
                                        'value' => request('price_min', request('pret_min')),
                                    ],
                                    [
                                        'id' => 'price-max',
                                        'name' => 'price_max',
                                        'placeholder' => 'Preț până la',
                                        'value' => request('price_max', request('pret_max')),
                                    ],
                                ],
                            ];
                            $numericFilterInputClass = 'listing-filter w-full h-[46px] px-3 pr-9 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#C81424] focus:ring-2 focus:ring-[#C81424]/10 outline-none dark:bg-[#20242b] dark:border-[#3a414b] dark:text-white dark:placeholder-white';
                            $numericFilterClearClass = 'ia-combobox__clear !right-1.5';
                        @endphp

                        <div class="grid grid-cols-2 gap-2">
                            <x-combobox
                                id="brand-filter"
                                name="brand_id"
                                label="Marca"
                                placeholder="Marca"
                                :options="$brands"
                                option-label="name"
                                :selected="$selectedBrandId"
                                class="listing-filter"
                            />

                            <x-combobox
                                id="model-filter"
                                name="model_id"
                                label="Model"
                                placeholder="Model"
                                :options="$currentModel ? collect([$currentModel]) : collect()"
                                :selected="$selectedModelId"
                                :disabled="!$selectedBrandId"
                                class="listing-filter"
                            />
                        </div>

                        @foreach($numericFilterGroups as $numericFilterGroup)
                            <div>
                                <div class="grid grid-cols-2 gap-2">
                                    @foreach($numericFilterGroup as $numericFilter)
                                        <div class="relative" data-clearable-filter>
                                            <input
                                                type="number"
                                                inputmode="numeric"
                                                id="{{ $numericFilter['id'] }}"
                                                name="{{ $numericFilter['name'] }}"
                                                placeholder="{{ $numericFilter['placeholder'] }}"
                                                aria-label="{{ $numericFilter['placeholder'] }}"
                                                value="{{ $numericFilter['value'] }}"
                                                class="{{ $numericFilterInputClass }}"
                                            >
                                            <button
                                                type="button"
                                                class="{{ $numericFilterClearClass }}"
                                                aria-label="Șterge {{ $numericFilter['placeholder'] }}"
                                                data-clear-filter-input
                                                @if($numericFilter['value'] === null || $numericFilter['value'] === '') hidden @endif
                                            >&times;</button>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        <div class="grid grid-cols-2 gap-2">
                            <x-combobox
                                id="body-filter"
                                name="caroserie_id"
                                label="Tip caroserie"
                                placeholder="Tip caroserie"
                                :options="$bodies"
                                option-label="nume"
                                :selected="request('caroserie_id')"
                                class="listing-filter"
                            />

                            <x-combobox
                                id="fuel-filter"
                                name="combustibil_id"
                                label="Combustibil"
                                placeholder="Combustibil"
                                :options="$fuels"
                                option-label="nume"
                                :selected="request('combustibil_id')"
                                class="listing-filter"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-2">
                            <x-combobox
                                id="gearbox-filter"
                                name="cutie_viteze_id"
                                label="Transmisie"
                                placeholder="Transmisie"
                                :options="$transmissions"
                                option-label="nume"
                                :selected="request('cutie_viteze_id')"
                                class="listing-filter"
                            />

                            <x-combobox
                                id="county-input"
                                name="county_id"
                                label="Județ"
                                placeholder="Județ"
                                :options="$counties"
                                option-label="name"
                                :selected="$selectedCountyId"
                                class="listing-filter"
                            />
                        </div>

                        <x-combobox
                            id="locality-input"
                            name="locality_id"
                            label="Localitate"
                            placeholder="Localitate"
                            :options="$currentLocality ? collect([$currentLocality]) : collect()"
                            option-label="name"
                            :selected="$selectedLocalityId"
                            :disabled="!$selectedCountyId"
                            class="listing-filter"
                        />

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
                <x-breadcrumbs :items="$visualBreadcrumbItems" data-listing-breadcrumb />

                <h1 class="mt-3 text-3xl font-extrabold leading-tight text-gray-950 dark:text-white">Autoturisme</h1>
                <p class="mt-1 max-w-2xl text-base leading-snug text-gray-700 dark:text-gray-300">
                    Autoturisme de vânzare - Găsește mașina potrivită pentru tine
                </p>
            </div>

            @php
                $listingActionButtonBaseClass = 'listing-action-button inline-flex h-11 min-w-0 items-center justify-center gap-1.5 rounded-md border border-gray-300 bg-white px-2 text-[13px] font-bold text-gray-800 shadow-sm transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C81424]/20 dark:border-[#333333] dark:bg-[#202024] dark:text-gray-100';
                $listingActionButtonClass = $listingActionButtonBaseClass . ' listing-action-button--interactive';
                $listingActionButtonPassiveClass = $listingActionButtonBaseClass . ' listing-action-button--passive';
            @endphp

            <div id="listing-actions-bar" class="sticky z-40 -mx-4 mb-4 bg-[#f6f7fb]/95 px-4 py-2.5 shadow-sm ring-1 ring-gray-200/80 backdrop-blur dark:bg-[#121212]/95 dark:ring-gray-800 sm:-mx-6 sm:px-6 lg:static lg:top-auto lg:z-auto lg:mx-0 lg:bg-transparent lg:p-0 lg:shadow-none lg:ring-0 lg:backdrop-blur-0">
                <div class="listing-actions-row grid grid-cols-[0.58fr_0.68fr_0.74fr_1.5fr] items-stretch gap-2 lg:flex lg:items-center lg:justify-between lg:gap-3">
                    <button type="button" id="scroll-to-listing-top"
                        class="{{ $listingActionButtonPassiveClass }} lg:hidden">
                        <span class="shrink-0 text-base leading-none">↑</span>
                        <span class="listing-action-label">Sus</span>
                    </button>

                    <button type="button" id="open-filters"
                        class="{{ $listingActionButtonClass }} lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                            <path d="M4 7h10" />
                            <path d="M18 7h2" />
                            <path d="M16 5v4" />
                            <path d="M4 17h2" />
                            <path d="M10 17h10" />
                            <path d="M8 15v4" />
                        </svg>
                        <span class="listing-action-label">Filtre</span>
                    </button>
                    <button type="button" id="save-search-btn"
                        class="{{ $listingActionButtonClass }} listing-action-button--save-search lg:w-auto lg:px-3 lg:text-sm">
                        <span class="listing-action-label">
                            <span>Salvează</span>
                            <span>căutarea</span>
                        </span>
                    </button>

                    <div class="listing-sort-compact min-w-0 lg:ml-auto lg:flex lg:w-auto lg:items-center lg:gap-2">
                        <x-combobox
                            id="sort-select"
                            name="sort_select"
                            label="Sortare"
                            placeholder="Sortare"
                            :options="[
                                ['value' => 'newest', 'label' => 'Recomandată'],
                                ['value' => 'price_asc', 'label' => 'Ieftine'],
                                ['value' => 'price_desc', 'label' => 'Scumpe'],
                                ['value' => 'km_asc', 'label' => 'Km crescător'],
                                ['value' => 'power_asc', 'label' => 'Putere crescător'],
                            ]"
                            :selected="request('sort') ?: 'newest'"
                            :searchable="false"
                            class="listing-filter listing-sort-combobox w-full lg:w-56"
                        />
                    </div>
                </div>
            </div>

            <div id="services-container" class="flex flex-col gap-4">
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
                     aria-label="Paginare anunțuri"
                     data-current-page="{{ $listingCurrentPage }}"
                     data-total-pages="{{ $listingTotalPages }}">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                        <div>
                            <p id="listing-pagination-summary" class="text-sm font-black text-gray-900 dark:text-white">
                                Pagina {{ $listingCurrentPage }} din {{ $listingTotalPages }}
                            </p>
                            <p id="listing-total-summary" class="mt-1 text-xs font-semibold text-gray-500 dark:text-gray-400">
                                {{ number_format($listingTotalCount, 0, ',', '.') }} anunțuri găsite
                            </p>
                        </div>

                        <div class="flex flex-wrap items-center gap-2">
                        <a id="listing-pagination-prev"
                           @if($listingPrevUrl) href="{{ $listingPrevUrl }}" rel="prev" @else aria-disabled="true" @endif
                           class="inline-flex min-h-10 items-center justify-center rounded-lg border px-3 text-sm font-black transition {{ $listingPrevUrl ? 'border-gray-200 bg-white text-gray-700 hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333333] dark:bg-[#202024] dark:text-gray-100 dark:hover:border-red-700 dark:hover:text-red-200' : 'pointer-events-none cursor-not-allowed border-gray-200 bg-gray-50 text-gray-300 dark:border-[#333333] dark:bg-[#18181B] dark:text-gray-600' }}">
                            Înapoi
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
                            Înainte
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
    const autoCatalogUrls = {
        brands: "{{ route('ajax.brands') }}",
        modelsByBrand: "{{ route('ajax.models.by.brand') }}",
        bodies: "{{ route('ajax.bodies') }}",
        fuels: "{{ route('ajax.fuels') }}",
        transmissions: "{{ route('ajax.transmissions') }}",
        counties: "{{ route('ajax.counties') }}",
    };

    let isLoading = false;
    let currentPage = Number(document.getElementById('load-more-trigger')?.dataset.nextPage || {{ $listingCurrentPage + 1 }});
    let hasMore = document.getElementById('load-more-trigger')?.dataset.hasMore === 'true';
    let activeListUrl = window.location.href;
    let listingRequestId = 0;
    let activeListingController = null;
    let closeMobileFilters = () => {};

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
        saveSearch: document.getElementById('save-search-btn'),
        scrollTopBtn: document.getElementById('scroll-to-listing-top'),
        resetBtn: document.getElementById('reset-btn'),
        container: document.getElementById('services-container'),
        loader: document.getElementById('loading-indicator'),
        trigger: document.getElementById('load-more-trigger'),
        vehicleType: document.getElementById('vehicle-type'),
        sellerType: document.getElementById('seller-type'),
        pagination: document.getElementById('listing-pagination'),
        paginationSummary: document.getElementById('listing-pagination-summary'),
        totalSummary: document.getElementById('listing-total-summary'),
        paginationPages: document.getElementById('listing-pagination-pages'),
        paginationPrev: document.getElementById('listing-pagination-prev'),
        paginationNext: document.getElementById('listing-pagination-next'),
        breadcrumbs: Array.from(document.querySelectorAll('[data-listing-breadcrumb]')),
    };

    function isMobileView() {
        return mobileQuery.matches;
    }

    function setSaveSearchButtonActive(isActive) {
        domElements.saveSearch?.classList.toggle('listing-action-button--active', Boolean(isActive));
    }

    function resetSelect(el, placeholder) {
        if (!el) return;
        if (window.iaCombobox?.get(el)) {
            window.iaCombobox.setOptions(el, [], '');
            window.iaCombobox.disable(el);
            return;
        }
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled = true;
        el.classList.add('bg-gray-50', 'text-gray-400', 'cursor-not-allowed');
        el.value = "";
    }

    function enableSelect(el) {
        if (!el) return;
        if (window.iaCombobox?.get(el)) {
            window.iaCombobox.enable(el);
            return;
        }
        el.disabled = false;
        el.classList.remove('bg-gray-50', 'text-gray-400', 'cursor-not-allowed');
    }

    function clearButtonForFilterInput(input) {
        return input?.closest('[data-clearable-filter]')?.querySelector('[data-clear-filter-input]') || null;
    }

    function syncClearableFilterInput(input) {
        const button = clearButtonForFilterInput(input);

        if (button) {
            button.hidden = !input?.value;
        }
    }

    function initClearableFilterInputs(inputs) {
        inputs.filter(Boolean).forEach((input) => {
            const button = clearButtonForFilterInput(input);

            syncClearableFilterInput(input);

            input.addEventListener('input', () => syncClearableFilterInput(input));
            input.addEventListener('change', () => syncClearableFilterInput(input));

            button?.addEventListener('click', () => {
                if (!input.value) return;

                input.value = '';
                input.dispatchEvent(new Event('input', { bubbles: true }));
                input.dispatchEvent(new Event('change', { bubbles: true }));
                input.focus();
            });
        });
    }

    const catalogCache = new Map();
    let brandsLoaded = false;
    let countiesLoaded = false;
    let modelsLoadedForBrand = null;

    function catalogLabel(item, labelKey = 'name') {
        return String(item?.[labelKey] ?? item?.name ?? item?.nume ?? item?.label ?? '').trim();
    }

    function catalogOption(item, labelKey = 'name', group = '') {
        const label = catalogLabel(item, labelKey);

        return {
            value: item?.id,
            label,
            name: item?.name ?? label,
            slug: item?.slug ?? '',
            group,
        };
    }

    function normaliseCatalogText(value) {
        return String(value || '')
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase()
            .trim();
    }

    function sortBrandsAlphabetically(brands) {
        return [...brands].sort((left, right) => {
            const leftName = normaliseCatalogText(left?.name);
            const rightName = normaliseCatalogText(right?.name);
            const leftOther = ['altul', 'alta-marca'].includes(String(left?.slug || ''))
                || ['alta marca', 'altul'].includes(leftName);
            const rightOther = ['altul', 'alta-marca'].includes(String(right?.slug || ''))
                || ['alta marca', 'altul'].includes(rightName);

            if (leftOther !== rightOther) return leftOther ? 1 : -1;
            return leftName.localeCompare(rightName, 'ro');
        });
    }

    function brandOptions(brands) {
        const popular = brands
            .filter((brand) => !!brand?.is_popular)
            .map((brand) => catalogOption(brand, 'name', 'Populare'));
        const alphabetical = sortBrandsAlphabetically(brands)
            .map((brand) => catalogOption(brand, 'name', 'A-Z'));

        return [...popular, ...alphabetical];
    }

    async function fetchCatalog(url) {
        if (!catalogCache.has(url)) {
            const promise = fetch(url, {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            }).then((response) => {
                if (!response.ok) {
                    throw new Error(`Catalog request failed: ${response.status}`);
                }

                return response.json();
            }).catch((error) => {
                catalogCache.delete(url);
                throw error;
            });

            catalogCache.set(url, promise);
        }

        return catalogCache.get(url);
    }

    function comboboxRoot(el) {
        return window.iaCombobox?.get(el)?.root || el?.closest?.('[data-combobox]') || null;
    }

    function setComboboxOptions(el, options, selectedValue = '') {
        if (!el || !window.iaCombobox?.get(el)) return;

        window.iaCombobox.setOptions(el, options, selectedValue || el.value || '', { dispatch: false });
        window.iaCombobox.enable(el);
    }

    function setComboboxEnabled(el, enabled) {
        if (!el) return;

        if (window.iaCombobox?.get(el)) {
            enabled ? window.iaCombobox.enable(el) : window.iaCombobox.disable(el);
            return;
        }

        el.disabled = !enabled;
    }

    function modelsUrl(brandId) {
        return `${autoCatalogUrls.modelsByBrand}?brand_id=${encodeURIComponent(brandId)}`;
    }

    function localitiesUrl(countyId) {
        return `${localityBaseUrl}/${encodeURIComponent(countyId)}`;
    }

    function prefetchStaticCatalogs() {
        [
            autoCatalogUrls.brands,
            autoCatalogUrls.bodies,
            autoCatalogUrls.fuels,
            autoCatalogUrls.transmissions,
            autoCatalogUrls.counties,
        ].forEach((url) => {
            fetchCatalog(url).catch((error) => console.error(error));
        });
    }

    function prefetchModelsForBrand(brandId) {
        if (!brandId) return;

        fetchCatalog(modelsUrl(brandId)).catch((error) => console.error(error));
    }

    function loadOnFirstComboboxOpen(el, loader) {
        if (!el) return;

        let loadPromise = null;
        const load = (event = null) => {
            if (event?.target?.closest?.('[data-combobox-listbox]')) {
                return Promise.resolve();
            }

            if (!el || el.disabled) return Promise.resolve();
            if (!loadPromise) {
                loadPromise = Promise.resolve(loader()).catch((error) => {
                    console.error(error);
                }).finally(() => {
                    loadPromise = null;
                });
            }

            return loadPromise;
        };

        const root = comboboxRoot(el);
        root?.addEventListener('pointerdown', load, { capture: true });
        root?.addEventListener('focusin', load);
        root?.querySelector('[data-combobox-toggle]')?.addEventListener('click', load, { capture: true });
    }

    async function ensureBrandsLoaded() {
        if (brandsLoaded) return;

        const brands = await fetchCatalog(autoCatalogUrls.brands);
        setComboboxOptions(domElements.brand, brandOptions(brands), domElements.brand?.value || '');
        brandsLoaded = true;
    }

    async function ensureCountiesLoaded() {
        if (countiesLoaded) return;

        const counties = await fetchCatalog(autoCatalogUrls.counties);
        setComboboxOptions(
            domElements.county,
            counties.map((county) => catalogOption(county, 'name')),
            domElements.county?.value || ''
        );
        countiesLoaded = true;
    }

    async function renderModelsForBrand(brandId, selectedModelId = '', { resetFirst = true } = {}) {
        if (resetFirst) {
            resetSelect(domElements.model, 'Model');
        }

        if (!brandId) {
            if (!resetFirst) {
                resetSelect(domElements.model, 'Model');
            }
            modelsLoadedForBrand = null;
            return;
        }

        const models = await fetchCatalog(modelsUrl(brandId));
        const options = models.map((model) => catalogOption(model, 'name'));

        if (!options.length) {
            resetSelect(domElements.model, 'Model');
            modelsLoadedForBrand = String(brandId);
            return;
        }

        setComboboxOptions(domElements.model, options, selectedModelId || domElements.model?.value || '');
        modelsLoadedForBrand = String(brandId);

        if (selectedModelId && domElements.model?.value) {
            domElements.model.dispatchEvent(new Event('change'));
        }
    }

    async function ensureModelsLoadedForSelectedBrand() {
        const brandId = domElements.brand?.value || '';

        if (!brandId || modelsLoadedForBrand === String(brandId)) {
            return;
        }

        await renderModelsForBrand(brandId, domElements.model?.value || initialModelId || '', { resetFirst: false });
    }

    function setupLookupCatalog(el, url, labelKey = 'nume') {
        loadOnFirstComboboxOpen(el, async () => {
            const items = await fetchCatalog(url);
            setComboboxOptions(el, items.map((item) => catalogOption(item, labelKey)), el?.value || '');
        });
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

    function resetLocalities(enabled = false) {
        if (!domElements.locality) return;
        if (window.iaCombobox?.get(domElements.locality)) {
            window.iaCombobox.setOptions(domElements.locality, [], '');
            enabled ? window.iaCombobox.enable(domElements.locality) : window.iaCombobox.disable(domElements.locality);
            return;
        }
        domElements.locality.innerHTML = '<option value="">Oraș</option>';
        domElements.locality.disabled = !enabled;
    }

    function renderLocalities(localities, selectedId) {
        if (!domElements.locality) return;
        if (window.iaCombobox?.get(domElements.locality)) {
            window.iaCombobox.setOptions(domElements.locality, localities.map((locality) => ({
                value: locality.id,
                label: locality.name,
                name: locality.name,
                slug: locality.slug,
            })), selectedId || '');
            window.iaCombobox.enable(domElements.locality);
            return;
        }
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

    async function renderLocalitiesForCounty(countyId, selectedId = null) {
        if (!countyId) {
            resetLocalities();
            return;
        }

        try {
            const data = await fetchCatalog(localitiesUrl(countyId));
            renderLocalities(data, selectedId);
        } catch (error) {
            console.error(error);
            resetLocalities();
        }
    }

    async function ensureLocalitiesLoadedForSelectedCounty() {
        const countyId = domElements.county?.value || '';

        if (!countyId) {
            resetLocalities();
            return;
        }

        await renderLocalitiesForCounty(countyId, domElements.locality?.value || initialLocalityId || '');
    }

    function selectedOptionMeta(el) {
        const comboOption = window.iaCombobox?.selectedOption(el);
        if (comboOption) return comboOption;

        return el?.selectedOptions?.[0] || null;
    }

    function optionSlug(option) {
        return option?.slug || option?.dataset?.slug || '';
    }

    function escapeBreadcrumbHtml(value) {
        const element = document.createElement('span');
        element.textContent = String(value || '');

        return element.innerHTML;
    }

    function autoListingPath(...segments) {
        const cleanSegments = segments.filter(Boolean);
        const path = ['anunturi-auto-de-vanzare', ...cleanSegments].join('/');

        return `${baseUrl.replace(/\/+$/, '')}/${path}`;
    }

    function listingBreadcrumbItems(targetUrl = window.location.href) {
        const brandOption = selectedOptionMeta(domElements.brand);
        const modelOption = selectedOptionMeta(domElements.model);
        const countyOption = selectedOptionMeta(domElements.county);
        const localityOption = selectedOptionMeta(domElements.locality);
        const brandSlug = optionSlug(brandOption);
        const modelSlug = optionSlug(modelOption);
        const countySlug = optionSlug(countyOption);
        const citySlug = optionSlug(localityOption);
        const items = [
            { label: 'Acasă', url: `${baseUrl.replace(/\/+$/, '')}/` },
            { label: 'Autoturisme', url: autoListingPath() },
        ];

        if (brandSlug) {
            items.push({
                label: brandOption.label || brandOption.name,
                url: autoListingPath(brandSlug),
            });
        }

        if (brandSlug && modelSlug) {
            items.push({
                label: modelOption.label || modelOption.name,
                url: autoListingPath(brandSlug, modelSlug),
            });
        }

        if (countySlug) {
            items.push({
                label: countyOption.label || countyOption.name,
                url: autoListingPath(brandSlug || countySlug, brandSlug ? (modelSlug || null) : null, brandSlug ? countySlug : null),
            });
        }

        if (countySlug && citySlug) {
            items.push({
                label: localityOption.label || localityOption.name,
                url: autoListingPath(brandSlug || countySlug, brandSlug ? (modelSlug || null) : citySlug, brandSlug ? countySlug : null, brandSlug ? citySlug : null),
            });
        }

        const currentUrl = new URL(targetUrl, window.location.origin).toString();
        items[items.length - 1].url = currentUrl;

        return items.filter((item) => item.label && item.url);
    }

    function renderListingBreadcrumbs(targetUrl = window.location.href) {
        if (!domElements.breadcrumbs.length) return;

        const segmentBaseClass = 'inline-flex h-7 max-w-[11rem] items-center bg-[#EEF2F8] text-[#172033] transition hover:bg-[#E5EBF4] active:bg-[#DCE4EF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C81424]/20 dark:bg-[#1F2937] dark:text-gray-100 dark:hover:bg-[#273449] sm:max-w-[14rem]';
        const currentClass = 'inline-flex h-7 max-w-[12rem] items-center pl-2.5 text-[#172033] transition hover:text-[#C81424] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C81424]/20 dark:text-gray-100 sm:max-w-[18rem]';
        const items = listingBreadcrumbItems(targetUrl);
        const html = items.map((item, index) => {
            const isFirst = index === 0;
            const isCurrent = index === items.length - 1;
            const clipPath = isFirst
                ? 'polygon(0 0, calc(100% - 9px) 0, 100% 50%, calc(100% - 9px) 100%, 0 100%)'
                : 'polygon(0 0, calc(100% - 9px) 0, 100% 50%, calc(100% - 9px) 100%, 0 100%, 9px 50%)';
            const itemClass = isFirst ? '' : '-ml-[7px]';
            const label = escapeBreadcrumbHtml(item.label);
            const href = escapeBreadcrumbHtml(item.url);

            if (isCurrent) {
                return `<li class="${itemClass}"><a href="${href}" class="${currentClass}" aria-current="page"><span class="truncate">${label}</span></a></li>`;
            }

            const paddingClass = isFirst ? 'pl-2 pr-4' : 'pl-4 pr-4';
            const linkClass = `${paddingClass} ${segmentBaseClass}`;

            return `<li class="${itemClass}"><a href="${href}" class="${linkClass}" style="clip-path: ${clipPath};"><span class="truncate">${label}</span></a></li>`;
        }).join('');

        domElements.breadcrumbs.forEach((breadcrumb) => {
            breadcrumb.innerHTML = `<ol class="flex min-w-max items-center whitespace-nowrap text-[13px] font-semibold">${html}</ol>`;
            breadcrumb.scrollLeft = 0;
        });
    }

    function buildSearchUrl() {
        const brandOption = selectedOptionMeta(domElements.brand);
        const modelOption = selectedOptionMeta(domElements.model);
        const countyOption = selectedOptionMeta(domElements.county);
        const localityOption = selectedOptionMeta(domElements.locality);

        const brandSlug = optionSlug(brandOption);
        const modelSlug = optionSlug(modelOption);
        const countySlug = optionSlug(countyOption);
        const citySlug = optionSlug(localityOption);
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

    function listingAjaxUrl(page, sourceUrl = activeListUrl) {
        const url = new URL(sourceUrl || window.location.href, window.location.origin);
        url.searchParams.set('page', String(page));
        url.searchParams.set('ajax', '1');

        return url.toString();
    }

    function updateBrowserListingUrl(url, replace = false) {
        activeListUrl = url;

        if (url !== window.location.href) {
            window.history[replace ? 'replaceState' : 'pushState']({ listingUrl: url }, '', url);
        }
    }

    function applyListingFilters({ replace = false } = {}) {
        const targetUrl = buildSearchUrl();

        if (isMobileView()) {
            closeMobileFilters();
        }

        return window.loadServices(1, targetUrl).then((loaded) => {
            if (loaded === false) {
                window.location.href = targetUrl;
                return false;
            }

            if (loaded !== true) {
                return false;
            }

            updateBrowserListingUrl(targetUrl, replace);
            renderListingBreadcrumbs(targetUrl);
            window.checkResetVisibility();
            window.scrollTo({ top: 0, behavior: 'auto' });

            return true;
        });
    }

    function selectedOptionLabel(el) {
        if (!el || !el.value) return '';

        const option = selectedOptionMeta(el);
        if (!option || option.value === '') return '';

        return String(option.label || option.dataset?.label || option.textContent || '').trim();
    }

    function collectSavedSearchFilters() {
        return {
            seller_type: domElements.sellerType?.value || '',
            brand_id: domElements.brand?.value || '',
            brand: selectedOptionLabel(domElements.brand),
            model_id: domElements.model?.value || '',
            model: selectedOptionLabel(domElements.model),
            county_id: domElements.county?.value || '',
            county: selectedOptionLabel(domElements.county),
            locality_id: domElements.locality?.value || '',
            locality: selectedOptionLabel(domElements.locality),
            caroserie_id: domElements.body?.value || '',
            caroserie: selectedOptionLabel(domElements.body),
            combustibil_id: domElements.fuel?.value || '',
            combustibil: selectedOptionLabel(domElements.fuel),
            cutie_viteze_id: domElements.gear?.value || '',
            cutie_viteze: selectedOptionLabel(domElements.gear),
            price_min: domElements.priceMin?.value || '',
            price_max: domElements.priceMax?.value || '',
            km_min: domElements.kmMin?.value || '',
            km_max: domElements.kmMax?.value || '',
            year_min: domElements.yearMin?.value || '',
            year_max: domElements.yearMax?.value || '',
            sort: domElements.sort?.value || '',
        };
    }

    function savedSearchName(filters) {
        const parts = [filters.brand, filters.model, filters.locality, filters.county].filter(Boolean);
        return parts.length ? parts.join(' ') : 'Căutare auto';
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

    window.loadServices = function(page, sourceUrl = null) {
        const numericPage = Number(page) || 1;
        const isNewFilter = numericPage === 1;

        if (isLoading && !isNewFilter) return Promise.resolve(false);
        if (!hasMore && !isNewFilter) return Promise.resolve(false);

        if (isNewFilter && activeListingController) {
            activeListingController.abort();
        }

        const requestId = ++listingRequestId;
        const controller = new AbortController();
        activeListingController = controller;

        if (isNewFilter) {
            hasMore = true;
            if (domElements.container) domElements.container.style.opacity = '0.5';
            if (domElements.trigger) domElements.trigger.dataset.hasMore = 'true';
            window.checkResetVisibility();
        } else {
            if (domElements.loader) domElements.loader.classList.remove('hidden');
        }

        isLoading = true;
        const requestUrl = listingAjaxUrl(numericPage, sourceUrl || activeListUrl);

        return fetch(requestUrl, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
        .then(res => res.json())
        .then(data => {
            if (requestId !== listingRequestId) {
                return null;
            }

            if (isNewFilter) {
                if (domElements.container) {
                    domElements.container.innerHTML = data.html;
                    domElements.container.style.opacity = '1';
                }
            } else {
                if (domElements.container) domElements.container.insertAdjacentHTML('beforeend', data.html);
            }

            window.iaAutoFavorites?.refresh(domElements.container || document);

            hasMore = !!data.hasMore;
            if (domElements.trigger) domElements.trigger.dataset.hasMore = hasMore ? 'true' : 'false';
            currentPage = numericPage + 1;
            if (domElements.trigger) domElements.trigger.dataset.nextPage = String(currentPage);
            if (domElements.totalSummary && typeof data.total !== 'undefined') {
                domElements.totalSummary.textContent = `${Number(data.total).toLocaleString('ro-RO')} anunțuri găsite`;
            }
            updateListingPagination(data.pagination);

            if (hasMore && domElements.trigger) {
                observer.unobserve(domElements.trigger);
                observer.observe(domElements.trigger);
            }

            return true;
        })
        .catch(err => {
            if (err.name === 'AbortError') {
                return null;
            }

            console.error(err);
            return false;
        })
        .finally(() => {
            if (requestId === listingRequestId) {
                isLoading = false;
                activeListingController = null;
                if (domElements.loader) domElements.loader.classList.add('hidden');
            }
        });
    };

    document.addEventListener('DOMContentLoaded', () => {
        window.iaCombobox?.init(document);
        document.querySelectorAll('select.autovit-select').forEach(enhanceSelect);

        window.checkResetVisibility();

        window.addEventListener('iaauto:saved-search-feedback', (event) => {
            setSaveSearchButtonActive(Boolean(event.detail?.active));
        });

        domElements.saveSearch?.addEventListener('click', () => {
            const filters = collectSavedSearchFilters();
            setSaveSearchButtonActive(true);

            if (!window.iaAutoSavedSearches?.save) {
                setSaveSearchButtonActive(false);
                return;
            }

            window.iaAutoSavedSearches.save({
                url: buildSearchUrl(),
                name: savedSearchName(filters),
                filters,
            });
        });

        const filterOverlay = document.getElementById('filters-overlay');
        const filterPanel = document.getElementById('filters-panel');
        const openFilters = document.getElementById('open-filters');
        const closeFilters = document.getElementById('close-filters');
        const sellerButtons = document.querySelectorAll('[data-seller-filter]');
        const filterPortalAnchor = filterOverlay?.parentNode && filterPanel
            ? document.createComment('filters-panel-portal')
            : null;

        if (filterPortalAnchor && filterOverlay?.parentNode) {
            filterOverlay.parentNode.insertBefore(filterPortalAnchor, filterOverlay);
        }

        const nav = document.getElementById('main-nav');
        let cachedNavHeight = nav ? Math.ceil(nav.offsetHeight) : 56;
        let currentMobileFiltersTop = cachedNavHeight;

        const applyMobileFiltersOffset = () => {
            document.documentElement.style.setProperty('--mobile-filters-top', `${Math.max(0, currentMobileFiltersTop)}px`);
        };

        const measureMobileFiltersTop = () => {
            if (!nav) {
                return cachedNavHeight;
            }

            const rect = nav.getBoundingClientRect();
            const viewportHeight = window.visualViewport?.height || window.innerHeight || document.documentElement.clientHeight || cachedNavHeight;

            return Math.max(0, Math.min(Math.ceil(rect.bottom), Math.ceil(viewportHeight)));
        };

        const setMobileFiltersOffset = (height = null) => {
            if (height !== null) {
                cachedNavHeight = height;
            } else if (nav) {
                cachedNavHeight = Math.ceil(nav.offsetHeight) || cachedNavHeight;
            }

            currentMobileFiltersTop = measureMobileFiltersTop();
            applyMobileFiltersOffset();
        };

        const filtersArePortaled = () => (
            filterOverlay?.parentNode === document.body || filterPanel?.parentNode === document.body
        );

        const portalMobileFilters = () => {
            if (!filterOverlay || !filterPanel || !mobileQuery.matches || filtersArePortaled()) {
                return;
            }

            document.body.appendChild(filterOverlay);
            document.body.appendChild(filterPanel);
        };

        const restoreMobileFilters = () => {
            if (!filterOverlay || !filterPanel || !filterPortalAnchor?.parentNode || !filtersArePortaled()) {
                return;
            }

            const originalParent = filterPortalAnchor.parentNode;
            originalParent.insertBefore(filterOverlay, filterPortalAnchor.nextSibling);
            originalParent.insertBefore(filterPanel, filterOverlay.nextSibling);
        };

        closeMobileFilters = () => {
            filterOverlay?.classList.add('hidden');
            filterPanel?.classList.add('hidden');
            document.body.style.overflow = '';
            closeCustomSelects();
            restoreMobileFilters();
            setMobileFiltersOffset();
        };

        const openMobileFilters = () => {
            portalMobileFilters();
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
            if (Number.isFinite(Number(event.detail?.visibleHeight))) {
                cachedNavHeight = Math.ceil(Number(event.detail.visibleHeight));
            }

            setMobileFiltersOffset();
        });

        window.addEventListener('resize', () => setMobileFiltersOffset(), { passive: true });
        window.visualViewport?.addEventListener('resize', () => setMobileFiltersOffset(), { passive: true });
        window.visualViewport?.addEventListener('scroll', () => setMobileFiltersOffset(), { passive: true });
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
                applyListingFilters();
            });
        }

        if (domElements.county) {
            domElements.county.addEventListener('change', () => {
                const countyId = domElements.county.value;

                resetLocalities(!!countyId);
                if (countyId) {
                    renderLocalitiesForCounty(countyId);
                }
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
                applyListingFilters();
            });
        }

        const sellerActiveClasses = ['border-b-[#C81424]', 'bg-white', 'text-[#C81424]', 'hover:bg-white', 'hover:text-[#C81424]', 'dark:bg-[#2a1013]', 'dark:text-red-300', 'dark:hover:bg-[#3a171c]', 'dark:hover:text-red-200'];
        const sellerInactiveClasses = ['border-b-transparent', 'bg-white', 'text-[#687080]', 'hover:bg-[#F7F8FA]', 'hover:text-[#30323A]', 'dark:bg-transparent', 'dark:text-gray-400', 'dark:hover:bg-[#2a2f36]', 'dark:hover:text-white'];
        const sellerLegacyClasses = ['border-[#C81424]', 'border-transparent', 'bg-[#30323A]', 'text-white', 'hover:bg-[#30323A]', 'bg-[#F7F8FA]', 'hover:bg-[#EEF1F4]', 'bg-[#2F3137]', 'shadow-[0_8px_18px_rgba(17,24,39,0.18)]', 'hover:bg-[#26282D]', 'bg-[#BA1C23]', 'shadow-[0_6px_16px_rgba(186,28,35,0.24)]', 'hover:bg-[#A8171F]', 'bg-[#fff0f2]', 'shadow-sm', 'dark:bg-[#3a171c]'];
        const updateSellerButtons = (selectedSellerType = 'all') => {
            const normalizedSellerType = ['all', 'individual', 'dealer'].includes(selectedSellerType)
                ? selectedSellerType
                : 'all';

            sellerButtons.forEach((button) => {
                const isSelected = button.dataset.sellerFilter === normalizedSellerType;
                button.setAttribute('aria-pressed', isSelected ? 'true' : 'false');
                button.classList.remove(...(isSelected ? sellerInactiveClasses : sellerActiveClasses), ...sellerLegacyClasses);
                button.classList.add(...(isSelected ? sellerActiveClasses : sellerInactiveClasses));
            });
        };

        if (sellerButtons.length) {
            sellerButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    const sellerType = button.dataset.sellerFilter || 'all';

                    if (domElements.sellerType) {
                        domElements.sellerType.value = sellerType;
                    }

                    updateSellerButtons(sellerType);
                    window.checkResetVisibility();
                });
            });

            updateSellerButtons(domElements.sellerType?.value || 'all');
        }

        loadOnFirstComboboxOpen(domElements.brand, ensureBrandsLoaded);
        loadOnFirstComboboxOpen(domElements.model, ensureModelsLoadedForSelectedBrand);
        loadOnFirstComboboxOpen(domElements.county, ensureCountiesLoaded);
        loadOnFirstComboboxOpen(domElements.locality, ensureLocalitiesLoadedForSelectedCounty);
        setupLookupCatalog(domElements.body, autoCatalogUrls.bodies);
        setupLookupCatalog(domElements.fuel, autoCatalogUrls.fuels);
        setupLookupCatalog(domElements.gear, autoCatalogUrls.transmissions);

        prefetchStaticCatalogs();
        prefetchModelsForBrand(domElements.brand?.value || '');

        if (domElements.brand) {
            domElements.brand.addEventListener('change', function () {
                const brandId = this.value;
                modelsLoadedForBrand = null;

                if (!brandId) {
                    resetSelect(domElements.model, 'Model');
                    window.checkResetVisibility();
                    return;
                }

                resetSelect(domElements.model, 'Model');
                setComboboxEnabled(domElements.model, true);
                prefetchModelsForBrand(brandId);
                window.checkResetVisibility();
            });
        }

        if (domElements.model) {
            domElements.model.addEventListener('change', function () {
                window.checkResetVisibility();
            });
        }

        if (domElements.county?.value) {
            setComboboxEnabled(domElements.locality, true);
            renderLocalitiesForCounty(domElements.county.value, initialLocalityId);
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

        const numericFilterInputs = [
            domElements.priceMin,
            domElements.priceMax,
            domElements.kmMin,
            domElements.kmMax,
            domElements.yearMin,
            domElements.yearMax,
        ];

        initClearableFilterInputs(numericFilterInputs);

        numericFilterInputs.forEach(el => {
            if (el) {
                el.addEventListener('input', () => {
                    window.checkResetVisibility();
                });
            }
        });

        if (domElements.trigger) observer.observe(domElements.trigger);
    });

    window.addEventListener('popstate', () => {
        window.location.reload();
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
    }, { rootMargin: '0px 0px 800px 0px' });

    window.toggleHeart = function(btn, serviceId) {
        window.iaAutoFavorites?.toggle(btn, serviceId);
    }
</script>

<style>
    :root {
        --mobile-filters-top: 56px;
    }

    .listing-action-button--active {
        border-color: #C81424 !important;
        background: #C81424 !important;
        color: #ffffff !important;
        box-shadow: 0 8px 18px rgba(200, 20, 36, 0.18);
    }

    @media (hover: hover) and (pointer: fine) {
        .listing-action-button--interactive:hover {
            border-color: #C81424;
            background: #fff4f5;
            color: #C81424;
        }

        .dark .listing-action-button--interactive:hover {
            border-color: rgba(127, 29, 29, 0.7);
            background: #2a1013;
            color: #fecaca;
        }
    }

    .listing-sort-combobox .ia-combobox__control,
    .listing-sort-combobox .ia-combobox__input {
        cursor: default;
    }

    .listing-sort-combobox .ia-combobox__input {
        caret-color: transparent;
    }

    .listing-sort-combobox .ia-combobox__clear {
        display: none !important;
    }

    .listing-sort-combobox.ia-combobox.has-value .ia-combobox__input {
        padding-right: 2.75rem !important;
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
        -webkit-backdrop-filter: blur(6px);
        backdrop-filter: blur(6px);
    }

        .listing-actions-row {
            display: grid;
            grid-template-columns: minmax(0, 0.58fr) minmax(0, 0.68fr) minmax(0, 0.74fr) minmax(0, 1.5fr);
            align-items: stretch;
            gap: 0.5rem;
        }

    .listing-action-button {
        height: auto;
        min-height: 42px;
        border-radius: 8px;
        line-height: 1.1;
        white-space: normal;
    }

    .listing-action-label {
        min-width: 0;
        text-align: center;
    }

    .listing-action-button--save-search {
        padding-right: 0.4rem;
        padding-left: 0.4rem;
    }

    .listing-action-button--save-search .listing-action-label {
        color: #6b7280;
        display: flex;
        flex-direction: column;
        align-items: center;
        font-weight: 700;
        line-height: 1.05;
    }

    .dark .listing-action-button--save-search .listing-action-label {
        color: #94a3b8;
    }

    .listing-sort-combobox .ia-combobox__control {
        height: 42px;
        border-radius: 8px;
    }

    .listing-sort-combobox .ia-combobox__floating {
        top: 0.35rem;
        left: 0.55rem;
        font-size: 0.54rem;
    }

    .listing-sort-combobox .ia-combobox__input,
    .listing-sort-combobox.ia-combobox.has-value .ia-combobox__input {
        padding: 0.72rem 1.3rem 0 0.55rem !important;
        font-size: 0.76rem;
        line-height: 1.05;
    }

    .listing-sort-combobox .ia-combobox__toggle {
        top: 0.55rem;
        right: 0;
        transform: none;
    }

    #listing-actions-bar .truncate,
    #listing-actions-bar .custom-select-label {
        overflow: visible;
        text-overflow: clip;
        white-space: normal;
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
            top: 0;
            bottom: 0;
            height: auto;
        }

        #filters-panel .filters-panel-sheet {
            height: auto;
            max-height: 100dvh;
            border-bottom-left-radius: 0.75rem;
            border-bottom-right-radius: 0.75rem;
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

        .listing-sort-combobox .ia-combobox__input,
        .listing-sort-combobox.ia-combobox.has-value .ia-combobox__input {
            padding-right: 1.15rem !important;
            font-size: 0.72rem;
        }

        .listing-sort-combobox .ia-combobox__toggle {
            right: -0.05rem;
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

    #filters-panel input.listing-filter[type="number"] {
        -moz-appearance: textfield;
        appearance: textfield;
    }

    #filters-panel input.listing-filter[type="number"]::-webkit-inner-spin-button,
    #filters-panel input.listing-filter[type="number"]::-webkit-outer-spin-button {
        -webkit-appearance: none;
        margin: 0;
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

        #filters-panel .listing-filter.ia-combobox .ia-combobox__input,
        #filters-panel .listing-filter.ia-combobox .ia-combobox__input::placeholder,
        #filters-panel .listing-filter.ia-combobox .ia-combobox__floating,
        #filters-panel .listing-filter.ia-combobox .ia-combobox__clear,
        #filters-panel .listing-filter.ia-combobox .ia-combobox__toggle {
            color: #ffffff !important;
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

</style>
@endsection
