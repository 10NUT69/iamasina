@extends('layouts.app')

@section('title', 'Anunțuri auto – autoturisme și servicii auto')
@section('meta_description', 'Caută autoturisme după marcă, model, generație, caroserie, combustibil, cutie de viteze și locație. Publică sau găsește rapid anunțuri auto.')
@section('meta_image', asset('images/social-share.webp'))

@section('hero')
<div class="w-full bg-[#CFE8FF] mt-6 md:mt-14">
    <div class="max-w-7xl mx-auto px-4 py-8 md:py-8">
        {{-- CARD FILTRE --}}
        <div class="bg-white dark:bg-[#1E1E1E] rounded-xl shadow-2xl overflow-hidden w-full md:max-w-3xl md:mx-auto border border-gray-100 dark:border-[#333] relative z-20">

            {{-- TABURI (De unde cumperi) --}}
            <div class="px-4 pt-4 md:pt-3 text-sm font-semibold text-gray-700 dark:text-gray-200">
                De unde vrei să cumperi?
            </div>
            <div class="flex border-b border-gray-200 dark:border-[#333] bg-gray-50 dark:bg-[#252525] mt-3 md:mt-2">
                <button type="button" data-seller="all"
                    class="seller-tab px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-300 flex items-center gap-2 transition-colors">
                    Parcuri + Proprietari
                </button>

                <button type="button" data-seller="individual"
                    class="seller-tab px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-300 flex items-center gap-2 transition-colors">
                    Proprietari
                </button>

                <button type="button" data-seller="dealer"
                    class="seller-tab px-4 py-2 text-sm font-bold text-gray-600 dark:text-gray-300 flex items-center gap-2 transition-colors">
                    Parcuri
                </button>
            </div>

            {{-- ZONA FILTRE --}}
            <div class="p-4 md:p-3">
                <form id="search-form">
                    <input type="hidden" name="vehicle_type" id="vehicle-type" value="autoturisme">
                    <input type="hidden" name="seller_type" id="seller-type" value="{{ request('seller_type', 'all') }}">

                    {{-- GRID --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-2.5">

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

                        {{-- IMPORTANT: county_id (nu county) --}}
                        <div class="col-span-2 md:col-span-1">
                            <select id="county-input" name="county_id" class="autovit-select">
                                <option value="">Toată țara</option>
                                @foreach($counties as $county)
                                    <option value="{{ $county->id }}" data-slug="{{ $county->slug }}" @selected((string)request('county_id') === (string)$county->id)>{{ $county->name }}</option>
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
                                    class="h-[46px] w-[46px] md:h-[40px] md:w-[40px] flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 transition-all duration-200 cursor-not-allowed"
                                    title="Șterge toate filtrele">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <button type="submit" class="h-[46px] md:h-[40px] flex-1 bg-[#CC2E2E] hover:bg-[#b02222] text-white font-bold text-base md:text-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
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

<div id="services-container" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 pb-10 relative z-0 max-w-7xl mx-auto px-4">
    @include('services.partials.service_cards', ['services' => $services])
</div>

<script>
    const baseUrl = "{{ url('/') }}";

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
        resetBtn: document.getElementById('reset-btn'),
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
            domElements.locality, domElements.radius
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
        if (domElements.brand) domElements.brand.value = '';

        resetSelect(domElements.model, 'Model');
        resetSelect(domElements.gen, 'Generație');

        if (domElements.body) domElements.body.value = '';
        if (domElements.fuel) domElements.fuel.value = '';
        if (domElements.gear) domElements.gear.value = '';
        if (domElements.county) domElements.county.value = '';
        if (domElements.locality) resetLocalities();
        if (domElements.radius) resetRadius();

        window.checkResetVisibility();
    };

    function buildSearchUrl() {
        const brandOption = domElements.brand?.selectedOptions?.[0];
        const modelOption = domElements.model?.selectedOptions?.[0];
        const countyOption = domElements.county?.selectedOptions?.[0];

        const brandSlug = brandOption?.dataset?.slug;
        const modelSlug = modelOption?.dataset?.slug;
        const countySlug = countyOption?.dataset?.slug;

        let path = '/autoturisme';
        if (brandSlug) {
            path += `/${brandSlug}`;
        }
        if (brandSlug && modelSlug) {
            path += `/${modelSlug}`;
        }
        if (brandSlug && modelSlug && countySlug) {
            path += `/${countySlug}`;
        }

        const params = new URLSearchParams({
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
        });

        [...params.keys()].forEach((key) => {
            if (!params.get(key)) {
                params.delete(key);
            }
        });

        const queryString = params.toString();
        return `${baseUrl}${path}${queryString ? `?${queryString}` : ''}`;
    }

    // --- INITIALIZARE ---
    document.addEventListener('DOMContentLoaded', () => {
        window.checkResetVisibility();
        const sellerTabs = document.querySelectorAll('.seller-tab');
        const sellerInput = domElements.sellerType;

        const setActiveSellerTab = (selectedValue) => {
            sellerTabs.forEach(tab => {
                const isActive = tab.dataset.seller === selectedValue;
                tab.classList.toggle('bg-[#CC2E2E]', isActive);
                tab.classList.toggle('text-white', isActive);
                tab.classList.toggle('shadow-sm', isActive);
                tab.classList.toggle('text-gray-600', !isActive);
                tab.classList.toggle('dark:text-gray-300', !isActive);
            });
        };

        if (sellerTabs.length && sellerInput) {
            setActiveSellerTab(sellerInput.value || 'all');
            sellerTabs.forEach(tab => {
                tab.addEventListener('click', () => {
                    const sellerValue = tab.dataset.seller || 'all';
                    sellerInput.value = sellerValue;
                    setActiveSellerTab(sellerValue);
                });
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
                if (!domElements.locality.value) {
                    resetRadius();
                } else if (domElements.radius) {
                    domElements.radius.disabled = false;
                }
                window.checkResetVisibility();
            });
        }

        if (domElements.radius) {
            domElements.radius.addEventListener('change', () => {
                window.checkResetVisibility();
            });
        }

        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                window.location.href = buildSearchUrl();
            });
        }

        // Dacă avem deja brand selectat (ex: pagină SEO /autoturisme/{slug}), populăm MODELELE
        if (domElements.brand && domElements.brand.value) {
            const brandId = domElements.brand.value;

            resetSelect(domElements.model, 'Model');
            resetSelect(domElements.gen, 'Generație');

            if (carData[brandId]) {
                enableSelect(domElements.model);
                carData[brandId].forEach(m => {
                    domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}">${m.name}</option>`;
                });
            }
        }

        // 1) Brand -> reset + populate
        if (domElements.brand) {
            domElements.brand.addEventListener('change', function () {
                const brandId = this.value;

                if (!brandId) {
                    resetSelect(domElements.model, 'Model');
                    resetSelect(domElements.gen, 'Generație');
                    window.checkResetVisibility();
                    return;
                }

                resetSelect(domElements.model, 'Model');
                resetSelect(domElements.gen, 'Generație');

                if (carData[brandId]) {
                    enableSelect(domElements.model);
                    carData[brandId].forEach(m => {
                        domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}">${m.name}</option>`;
                    });
                }

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

        // 3) Listeneri pentru restul filtrelor (doar actualizare UI)
        [domElements.gen, domElements.body, domElements.fuel, domElements.gear].forEach(el => {
            if (el) {
                el.addEventListener('change', () => {
                    window.checkResetVisibility();
                });
            }
        });
    });

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
            width: 8rem;
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
    @media (min-width: 768px) {
        .autovit-select {
            height: 40px;
            font-size: 0.8rem;
        }
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
