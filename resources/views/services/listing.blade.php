@extends('layouts.app')

@section('title', 'Anunțuri auto – autoturisme și servicii auto')
@section('meta_description', 'Caută autoturisme după marcă, model, generație, caroserie, combustibil, cutie de viteze și locație. Publică sau găsește rapid anunțuri auto.')
@section('meta_image', asset('images/social-share.webp'))

@section('content')
<div class="max-w-7xl mx-auto px-4 pt-6 pb-12">
    <div class="flex flex-col lg:flex-row gap-6">
        {{-- Sidebar filtre (desktop) --}}
        <aside class="lg:w-[300px]">
            <div class="lg:hidden flex items-center justify-between mb-4">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Autoturisme</h1>
            </div>

            <div class="hidden lg:block mb-4">
                <p class="text-sm text-gray-500">Prima pagină · Autoturisme</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Autoturisme</h1>
                <p class="text-sm text-gray-500 mt-1">Număr de anunțuri: {{ number_format($totalCount, 0, ',', '.') }}</p>
            </div>

            <div id="filters-overlay" class="fixed inset-0 bg-black/40 z-40 hidden lg:hidden"></div>
            <div id="filters-panel"
                 class="fixed inset-0 z-50 hidden lg:static lg:block lg:z-auto">
                <div class="bg-white dark:bg-[#1E1E1E] h-full lg:h-auto w-full max-w-md lg:max-w-none lg:rounded-2xl lg:shadow-md border border-gray-200 dark:border-[#333333] overflow-y-auto">
                    <div class="flex items-center justify-between px-4 py-4 border-b border-gray-200 dark:border-[#333333] lg:hidden">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Filtrează</h2>
                        <button type="button" id="close-filters" class="text-gray-500 hover:text-gray-900">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <form id="search-form" class="p-4 space-y-4">
                        <input type="hidden" name="vehicle_type" id="vehicle-type" value="autoturisme">
                        <input type="hidden" name="seller_type" id="seller-type" value="all">

                        <select id="brand-filter" name="brand_id" class="autovit-select listing-filter">
                            <option value="">Marcă</option>
                            @php
                                $populareNume = ['Audi', 'BMW', 'Dacia', 'Ford', 'Opel', 'Renault', 'Volkswagen', 'Mercedes-Benz', 'Skoda'];
                                $brandsPopulare = $brands->whereIn('name', $populareNume)->sortBy('name');
                                $toateMarcile = $brands->sortBy('name');
                                $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
                            @endphp

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

                        <select id="model-filter" name="model_id" class="autovit-select listing-filter bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                            <option value="">Model</option>
                        </select>

                        <select id="generation-filter" name="car_generation_id" class="autovit-select listing-filter bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                            <option value="">Generație</option>
                        </select>

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
                                <option value="{{ $county->id }}" data-slug="{{ $county->slug }}" @selected((string)optional($currentCounty)->id === (string)$county->id)>{{ $county->name }}</option>
                            @endforeach
                        </select>

                        <select id="locality-input" name="locality_id" class="autovit-select listing-filter" disabled>
                            <option value="">Localitate</option>
                        </select>

                        <select id="radius-input" name="radius_km" class="autovit-select listing-filter" disabled>
                            <option value="">Rază (km)</option>
                            @foreach ([5, 10, 25, 50, 100] as $radius)
                                <option value="{{ $radius }}" @selected((string)request('radius_km') === (string)$radius)>{{ $radius }} km</option>
                            @endforeach
                        </select>

                        <div class="flex gap-2">
                            <button type="button" id="reset-btn" onclick="resetFilters()" disabled
                                    class="h-[46px] w-[46px] flex items-center justify-center rounded-lg border border-gray-200 bg-gray-50 text-gray-300 transition-all duration-200 cursor-not-allowed"
                                    title="Șterge toate filtrele">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                            <button type="submit" class="h-[46px] flex-1 bg-[#CC2E2E] hover:bg-[#b02222] text-white font-bold text-sm rounded-lg shadow-md transition-all flex items-center justify-center gap-2 uppercase tracking-wide">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                </svg>
                                Vezi rezultatele
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </aside>

        <div class="flex-1">
            <div class="mb-4">
                <p class="text-sm font-semibold text-gray-700 mb-2">De unde vrei să cumperi?</p>
                <div class="flex flex-wrap gap-2">
                    <button type="button" data-seller="all"
                        class="seller-tab px-3 py-2 text-sm font-bold rounded-lg border border-gray-200 transition-colors {{ request('seller_type', 'all') === 'all' ? 'bg-[#CC2E2E] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                        Parcuri + Proprietari
                    </button>
                    <button type="button" data-seller="individual"
                        class="seller-tab px-3 py-2 text-sm font-bold rounded-lg border border-gray-200 transition-colors {{ request('seller_type') === 'individual' ? 'bg-[#CC2E2E] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                        Proprietari
                    </button>
                    <button type="button" data-seller="dealer"
                        class="seller-tab px-3 py-2 text-sm font-bold rounded-lg border border-gray-200 transition-colors {{ request('seller_type') === 'dealer' ? 'bg-[#CC2E2E] text-white shadow-sm' : 'text-gray-600 hover:bg-gray-100' }}">
                        Parcuri
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
                <div class="flex flex-col sm:flex-row sm:flex-wrap items-start sm:items-center gap-2 w-full sm:w-auto">
                    <button type="button" id="open-filters"
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-lg border border-gray-200 bg-white text-sm font-semibold text-gray-700 shadow-sm lg:hidden">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500" viewBox="0 0 20 20" fill="currentColor">
                            <path d="M2 4a1 1 0 011-1h14a1 1 0 01.8 1.6L12 12.333V16a1 1 0 01-1.447.894l-2-1A1 1 0 018 15V12.333L2.2 4.6A1 1 0 012 4z" />
                        </svg>
                        Filtrează
                    </button>
                    <button type="button"
                        class="inline-flex items-center gap-2 px-3 py-2 text-sm font-semibold text-[#0F5CC0] border border-[#0F5CC0] rounded-lg bg-white hover:bg-blue-50 transition-colors">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V4a2 2 0 10-4 0v1.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0a3 3 0 11-6 0h6z"/>
                        </svg>
                        Salvează căutarea
                    </button>
                </div>

                <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                    <label for="sort-select" class="text-sm font-semibold text-gray-600">Sortare</label>
                    <select id="sort-select" class="autovit-select listing-filter w-full sm:w-56">
                        <option value="newest" @selected(request('sort', 'newest') === 'newest')>Anunțuri noi</option>
                        <option value="price_asc" @selected(request('sort') === 'price_asc')>Ieftine</option>
                        <option value="price_desc" @selected(request('sort') === 'price_desc')>Scumpe</option>
                        <option value="km_asc" @selected(request('sort') === 'km_asc')>Km crescător</option>
                        <option value="power_asc" @selected(request('sort') === 'power_asc')>Putere crescător</option>
                    </select>
                </div>
            </div>

            <div id="services-container" class="flex flex-col gap-4">
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
        </div>
    </div>
