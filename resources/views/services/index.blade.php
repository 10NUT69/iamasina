@extends('layouts.app')

@section('title', 'Anunțuri auto – autoturisme și servicii auto')
@section('meta_description', 'Caută autoturisme după marcă, model, generație, caroserie, combustibil, cutie de viteze și locație. Publică sau găsește rapid anunțuri auto.')
@section('meta_image', asset('images/social-share.webp'))

@section('hero')
<div class="relative w-full group font-sans">

    {{-- FUNDAL --}}
    <div class="absolute inset-0 h-[460px] md:h-[340px] w-full overflow-hidden z-0">
        <img src="{{ asset('images/hero-desktop.webp') }}" alt="Fundal auto"
             class="hidden md:block w-full h-full object-cover object-center opacity-90">
        <img src="{{ asset('images/hero-mobile.webp') }}" alt="Fundal auto"
             class="block md:hidden w-full h-full object-cover object-center opacity-80">
        {{-- Gradient --}}
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/80 via-gray-900/50 to-transparent"></div>
    </div>

    {{-- CONTAINER CONȚINUT --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 pt-20 md:pt-28 pb-8 flex flex-col md:flex-row md:items-end md:justify-between gap-6">

        {{-- CARD FILTRE (STÂNGA) --}}
        <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-2xl overflow-hidden w-full md:w-auto border border-gray-100 dark:border-[#333] relative z-20">

            {{-- TABURI (De unde cumperi) --}}
            <div class="flex border-b border-gray-200 dark:border-[#333] bg-gray-50 dark:bg-[#252525]">
                <button type="button" data-seller="all"
                    class="seller-tab px-6 py-3 text-sm font-bold text-[#CC2E2E] border-b-2 border-[#CC2E2E] bg-white dark:bg-[#1E1E1E] flex items-center gap-2">
                    Parcuri + Proprietari
                </button>

                <button type="button" data-seller="individual"
                    class="seller-tab px-6 py-3 text-sm font-bold text-gray-500 dark:text-gray-300 flex items-center gap-2">
                    Proprietari
                </button>

                <button type="button" data-seller="dealer"
                    class="seller-tab px-6 py-3 text-sm font-bold text-gray-500 dark:text-gray-300 flex items-center gap-2">
                    Parcuri
                </button>
            </div>

            {{-- ZONA FILTRE --}}
            <div class="p-4 md:p-5">
                <form id="search-form" onsubmit="event.preventDefault(); loadServices(1);">
                    <input type="hidden" name="vehicle_type" id="vehicle-type" value="autoturisme">
                    <input type="hidden" name="seller_type" id="seller-type" value="all">

                    {{-- GRID --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                        {{-- RÂNDUL 1 --}}
                        <div class="col-span-1">
                            @php
                                $populareNume = ['Audi', 'BMW', 'Dacia', 'Ford', 'Opel', 'Renault', 'Volkswagen', 'Mercedes-Benz', 'Skoda'];
                                $brandsPopulare = $brands->whereIn('name', $populareNume)->sortBy('name');
                                $toateMarcile = $brands->sortBy('name');
                                $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
                            @endphp

                            {{-- IMPORTANT: value = brand_id (nu name) --}}
                            <select id="brand-filter" name="brand_id" class="autovit-select">
                                <option value="">Marcă</option>

                                @if($brandsPopulare->isNotEmpty())
                                    <optgroup label="Mărci Populare">
                                        @foreach($brandsPopulare as $brand)
                                            <option
                                                value="{{ $brand->id }}"
                                                data-slug="{{ $brand->slug }}"
                                                @selected($currentBrandId === $brand->id)
                                            >
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </optgroup>
                                @endif

                                <optgroup label="Toate Mărcile">
                                    @foreach($toateMarcile as $brand)
                                        <option
                                            value="{{ $brand->id }}"
                                            data-slug="{{ $brand->slug }}"
                                            @selected($currentBrandId === $brand->id)
                                        >
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                        </div>

                        {{-- IMPORTANT: value = model_id --}}
                        <div class="col-span-1">
                            <select id="model-filter" name="model_id" class="autovit-select bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                                <option value="">Model</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <select id="generation-filter" name="car_generation_id" class="autovit-select bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                                <option value="">Generație</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                            <select id="body-filter" name="caroserie_id" class="autovit-select">
                                <option value="">Caroserie</option>
                                @foreach($bodies as $body)
                                    <option value="{{ $body->id }}">{{ $body->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RÂNDUL 2 --}}
                        <div class="col-span-1">
                            <select id="fuel-filter" name="combustibil_id" class="autovit-select">
                                <option value="">Combustibil</option>
                                @foreach($fuels as $fuel)
                                    <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-1">
                            <select id="gearbox-filter" name="cutie_viteze_id" class="autovit-select">
                                <option value="">Cutie viteze</option>
                                @foreach($transmissions as $trans)
                                    <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-1">
                            <input id="price-min" name="price_min" type="number" min="0" step="100"
                                   value="{{ request('price_min') }}"
                                   class="autovit-input" placeholder="Preț de la" inputmode="numeric">
                        </div>

                        <div class="col-span-1">
                            <input id="price-max" name="price_max" type="number" min="0" step="100"
                                   value="{{ request('price_max') }}"
                                   class="autovit-input" placeholder="Preț până la" inputmode="numeric">
                        </div>

                        <div class="col-span-1">
                            <input id="year-min" name="year_min" type="number" min="1900" max="{{ date('Y') }}"
                                   value="{{ request('year_min') }}"
                                   class="autovit-input" placeholder="Anul de la" inputmode="numeric">
                        </div>

                        <div class="col-span-1">
                            <input id="year-max" name="year_max" type="number" min="1900" max="{{ date('Y') }}"
                                   value="{{ request('year_max') }}"
                                   class="autovit-input" placeholder="Anul până la" inputmode="numeric">
                        </div>

                        {{-- IMPORTANT: county_id (nu county) --}}
                        <div class="col-span-2 md:col-span-1">
                            <select id="county-input" name="county_id" class="autovit-select">
                                <option value="">Toată țara</option>
                                @foreach($counties as $county)
                                    <option value="{{ $county->id }}" @selected((string)request('county_id') === (string)$county->id)>{{ $county->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <select id="locality-input" name="locality_id" class="autovit-select" disabled>
                                <option value="">Localitate</option>
                            </select>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                            <select id="radius-input" name="radius_km" class="autovit-select" disabled>
                                <option value="">Rază (km)</option>
                                @foreach ([5, 10, 25, 50, 100] as $radius)
                                    <option value="{{ $radius }}" @selected((string)request('radius_km') === (string)$radius)>{{ $radius }} km</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- BUTOANE --}}
                        <div class="col-span-2 md:col-span-1 flex gap-2">
                            <button type="button" id="reset-btn" onclick="resetFilters()" disabled
                                    class="h-[46px] w-[46px] flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 transition-all duration-200 cursor-not-allowed"
                                    title="Șterge toate filtrele">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <button type="submit" class="h-[46px] flex-1 bg-[#CC2E2E] hover:bg-[#b02222] text-white font-bold text-base rounded-lg shadow-md transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Caută
                            </button>
                        </div>

                    </div>
                </form>
            </div>
        </div>

        {{-- TITLU (DREAPTA - ASCUNS PE MOBIL) --}}
        <div class="hidden md:block md:text-right mb-6 max-w-lg">
            <h1 class="text-white font-extrabold tracking-tight text-4xl lg:text-5xl drop-shadow-2xl leading-tight">
                Vindeți mașina<br>
                <span class="text-[#CC2E2E]">rapid și sigur.</span>
            </h1>
            <p class="text-gray-200 mt-2 text-lg font-medium drop-shadow-md">
                Publică anunțul tău în câteva minute.
            </p>
        </div>

    </div>
</div>
@endsection

{{-- ======================= CONȚINUT LISTĂ ANUNȚURI ======================= --}}
@section('content')

<div class="mt-4 md:mt-6 mb-6 flex items-center gap-3 max-w-7xl mx-auto px-4">
    <span class="w-1.5 h-8 bg-[#CC2E2E] rounded-full shadow-sm"></span>
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-[#F2F2F2]">
        Anunțuri recente
    </h2>
</div>

<div id="services-container" class="grid grid-cols-1 gap-4 pb-10 relative z-0 max-w-7xl mx-auto px-4">
    @include('services.partials.service_cards_horizontal', ['services' => $services])
</div>

<div id="loading-indicator" class="text-center py-8 {{ $services->isEmpty() || !$hasMore ? 'hidden' : '' }}">
    <svg class="animate-spin h-8 w-8 text-[#CC2E2E] mx-auto" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3.003 7.91l2.997-2.619z"></path>
    </svg>
    <p class="text-sm text-gray-500 mt-2">Se încarcă...</p>
</div>

<div id="load-more-trigger" data-next-page="2" data-has-more="{{ $hasMore ? 'true' : 'false' }}" style="height: 1px;"></div>

<script>
    // Suntem pe o pagină SEO de brand? (ex: /autoturisme/audi)
    const isBrandPage = @json(isset($currentBrand));
    const homeUrl = "{{ route('services.index') }}";

    // Variabile globale
    let isLoading = false;
    let currentPage = 2;
    let hasMore = document.getElementById('load-more-trigger')?.dataset.hasMore === 'true';
    let debounceTimer;

    // IMPORTANT: carData pe ID-uri, ca în create.blade:
    // carData[brand_id] = [{id, name, generations:[{id,name,start,end}]}]
    const carData = @json($carData ?? []);
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const initialLocalityId = @json(optional($currentLocality)->id);
    const initialRadius = @json($currentRadius);

    // Elemente DOM principale
    const domElements = {
        brand: document.getElementById('brand-filter'),
        model: document.getElementById('model-filter'),
        gen: document.getElementById('generation-filter'),
        body: document.getElementById('body-filter'),
        fuel: document.getElementById('fuel-filter'),
        gear: document.getElementById('gearbox-filter'),
        county: document.getElementById('county-input'),
        locality: document.getElementById('locality-input'),
        radius: document.getElementById('radius-input'),
        priceMin: document.getElementById('price-min'),
        priceMax: document.getElementById('price-max'),
        yearMin: document.getElementById('year-min'),
        yearMax: document.getElementById('year-max'),
        resetBtn: document.getElementById('reset-btn'),
        container: document.getElementById('services-container'),
        loader: document.getElementById('loading-indicator'),
        trigger: document.getElementById('load-more-trigger'),
        vehicleType: document.getElementById('vehicle-type'),
        sellerType: document.getElementById('seller-type'),
    };

    // --- FUNCȚII AJUTĂTOARE ---
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

    function resetLocalities() {
        if (!domElements.locality) return;
        domElements.locality.innerHTML = '<option value="">Localitate</option>';
        domElements.locality.disabled = true;
    }

    function resetRadius() {
        if (!domElements.radius) return;
        domElements.radius.value = '';
        domElements.radius.disabled = true;
    }

    function populateLocalities(localities, selectedId) {
        if (!domElements.locality) return;
        domElements.locality.innerHTML = '<option value="">Localitate</option>';
        localities.forEach(locality => {
            const option = document.createElement('option');
            option.value = locality.id;
            option.textContent = locality.name;
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
            resetRadius();
            return;
        }

        try {
            const response = await fetch(`${localityBaseUrl}/${countyId}`);
            const data = await response.json();
            populateLocalities(data, selectedId);
            if (domElements.radius && domElements.locality.value) {
                domElements.radius.disabled = false;
            }
        } catch (error) {
            console.error(error);
            resetLocalities();
            resetRadius();
        }
    }

    // --- LOGICA DE RESETARE ---
    window.checkResetVisibility = function() {
        const btn = domElements.resetBtn;
        if (!btn) return;

        const filters = [
            domElements.brand, domElements.model, domElements.gen,
            domElements.body, domElements.fuel, domElements.gear, domElements.county,
            domElements.locality, domElements.radius,
            domElements.priceMin, domElements.priceMax, domElements.yearMin, domElements.yearMax
        ];

        const hasAnyFilter = filters.some(el => el && el.value !== '');

        if (hasAnyFilter) {
            btn.disabled = false;
            btn.classList.remove('bg-gray-50', 'text-gray-300', 'cursor-not-allowed');
            btn.classList.add('bg-white', 'text-[#CC2E2E]', 'border-[#CC2E2E]', 'hover:bg-red-50', 'cursor-pointer', 'shadow-sm');
        } else {
            btn.disabled = true;
            btn.classList.remove('bg-white', 'text-[#CC2E2E]', 'border-[#CC2E2E]', 'hover:bg-red-50', 'cursor-pointer', 'shadow-sm');
            btn.classList.add('bg-gray-50', 'text-gray-300', 'cursor-not-allowed');
        }
    };

    window.resetFilters = function() {
        // Dacă suntem pe pagina de brand, reset = întoarcere acasă
        if (isBrandPage) {
            window.location.href = homeUrl;
            return;
        }

        // Reset valori
        if (domElements.brand) domElements.brand.value = '';

        resetSelect(domElements.model, 'Model');
        resetSelect(domElements.gen, 'Generație');

        if (domElements.body) domElements.body.value = '';
        if (domElements.fuel) domElements.fuel.value = '';
        if (domElements.gear) domElements.gear.value = '';
        if (domElements.county) domElements.county.value = '';
        if (domElements.locality) resetLocalities();
        if (domElements.radius) resetRadius();
        if (domElements.priceMin) domElements.priceMin.value = '';
        if (domElements.priceMax) domElements.priceMax.value = '';
        if (domElements.yearMin) domElements.yearMin.value = '';
        if (domElements.yearMax) domElements.yearMax.value = '';

        window.checkResetVisibility();
        window.loadServices(1);
    };

    // --- LOGICA DE ÎNCĂRCARE (AJAX) ---
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

        // IMPORTANT: trimitem ID-uri (brand_id, model_id, county_id)
        const params = new URLSearchParams({
            page: page,
            ajax: 1,
            vehicle_type: domElements.vehicleType?.value || '',
            seller_type: domElements.sellerType?.value || 'all',

            brand_id: domElements.brand?.value || '',
            model_id: domElements.model?.value || '',
            car_generation_id: domElements.gen?.value || '',

            caroserie_id: domElements.body?.value || '',
            combustibil_id: domElements.fuel?.value || '',
            cutie_viteze_id: domElements.gear?.value || '',
            county_id: domElements.county?.value || '',
            locality_id: domElements.locality?.value || '',
            radius_km: domElements.radius?.value || '',
            price_min: domElements.priceMin?.value || '',
            price_max: domElements.priceMax?.value || '',
            year_min: domElements.yearMin?.value || '',
            year_max: domElements.yearMax?.value || '',
        });

        fetch(`{{ route('services.index') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (isNewFilter) {
                if (domElements.container) {
                    domElements.container.innerHTML = data.html;
                    domElements.container.style.opacity = '1';
                }

                if (data.loadedCount === 0) {
                    domElements.container.innerHTML = `
                        <div class="col-span-full flex flex-col items-center justify-center py-16 px-4 text-center bg-white dark:bg-[#1E1E1E] rounded-xl border border-gray-200 dark:border-[#333]">
                            <div class="text-4xl mb-4">😕</div>
                            <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nu am găsit anunțuri</h3>
                            <p class="text-gray-500 mb-6 text-sm">Încearcă să modifici criteriile sau șterge toate filtrele.</p>

                            <button type="button" onclick="window.resetFilters()" class="px-6 py-3 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold rounded-lg shadow-md transition-colors text-sm uppercase tracking-wide">
                                Șterge toate filtrele
                            </button>
                        </div>
                    `;
                }
            } else {
                if (domElements.container) domElements.container.insertAdjacentHTML('beforeend', data.html);
            }

            hasMore = !!data.hasMore;
            if (domElements.trigger) domElements.trigger.dataset.hasMore = hasMore ? 'true' : 'false';

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

    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => window.loadServices(1), 400);
    }

    // --- INITIALIZARE ---
    document.addEventListener('DOMContentLoaded', () => {
        window.checkResetVisibility();

        if (domElements.county) {
            domElements.county.addEventListener('change', () => {
                loadLocalities(domElements.county.value);
                debounceLoad();
                window.checkResetVisibility();
            });
        }

        if (domElements.locality) {
            domElements.locality.addEventListener('change', () => {
                if (!domElements.locality.value) {
                    resetRadius();
                } else if (domElements.radius) {
                    domElements.radius.disabled = false;
                }
                debounceLoad();
                window.checkResetVisibility();
            });
        }

        if (domElements.radius) {
            domElements.radius.addEventListener('change', () => {
                debounceLoad();
                window.checkResetVisibility();
            });
        }

        // --- TABURI "De unde cumperi" ---
        document.querySelectorAll('.seller-tab').forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.dataset.seller; // all / individual / dealer
                if (domElements.sellerType) domElements.sellerType.value = val;

                // Stil activ/inactiv
                document.querySelectorAll('.seller-tab').forEach(b => {
                    b.classList.remove('text-[#CC2E2E]', 'border-b-2', 'border-[#CC2E2E]', 'bg-white', 'dark:bg-[#1E1E1E]');
                    b.classList.add('text-gray-500', 'dark:text-gray-300');
                });

                btn.classList.add('text-[#CC2E2E]', 'border-b-2', 'border-[#CC2E2E]', 'bg-white', 'dark:bg-[#1E1E1E]');
                btn.classList.remove('text-gray-500', 'dark:text-gray-300');

                window.loadServices(1);
            });
        });

        // Dacă avem deja brand selectat (ex: pagină SEO /autoturisme/{slug}), populăm MODELELE
        if (domElements.brand && domElements.brand.value) {
            const brandId = domElements.brand.value;

            resetSelect(domElements.model, 'Model');
            resetSelect(domElements.gen, 'Generație');

            if (carData[brandId]) {
                enableSelect(domElements.model);
                carData[brandId].forEach(m => {
                    domElements.model.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                });
            }
        }

        // 1) Brand -> redirect SEO / reset + ajax
        if (domElements.brand) {
            domElements.brand.addEventListener('change', function () {
                const brandId = this.value;
                const selectedOption = this.options[this.selectedIndex];
                const slug = selectedOption ? selectedOption.getAttribute('data-slug') : null;

                // Ștergere brand
                if (!brandId) {
                    if (isBrandPage) {
                        window.location.href = homeUrl;
                        return;
                    }
                    resetSelect(domElements.model, 'Model');
                    resetSelect(domElements.gen, 'Generație');
                    debounceLoad();
                    window.checkResetVisibility();
                    return;
                }

                // Dacă există slug -> redirect SEO
                if (slug) {
                    const baseUrl = "{{ url('/') }}";
                    window.location.href = `${baseUrl}/autoturisme/${slug}`;
                    return;
                }

                // fallback (dacă nu ai slug)
                resetSelect(domElements.model, 'Model');
                resetSelect(domElements.gen, 'Generație');

                if (carData[brandId]) {
                    enableSelect(domElements.model);
                    carData[brandId].forEach(m => {
                        domElements.model.innerHTML += `<option value="${m.id}">${m.name}</option>`;
                    });
                }

                debounceLoad();
                window.checkResetVisibility();
            });
        }

        // 2) Model -> Generație (ID-uri)
        if (domElements.model) {
            domElements.model.addEventListener('change', function () {
                const brandId = domElements.brand.value;
                const modelId = this.value;

                resetSelect(domElements.gen, 'Generație');

                if (brandId && modelId && carData[brandId]) {
                    const modelObj = carData[brandId].find(x => String(x.id) === String(modelId));
                    const generations = modelObj?.generations || [];

                    if (generations.length) {
                        enableSelect(domElements.gen);
                        generations.forEach(g => {
                            domElements.gen.innerHTML += `<option value="${g.id}">${g.name} (${g.start} - ${g.end || 'Prezent'})</option>`;
                        });
                    }
                }

                debounceLoad();
                window.checkResetVisibility();
            });
        }

        if (domElements.county && domElements.county.value) {
            loadLocalities(domElements.county.value, initialLocalityId).then(() => {
                if (domElements.radius) {
                    domElements.radius.value = initialRadius || '';
                    domElements.radius.disabled = !domElements.locality?.value;
                }
            });
        } else {
            resetLocalities();
            resetRadius();
        }

        // 3) Listeneri pentru restul filtrelor
        [domElements.gen, domElements.body, domElements.fuel, domElements.gear].forEach(el => {
            if (el) {
                el.addEventListener('change', () => {
                    debounceLoad();
                    window.checkResetVisibility();
                });
            }
        });

        [domElements.priceMin, domElements.priceMax, domElements.yearMin, domElements.yearMax].forEach(el => {
            if (el) {
                el.addEventListener('input', () => {
                    debounceLoad();
                    window.checkResetVisibility();
                });
                el.addEventListener('change', () => {
                    debounceLoad();
                    window.checkResetVisibility();
                });
            }
        });

        // 4) Observer pentru Infinite Scroll
        if (domElements.trigger) observer.observe(domElements.trigger);
    });

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            window.loadServices(currentPage);
        }
    }, { rootMargin: '0px 0px 400px 0px' });

    // Favorite (inimioară)
    window.toggleHeart = function(btn, serviceId) {
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}";
            return;
        @endif

        const icon = btn.querySelector('svg');
        const isLiked = icon.classList.contains('text-[#CC2E2E]');

        if (isLiked) {
            icon.classList.remove('text-[#CC2E2E]', 'fill-[#CC2E2E]', 'scale-110');
            icon.classList.add('text-gray-600', 'dark:text-gray-300', 'fill-none');
        } else {
            icon.classList.remove('text-gray-600', 'dark:text-gray-300', 'fill-none');
            icon.classList.add('text-[#CC2E2E]', 'fill-[#CC2E2E]', 'scale-125');
            setTimeout(() => { icon.classList.remove('scale-125'); icon.classList.add('scale-110'); }, 200);
        }

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
    .autovit-select {
        display: block;
        width: 100%;
        @media (min-width: 768px) {
            width: 10rem;
        }
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
        border-color: #CC2E2E;
        box-shadow: 0 0 0 3px rgba(204, 46, 46, 0.1);
    }

    .dark .autovit-select {
        background-color: #2d2d2d;
        border-color: #404040;
        color: #e5e7eb;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%239ca3af' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
    }

    .dark .autovit-select:disabled {
        background-color: #1a1a1a;
        color: #555;
        cursor: not-allowed;
    }

    .autovit-input {
        display: block;
        width: 100%;
        @media (min-width: 768px) {
            width: 10rem;
        }
        height: 46px;
        padding: 0 1rem;
        font-size: 0.9rem;
        font-weight: 500;
        color: #1f2937;
        background-color: #ffffff;
        border: 1px solid #d1d5db;
        border-radius: 0.5rem;
        transition: all 0.2s ease;
    }

    .autovit-input:focus {
        outline: none;
        border-color: #CC2E2E;
        box-shadow: 0 0 0 3px rgba(204, 46, 46, 0.1);
    }

    .dark .autovit-input {
        background-color: #2d2d2d;
        border-color: #404040;
        color: #e5e7eb;
    }

    optgroup {
        font-weight: 700;
        color: #CC2E2E;
        font-style: normal;
        background-color: #f9fafb;
    }
    option {
        color: #1f2937;
        padding: 4px;
        background-color: #fff;
    }
</style>
@endsection
