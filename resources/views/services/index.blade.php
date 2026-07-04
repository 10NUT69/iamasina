@extends('layouts.app')

@section('title', 'Anunțuri Auto Second Hand & Noi - Mașini de Vânzare')
@section('meta_title', 'Anunțuri Auto - Mașini de Vânzare Second Hand și Noi | iaAuto.ro')
@section('meta_description', 'Cauți o mașină? Pe iaAuto.ro găsești mașini de vânzare second hand și noi de la proprietari și parcuri auto din România. Adaugă anunțul tău complet gratuit!')
@section('meta_image', asset('images/social-share.webp'))
@section('head')
    <link rel="preload" as="image" href="{{ asset('images/homepage-hero-car.webp') }}" fetchpriority="high" media="(prefers-color-scheme: light), (prefers-color-scheme: no-preference)">
    <link rel="preload" as="image" href="{{ asset('images/homepage-hero-car-dark.webp') }}" fetchpriority="high" media="(prefers-color-scheme: dark)">
@endsection

@section('hero')
{{-- HERO SECTION --}}
<div class="w-full bg-[linear-gradient(180deg,#fff7f8_0%,#ffffff_72%)] dark:bg-[linear-gradient(180deg,#171112_0%,#121212_76%)] pt-14 md:pt-20 lg:pt-20 pb-3 md:pb-4 lg:pb-2">
    <div class="homepage-hero-layout max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">

        <div class="homepage-hero-visual relative isolate -mx-4 flex min-h-[210px] items-center overflow-hidden rounded-none bg-white/70 px-4 py-5 sm:-mx-6 sm:min-h-[244px] sm:px-6 sm:py-7 md:min-h-[264px] md:py-8 lg:mx-0 lg:min-h-[216px] lg:rounded-3xl lg:px-10 lg:py-5 lg:shadow-[0_22px_70px_rgba(200,20,36,0.10)] dark:bg-[#171112] dark:lg:shadow-black/40 xl:min-h-[225px]">
            <picture class="homepage-hero-media absolute inset-0 -z-20 block">
                <source media="(prefers-color-scheme: dark)" srcset="{{ asset('images/homepage-hero-car-dark.webp') }}">
                <img src="{{ asset('images/homepage-hero-car.webp') }}"
                     alt="Mașină de vânzare pe iaAuto.ro"
                     class="homepage-hero-image h-full w-full object-cover"
                     fetchpriority="high"
                     decoding="async">
            </picture>
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
                $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
                $currentModelId = isset($currentModel) ? $currentModel->id : null;
                $selectedBrandId = request('brand_id', $currentBrandId);
                $selectedModelId = request('model_id', $currentModelId);
            @endphp

            <div class="homepage-quick-filters lg:hidden p-3">
                <div class="grid grid-cols-2 gap-2">
                    <div class="min-w-0">
                        <x-combobox
                            id="homepage-quick-brand-filter"
                            name="homepage_quick_brand_id"
                            label="Marca"
                            placeholder="Marca"
                            :options="$brands"
                            option-label="name"
                            :selected="$selectedBrandId"
                            class="homepage-quick-select"
                        />
                    </div>

                    <div class="min-w-0">
                        <x-combobox
                            id="homepage-quick-model-filter"
                            name="homepage_quick_model_id"
                            label="Model"
                            placeholder="Model"
                            :options="$currentModel ? collect([$currentModel]) : collect()"
                            :selected="$selectedModelId"
                            :disabled="!$selectedBrandId"
                            class="homepage-quick-select"
                        />
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
                        De unde vrei să cumperi?
                    </span>
                </div>

                {{-- Toggle Tabs --}}
                <div class="seller-tabs grid min-w-0 grid-cols-3 overflow-hidden bg-white dark:bg-[#151515] border-t border-[#E6E8EC] dark:border-white/10 sm:border-t-0 sm:border-l w-full sm:w-[430px] rounded-t-xl sm:rounded-tl-lg sm:rounded-tr-xl">
                    <button type="button" data-seller="all"
                    class="seller-tab min-w-0 border-b-2 border-b-[#C81424] bg-white px-2 py-3 text-[11px] font-bold text-[#C81424] hover:bg-white hover:text-[#C81424] dark:bg-[#2a1013] dark:text-red-300 dark:hover:bg-[#3a171c] dark:hover:text-red-200 sm:px-4 sm:text-xs">
                        Toți
                    </button>
                    <button type="button" data-seller="individual"
                        class="seller-tab min-w-0 border-b-2 border-b-transparent bg-white px-2 py-3 text-[11px] font-bold text-[#687080] hover:bg-[#F7F8FA] hover:text-[#30323A] dark:bg-transparent dark:text-gray-400 dark:hover:bg-[#2a2f36] dark:hover:text-white sm:px-4 sm:text-xs">
                        Proprietari
                    </button>
                    <button type="button" data-seller="dealer"
                        class="seller-tab min-w-0 border-b-2 border-b-transparent bg-white px-2 py-3 text-[11px] font-bold text-[#687080] hover:bg-[#F7F8FA] hover:text-[#30323A] dark:bg-transparent dark:text-gray-400 dark:hover:bg-[#2a2f36] dark:hover:text-white sm:px-4 sm:text-xs">
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
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">

                        {{-- RÂNDUL 1 --}}
                        <div class="col-span-2 lg:col-span-1">
                            <x-combobox
                                id="brand-filter"
                                name="brand_id"
                                label="Marca"
                                placeholder="Marca"
                                :options="$brands"
                                option-label="name"
                                :selected="$selectedBrandId"
                            />
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <x-combobox
                                id="model-filter"
                                name="model_id"
                                label="Model"
                                placeholder="Model"
                                :options="$currentModel ? collect([$currentModel]) : collect()"
                                :selected="$selectedModelId"
                                :disabled="!$selectedBrandId"
                            />
                        </div>

                        <div class="col-span-1 lg:col-span-1">
                            <x-combobox
                                id="body-filter"
                                name="caroserie_id"
                                label="Tip caroserie"
                                placeholder="Tip caroserie"
                                :options="$bodies"
                                option-label="nume"
                                :selected="request('caroserie_id')"
                            />
                        </div>

                        {{-- RÂNDUL 2 --}}
                        <div class="col-span-1">
                            <x-combobox
                                id="fuel-filter"
                                name="combustibil_id"
                                label="Combustibil"
                                placeholder="Combustibil"
                                :options="$fuels"
                                option-label="nume"
                                :selected="request('combustibil_id')"
                            />
                        </div>

                        <div class="col-span-1">
                            <x-combobox
                                id="gearbox-filter"
                                name="cutie_viteze_id"
                                label="Transmisie"
                                placeholder="Transmisie"
                                :options="$transmissions"
                                option-label="nume"
                                :selected="request('cutie_viteze_id')"
                            />
                        </div>