</div>

<script>
    const isHomepage = false;
    const homeUrl = "{{ route('cars.index') }}";
    const listUrl = "{{ url()->current() }}";
    const baseUrl = "{{ url('/') }}";
    const initialModelId = @json(optional($currentModel)->id);

    let isLoading = false;
    let currentPage = 2;
    let hasMore = document.getElementById('load-more-trigger')?.dataset.hasMore === 'true';
    let debounceTimer;

    const carData = @json($carData ?? []);
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const initialLocalityId = @json(optional($currentLocality)->id);
    const initialRadius = @json($currentRadius);
    const mobileQuery = window.matchMedia('(max-width: 1023px)');

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
        sort: document.getElementById('sort-select'),
        resetBtn: document.getElementById('reset-btn'),
        container: document.getElementById('services-container'),
        loader: document.getElementById('loading-indicator'),
        trigger: document.getElementById('load-more-trigger'),
        vehicleType: document.getElementById('vehicle-type'),
        sellerType: document.getElementById('seller-type'),
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
            sort: domElements.sort?.value || '',
        });

        [...params.keys()].forEach((key) => {
            if (!params.get(key) || (key === 'sort' && params.get(key) === 'newest')) {
                params.delete(key);
            }
        });

        const queryString = params.toString();
        return `${baseUrl}${path}${queryString ? `?${queryString}` : ''}`;
    }

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
        window.location.href = homeUrl;
    };

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
            car_generation_id: domElements.gen?.value || '',
            caroserie_id: domElements.body?.value || '',
            combustibil_id: domElements.fuel?.value || '',
            cutie_viteze_id: domElements.gear?.value || '',
            county_id: domElements.county?.value || '',
            locality_id: domElements.locality?.value || '',
            radius_km: domElements.radius?.value || '',
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
        if (isMobileView()) return;
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => window.loadServices(1), 400);
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.checkResetVisibility();

        const filterOverlay = document.getElementById('filters-overlay');
        const filterPanel = document.getElementById('filters-panel');
        const openFilters = document.getElementById('open-filters');
        const closeFilters = document.getElementById('close-filters');
        const sellerTabs = document.querySelectorAll('.seller-tab');

        if (openFilters && filterOverlay && filterPanel) {
            openFilters.addEventListener('click', () => {
                filterOverlay.classList.remove('hidden');
                filterPanel.classList.remove('hidden');
            });
        }

        [filterOverlay, closeFilters].forEach((el) => {
            if (el) {
                el.addEventListener('click', () => {
                    filterOverlay?.classList.add('hidden');
                    filterPanel?.classList.add('hidden');
                });
            }
        });

        const searchForm = document.getElementById('search-form');
        if (searchForm) {
            searchForm.addEventListener('submit', (event) => {
                event.preventDefault();
                if (isMobileView()) {
                    filterOverlay?.classList.add('hidden');
                    filterPanel?.classList.add('hidden');
                }
                window.location.href = buildSearchUrl();
            });
        }

        if (domElements.county) {
            domElements.county.addEventListener('change', () => {
                const countyOption = domElements.county.selectedOptions?.[0];
                const countySlug = countyOption?.dataset?.slug;
                if (!isMobileView() && domElements.brand?.value && domElements.model?.value && countySlug) {
                    window.location.href = buildSearchUrl();
                    return;
                }
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

        if (domElements.sort) {
            domElements.sort.addEventListener('change', () => {
                window.location.href = buildSearchUrl();
            });
        }

        const setActiveSellerTab = (selectedValue) => {
            sellerTabs.forEach(tab => {
                const isActive = tab.dataset.seller === selectedValue;
                tab.classList.toggle('bg-[#CC2E2E]', isActive);
                tab.classList.toggle('text-white', isActive);
                tab.classList.toggle('shadow-sm', isActive);
                tab.classList.toggle('text-gray-600', !isActive);
                tab.classList.toggle('hover:bg-gray-100', !isActive);
            });
        };

        if (sellerTabs.length && domElements.sellerType) {
            setActiveSellerTab(domElements.sellerType.value || 'all');
        }

        sellerTabs.forEach(btn => {
            btn.addEventListener('click', () => {
                const val = btn.dataset.seller;
                if (domElements.sellerType) domElements.sellerType.value = val;
                setActiveSellerTab(val);
                if (!isMobileView()) {
                    window.location.href = buildSearchUrl();
                } else {
                    window.checkResetVisibility();
                }
            });
        });

        if (domElements.brand && domElements.brand.value) {
            const brandId = domElements.brand.value;

            resetSelect(domElements.model, 'Model');
            resetSelect(domElements.gen, 'Generație');

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
                const selectedOption = this.options[this.selectedIndex];
                const slug = selectedOption ? selectedOption.getAttribute('data-slug') : null;

                if (!brandId) {
                    resetSelect(domElements.model, 'Model');
                    resetSelect(domElements.gen, 'Generație');
                    if (!isMobileView()) {
                        window.location.href = homeUrl;
                        return;
                    }
                    return;
                }

                if (slug && !isMobileView()) {
                    window.location.href = `${baseUrl}/autoturisme/${slug}`;
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

        if (domElements.model) {
            domElements.model.addEventListener('change', function () {
                const brandOption = domElements.brand?.selectedOptions?.[0];
                const modelOption = this.selectedOptions?.[0];
                const brandSlug = brandOption?.dataset?.slug;
                const modelSlug = modelOption?.dataset?.slug;

                resetSelect(domElements.gen, 'Generație');

                const brandId = domElements.brand.value;
                const modelId = this.value;

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

                if (brandSlug && modelSlug) {
                    if (!isMobileView()) {
                        window.location.href = `${baseUrl}/autoturisme/${brandSlug}/${modelSlug}`;
                        return;
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

        [domElements.gen, domElements.body, domElements.fuel, domElements.gear].forEach(el => {
            if (el) {
                el.addEventListener('change', () => {
                    debounceLoad();
                    window.checkResetVisibility();
                });
            }
        });

        if (domElements.trigger) observer.observe(domElements.trigger);
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

    .autovit-select.listing-filter {
        width: 100%;
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