{{-- BUTOANE ACȚIUNE --}}
                        <div class="col-span-2 lg:hidden mt-1 flex items-center justify-between pt-4 border-t border-gray-100 dark:border-[#333]">

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

                            <button type="submit" class="h-[42px] px-8 bg-[#C81424] hover:bg-[#94111B] text-white font-bold text-sm rounded-lg shadow-md shadow-red-700/20 transition-all flex items-center gap-2 uppercase tracking-wide transform active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Afișează rezultatele
                            </button>
                        </div>

                        <a href="{{ route('cars.index') }}"
                           class="homepage-detail-link hidden lg:inline-flex h-[42px] self-end items-center justify-center gap-2 rounded-lg border border-slate-300 bg-slate-50 px-4 text-[0.9rem] font-black whitespace-nowrap text-slate-700 shadow-sm transition hover:border-slate-400 hover:bg-slate-100 hover:text-slate-950 active:scale-[0.98] dark:border-white/10 dark:bg-[#201d1e] dark:text-gray-100 dark:hover:bg-[#2a2728]">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                <path d="M4 7h10" />
                                <path d="M18 7h2" />
                                <path d="M16 5v4" />
                                <path d="M4 17h2" />
                                <path d="M10 17h10" />
                                <path d="M8 15v4" />
                            </svg>
                            <span>Căutare detaliată</span>
                        </a>

                        {{-- Buton Submit --}}
                        <div class="hidden lg:block lg:col-span-2 lg:self-end">
                            <button type="submit" class="h-[42px] w-full px-8 bg-[#C81424] hover:bg-[#94111B] text-white font-bold text-sm rounded-lg shadow-md shadow-red-700/20 transition-all flex items-center justify-center gap-2 uppercase tracking-wide transform active:scale-[0.98]">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Caută
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

<div class="w-full -mt-3 md:-mt-4">
    <div class="flex items-center justify-between gap-3 mb-2 pb-1 border-b border-gray-100 dark:border-[#2C2C2C] md:mb-3">
        <div class="flex min-w-0 items-center gap-3">
             <div class="w-1.5 h-8 bg-[#C81424] rounded-full shadow-sm"></div>
            <h2 class="text-xl font-bold text-gray-900 dark:text-white sm:text-2xl">Anunțuri recente</h2>
        </div>

        <a href="{{ url('/anunturi-auto-de-vanzare') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-lg border border-[#C81424] bg-white px-4 py-2.5 text-xs font-black text-[#C81424] shadow-sm transition hover:bg-[#C81424] hover:text-white active:scale-[0.98] dark:bg-transparent dark:hover:bg-[#C81424] sm:px-6 sm:py-3 sm:text-sm"
           aria-label="Toate anunțurile auto">
            Toate anunțurile auto
            <span aria-hidden="true">&rarr;</span>
        </a>
    </div>

    {{-- GRID CARDURI VERTICALE --}}
    <div id="services-container" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 pb-6 relative z-0">
        @include('services.partials.service_cards_home', [
            'services' => $services,
            'serviceCardVariant' => 'index',
            'serviceCardTrackItem' => false,
        ])
    </div>

    @if($services->isNotEmpty())
        <div class="flex justify-center pb-8">
            <a href="{{ url('/anunturi-auto-de-vanzare') }}"
               class="inline-flex w-full max-w-xs items-center justify-center gap-2 rounded-lg border border-[#C81424] bg-white px-6 py-3 text-sm font-black text-[#C81424] shadow-sm transition hover:bg-[#C81424] hover:text-white active:scale-[0.98] dark:bg-transparent dark:hover:bg-[#C81424] sm:w-auto sm:max-w-none"
               aria-label="Toate anunțurile auto">
                Toate anunțurile auto
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    @endif

    <section class="pb-12">
        <div class="rounded-2xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#333] dark:bg-[#1E1E1E] sm:p-8 lg:p-10">
            <p class="mb-4 text-xs font-black uppercase tracking-wide text-[#C81424]">
                ANUNȚURI AUTO ÎN ROMÂNIA
            </p>

            <h2 class="max-w-none text-2xl font-black leading-tight text-gray-900 dark:text-white sm:text-3xl">
                iaAuto.ro - Platforma ta de anunțuri auto și mașini de vânzare
            </h2>

            <div class="mt-5 max-w-none space-y-4 text-justify text-sm leading-6 text-gray-700 dark:text-gray-300 sm:text-base sm:leading-7">
                <p>
                    Ai intrat pe iaAuto.ro, locul unde căutarea unei mașini devine simplă și rapidă. Indiferent dacă ești în căutarea unei mașini second hand accesibile sau vrei să achiziționezi un autoturism nou, platforma noastră aduce împreună cumpărători, proprietari particulari și parcuri auto din întreaga Românie.
                </p>
                <p>
                    Pentru cei care doresc să își vândă autoturismul, procesul este simplu. Poți să adaugi un anunț gratuit în doar câteva minute, oferind mai multă vizibilitate mașinii tale în fața potențialilor cumpărători. De la mașini de oraș economice și SUV-uri spațioase, până la utilitare sau mașini premium, iaAuto.ro este destinația potrivită pentru anunțuri auto din mai multe categorii.
                </p>
            </div>

            <div class="mt-7 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-3">
                <div class="flex gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#181818]">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-red-100 bg-red-50 text-[#C81424] dark:border-red-900/40 dark:bg-[#2a1013]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 14l2 2 4-4" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M7 7h10M7 11h5M5 3h14a2 2 0 012 2v14l-4-3H5a2 2 0 01-2-2V5a2 2 0 012-2z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Anunțuri gratuite</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Adaugi mașina de vânzare fără taxe de publicare.</p>
                    </div>
                </div>

                <div class="flex gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#181818]">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-red-100 bg-red-50 text-[#C81424] dark:border-red-900/40 dark:bg-[#2a1013]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.2-5.2" />
                            <circle cx="10.5" cy="10.5" r="6.5" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Căutare rapidă</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Filtrezi după marcă, model, preț, județ sau oraș.</p>
                    </div>
                </div>

                <div class="flex gap-4 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#181818] sm:col-span-2 lg:col-span-1">
                    <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-full border border-red-100 bg-red-50 text-[#C81424] dark:border-red-900/40 dark:bg-[#2a1013]">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 10h.01M12 10h.01M16 10h.01" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 12c0 4-4 7-9 7a11 11 0 01-4-.7L3 20l1.7-4A6.3 6.3 0 013 12c0-4 4-7 9-7s9 3 9 7z" />
                        </svg>
                    </span>
                    <div>
                        <h3 class="text-base font-black text-gray-900 dark:text-white">Contact direct</h3>
                        <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">Discuți direct cu vânzătorul, particular sau parc auto.</p>
                    </div>
                </div>
            </div>

            <p class="mt-7 max-w-none text-justify text-sm leading-6 text-gray-700 dark:text-gray-300 sm:text-base sm:leading-7">
                Navighează printre ofertele disponibile, compară prețurile și găsește mașina ideală pentru nevoile și bugetul tău. Pe iaAuto.ro, piața auto din România este mai accesibilă ca niciodată.
            </p>
        </div>
    </section>
</div>

<script>
    const baseUrl = "{{ url('/') }}";
    const autoCatalogUrls = {
        brands: "{{ route('ajax.brands') }}",
        modelsByBrand: "{{ route('ajax.models.by.brand') }}",
        bodies: "{{ route('ajax.bodies') }}",
        fuels: "{{ route('ajax.fuels') }}",
        transmissions: "{{ route('ajax.transmissions') }}",
    };
    const initialModelId = @json(optional($currentModel)->id);

    const domElements = {
        brand: document.getElementById('brand-filter'),
        model: document.getElementById('model-filter'),
        body: document.getElementById('body-filter'),
        fuel: document.getElementById('fuel-filter'),
        gear: document.getElementById('gearbox-filter'),
        // Am eliminat radius
        resetBtn: document.getElementById('reset-btn'),
        vehicleType: document.getElementById('vehicle-type'),
        sellerType: document.getElementById('seller-type'),
        quickBrand: document.getElementById('homepage-quick-brand-filter'),
        quickModel: document.getElementById('homepage-quick-model-filter'),
        quickSubmit: document.getElementById('homepage-quick-submit'),
        quickToggle: document.getElementById('homepage-more-filters-toggle'),
        quickToggleLabel: document.querySelector('.homepage-more-filters-label'),
        homepageFilterPanel: document.getElementById('homepage-filter-panel'),
    };

    function resetSelect(el, placeholder) {
        if (!el) return;
        const combo = window.iaCombobox?.get(el);
        if (combo) {
            window.iaCombobox.setOptions(el, [], '');
            window.iaCombobox.disable(el);
            return;
        }
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled = true;
        el.classList.add('bg-gray-50', 'text-gray-400', 'cursor-not-allowed', 'dark:bg-[#1a1a1a]', 'dark:text-gray-500');
        el.classList.remove('bg-white', 'text-gray-900', 'dark:bg-[#2d2d2d]', 'dark:text-gray-100');
        el.value = "";
        syncCustomSelect(el);
    }

    function enableSelect(el) {
        if (!el) return;
        if (window.iaCombobox?.get(el)) {
            window.iaCombobox.enable(el);
            return;
        }
        el.disabled = false;
        el.classList.remove('bg-gray-50', 'text-gray-400', 'cursor-not-allowed', 'dark:bg-[#1a1a1a]', 'dark:text-gray-500');
        el.classList.add('bg-white', 'text-gray-900', 'dark:bg-[#2d2d2d]', 'dark:text-gray-100');
        syncCustomSelect(el);
    }

    const catalogCache = new Map();
    let brandsLoaded = false;
    let modelsLoadedForBrand = null;

    function brandFields() {
        return [domElements.brand, domElements.quickBrand].filter(Boolean);
    }

    function modelFields() {
        return [domElements.model, domElements.quickModel].filter(Boolean);
    }

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
        syncCustomSelect(el);
    }

    function setComboboxEnabled(el, enabled) {
        if (!el) return;

        if (window.iaCombobox?.get(el)) {
            enabled ? window.iaCombobox.enable(el) : window.iaCombobox.disable(el);
            return;
        }

        el.disabled = !enabled;
        syncCustomSelect(el);
    }

    function setComboboxValue(el, value, { dispatch = false } = {}) {
        if (!el) return;

        if (window.iaCombobox?.get(el)) {
            window.iaCombobox.setValue(el, value || '', { dispatch });
        } else {
            el.value = value || '';
            if (dispatch) {
                el.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }

        syncCustomSelect(el);
    }

    function modelsUrl(brandId) {
        return `${autoCatalogUrls.modelsByBrand}?brand_id=${encodeURIComponent(brandId)}`;
    }

    function prefetchStaticCatalogs() {
        [
            autoCatalogUrls.brands,
            autoCatalogUrls.bodies,
            autoCatalogUrls.fuels,
            autoCatalogUrls.transmissions,
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
        const options = brandOptions(brands);
        brandFields().forEach((el) => setComboboxOptions(el, options, el.value || ''));
        brandsLoaded = true;
    }

    function resetModelFields(enabled = false) {
        modelFields().forEach((el) => {
            resetSelect(el, el === domElements.quickModel ? 'Toate modelele' : 'Alege model');
            if (enabled) {
                setComboboxEnabled(el, true);
            }
        });
    }

    async function renderModelsForBrand(brandId, selectedModelId = '', { resetFirst = true } = {}) {
        if (resetFirst) {
            resetModelFields();
        }

        if (!brandId) {
            if (!resetFirst) resetModelFields();
            modelsLoadedForBrand = null;
            return;
        }

        const models = await fetchCatalog(modelsUrl(brandId));
        const options = models.map((model) => catalogOption(model, 'name'));

        if (!options.length) {
            resetModelFields();
            modelsLoadedForBrand = String(brandId);
            return;
        }

        modelFields().forEach((el) => {
            const selectedValue = selectedModelId || el.value || '';
            setComboboxOptions(el, options, selectedValue);
        });
        modelsLoadedForBrand = String(brandId);
    }

    async function ensureModelsLoadedForSelectedBrand() {
        const brandId = domElements.brand?.value || domElements.quickBrand?.value || '';

        if (!brandId || modelsLoadedForBrand === String(brandId)) {
            return;
        }

        await renderModelsForBrand(brandId, domElements.model?.value || domElements.quickModel?.value || initialModelId || '', { resetFirst: false });
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

    function syncHomepageQuickFiltersFromMain() {
        setComboboxValue(domElements.quickBrand, domElements.brand?.value || '', { dispatch: false });
        setComboboxValue(domElements.quickModel, domElements.model?.value || '', { dispatch: false });
    }

    function applyHomepageQuickFiltersToMain() {
        setComboboxValue(domElements.brand, domElements.quickBrand?.value || '', { dispatch: false });
        setComboboxValue(domElements.model, domElements.quickModel?.value || '', { dispatch: false });

        window.checkResetVisibility();
    }

    window.checkResetVisibility = function() {
        const btn = domElements.resetBtn;
        if (!btn) return;
        const filters = [domElements.brand, domElements.model, domElements.body, domElements.fuel, domElements.gear];
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
        brandFields().forEach((el) => setComboboxValue(el, '', { dispatch: false }));
        resetModelFields();
        ['body','fuel','gear'].forEach(k => {
            if (!domElements[k]) return;
            if (window.iaCombobox?.get(domElements[k])) {
                window.iaCombobox.setValue(domElements[k], '');
            } else {
                domElements[k].value = '';
            }
        });
        syncAllCustomSelects();
        window.checkResetVisibility();
    };

    function selectedOptionMeta(el) {
        const comboOption = window.iaCombobox?.selectedOption(el);
        if (comboOption) return comboOption;

        return el?.selectedOptions?.[0] || null;
    }

    function optionSlug(option) {
        return option?.slug || option?.dataset?.slug || '';
    }

    function buildSearchUrl() {
        const brandOption = selectedOptionMeta(domElements.brand);
        const modelOption = selectedOptionMeta(domElements.model);
        const brandSlug = optionSlug(brandOption);
        const modelSlug = optionSlug(modelOption);

        let path = '/anunturi-auto-de-vanzare';
        if (brandSlug) path += `/${brandSlug}`;
        if (brandSlug && modelSlug) path += `/${modelSlug}`;

        const params = new URLSearchParams();
        const addParam = (key, value, defaultValue = '') => {
            if (value && value !== defaultValue) params.set(key, value);
        };

        addParam('seller_type', domElements.sellerType?.value || '', 'all');
        addParam('brand_id', brandSlug ? '' : (domElements.brand?.value || ''));
        addParam('model_id', modelSlug ? '' : (domElements.model?.value || ''));
        addParam('caroserie_id', domElements.body?.value || '');
        addParam('combustibil_id', domElements.fuel?.value || '');
        addParam('cutie_viteze_id', domElements.gear?.value || '');

        const queryString = params.toString();
        return `${baseUrl}${path}${queryString ? `?${queryString}` : ''}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.iaCombobox?.init(document);
        document.querySelectorAll('select.autovit-select').forEach(enhanceSelect);

        window.checkResetVisibility();

        const sellerTabs = document.querySelectorAll('.seller-tab');
        const sellerInput = domElements.sellerType;
        const sellerActiveClasses = ['border-b-[#C81424]', 'bg-white', 'text-[#C81424]', 'hover:bg-white', 'hover:text-[#C81424]', 'dark:bg-[#2a1013]', 'dark:text-red-300', 'dark:hover:bg-[#3a171c]', 'dark:hover:text-red-200'];
        const sellerInactiveClasses = ['border-b-transparent', 'bg-white', 'text-[#687080]', 'hover:bg-[#F7F8FA]', 'hover:text-[#30323A]', 'dark:bg-transparent', 'dark:text-gray-400', 'dark:hover:bg-[#2a2f36]', 'dark:hover:text-white'];
        const sellerLegacyClasses = ['border-[#C81424]', 'border-transparent', 'text-gray-900', 'text-[#8f111a]', 'bg-[#fff0f2]', 'dark:text-white', 'dark:bg-[#3a171c]', 'dark:bg-[#333]', 'bg-transparent', 'text-gray-500', 'hover:text-gray-900', 'bg-[#30323A]', 'hover:bg-[#30323A]', 'bg-[#F7F8FA]', 'hover:bg-[#EEF1F4]', 'bg-[#2F3137]', 'shadow-[0_8px_18px_rgba(17,24,39,0.18)]', 'hover:bg-[#26282D]', 'rounded-md'];
        const setActiveSellerTab = (val) => {
            sellerTabs.forEach(tab => {
                const isActive = tab.dataset.seller === val;
                if (isActive) {
                    tab.classList.remove(...sellerInactiveClasses, ...sellerLegacyClasses);
                    tab.classList.add(...sellerActiveClasses);
                } else {
                    tab.classList.remove(...sellerActiveClasses, ...sellerLegacyClasses);
                    tab.classList.add(...sellerInactiveClasses);
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

        brandFields().forEach((el) => loadOnFirstComboboxOpen(el, ensureBrandsLoaded));
        modelFields().forEach((el) => loadOnFirstComboboxOpen(el, ensureModelsLoadedForSelectedBrand));
        setupLookupCatalog(domElements.body, autoCatalogUrls.bodies);
        setupLookupCatalog(domElements.fuel, autoCatalogUrls.fuels);
        setupLookupCatalog(domElements.gear, autoCatalogUrls.transmissions);

        prefetchStaticCatalogs();
        prefetchModelsForBrand(domElements.brand?.value || domElements.quickBrand?.value || '');

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
                applyHomepageQuickFiltersToMain();
                modelsLoadedForBrand = null;
                resetModelFields(!!domElements.quickBrand.value);
                prefetchModelsForBrand(domElements.quickBrand.value || '');
                syncHomepageQuickFiltersFromMain();
                window.checkResetVisibility();
            });
        }

        if (domElements.quickModel) {
            domElements.quickModel.addEventListener('change', () => {
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
            setComboboxValue(domElements.quickBrand, brandId, { dispatch: false });
            modelsLoadedForBrand = null;
            resetModelFields(!!brandId);
            prefetchModelsForBrand(brandId);
            syncHomepageQuickFiltersFromMain();
            window.checkResetVisibility();
        };

        if (domElements.brand) {
            domElements.brand.addEventListener('change', handleBrandChange);
        }

        if (domElements.model) {
            domElements.model.addEventListener('change', function() {
                syncHomepageQuickFiltersFromMain();
                window.checkResetVisibility();
            });
        }

        [domElements.body, domElements.fuel, domElements.gear].forEach(el => el && el.addEventListener('change', window.checkResetVisibility));

        syncHomepageQuickFiltersFromMain();
    });

    document.addEventListener('click', (event) => {
        if (!event.target.closest('.custom-select')) {
            closeCustomSelects();
        }
    });

    window.toggleHeart = function(btn, serviceId) {
        window.iaAutoFavorites?.toggle(btn, serviceId);
    }
</script>
<style>
    .homepage-hero-visual {
        background: transparent;
    }

    .homepage-hero-visual::before {
        content: "";
        position: absolute;
        inset: 0;
        z-index: -10;
        pointer-events: none;
        background: linear-gradient(90deg, rgba(255, 255, 255, 0.99) 0%, rgba(255, 255, 255, 0.97) 36%, rgba(255, 255, 255, 0.82) 48%, rgba(255, 255, 255, 0.24) 70%, rgba(255, 255, 255, 0.02) 100%);
    }

    .homepage-hero-image {
        object-position: center right;
    }

    @media (max-width: 1023px) {
        .homepage-hero-image {
            object-position: 38% center;
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
        .homepage-hero-image {
            object-position: 40% center;
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
        .homepage-hero-image {
            object-position: 22% center;
        }

        .homepage-quick-submit-extra {
            display: none;
        }
    }

    @media (max-width: 390px) {
        .homepage-hero-image {
            object-position: 16% center;
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
            margin-top: 1.8rem !important;
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
            padding-top: 1.25rem;
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
            font-size: 0.9rem;
        }

        .homepage-filter-panel .seller-tab {
            font-size: 0.9rem;
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
            height: 42px;
        }

        .homepage-quick-filters {
            display: none !important;
        }

        .homepage-advanced-filters {
            display: block !important;
        }
    }

    @media (min-width: 1536px) {
        .homepage-filter-wrap {
            margin-top: 2.3rem !important;
        }
    }

    @media (min-width: 1024px) and (max-width: 1365px) {
        .homepage-detail-link {
            gap: 0.35rem;
            padding-left: 0.5rem;
            padding-right: 0.5rem;
        }

        .homepage-detail-link svg {
            display: none;
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
        .homepage-hero-visual::before {
            background: linear-gradient(90deg, rgba(18, 18, 18, 0.99) 0%, rgba(18, 18, 18, 0.94) 34%, rgba(18, 18, 18, 0.72) 52%, rgba(18, 18, 18, 0.18) 78%, rgba(18, 18, 18, 0.02) 100%);
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
