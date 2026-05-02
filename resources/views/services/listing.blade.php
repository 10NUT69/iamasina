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
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Anunțuri auto</h1>
            </div>

            <div class="hidden lg:block mb-4">
                <p class="text-sm text-gray-500">Prima pagină · Anunțuri auto</p>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Anunțuri auto</h1>
                <p class="text-sm text-gray-500 mt-1">Număr de anunțuri: {{ number_format($totalCount, 0, ',', '.') }}</p>
            </div>

            <div id="filters-overlay" class="fixed inset-0 bg-black/40 z-[1000] hidden lg:hidden"></div>
            <div id="filters-panel"
                 class="fixed inset-0 z-[1001] hidden pointer-events-none lg:static lg:block lg:z-auto lg:pointer-events-auto">
                <div class="filters-panel-sheet pointer-events-auto bg-white dark:bg-[#1E1E1E] h-full lg:h-auto w-full max-w-md lg:max-w-none lg:rounded-2xl lg:shadow-md border border-gray-200 dark:border-[#333333] overflow-y-auto">
                    <div class="sticky top-0 z-20 flex items-center justify-between px-4 py-4 border-b border-gray-200 bg-white dark:bg-[#1E1E1E] dark:border-[#333333] lg:hidden">
                        <h2 class="text-lg font-bold text-gray-900 dark:text-white">Filtrează</h2>
                        <button type="button" id="close-filters" class="inline-flex h-10 w-10 items-center justify-center rounded-full border border-gray-200 bg-white text-gray-600 shadow-sm transition hover:border-[#CC2E2E] hover:text-[#CC2E2E] dark:border-[#333333] dark:bg-[#2d2d2d] dark:text-gray-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                            </svg>
                        </button>
                    </div>

                    <form id="search-form" class="p-4 space-y-4">
                        <input type="hidden" name="vehicle_type" id="vehicle-type" value="anunturi-auto-de-vanzare">
                        <input type="hidden" name="seller_type" id="seller-type" value="{{ request('seller_type', 'all') }}">

                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">De unde vrei să cumperi?</p>
                            <select id="seller-type-select" class="autovit-select listing-filter">
                                <option value="all" @selected(request('seller_type', 'all') === 'all')>Parcuri + Proprietari</option>
                                <option value="individual" @selected(request('seller_type') === 'individual')>Proprietari</option>
                                <option value="dealer" @selected(request('seller_type') === 'dealer')>Parcuri</option>
                            </select>
                        </div>

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

                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">An fabricație</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="year-min" name="year_min" placeholder="Min" value="{{ request('year_min', request('an_min')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#CC2E2E] focus:ring-2 focus:ring-[#CC2E2E]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                                <input type="number" id="year-max" name="year_max" placeholder="Max" value="{{ request('year_max', request('an_max')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#CC2E2E] focus:ring-2 focus:ring-[#CC2E2E]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Km</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="km-min" name="km_min" placeholder="Min" value="{{ request('km_min') }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#CC2E2E] focus:ring-2 focus:ring-[#CC2E2E]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                                <input type="number" id="km-max" name="km_max" placeholder="Max" value="{{ request('km_max') }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#CC2E2E] focus:ring-2 focus:ring-[#CC2E2E]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                            </div>
                        </div>

                        <div>
                            <p class="text-sm font-semibold text-gray-700 mb-2">Preț (EUR)</p>
                            <div class="grid grid-cols-2 gap-2">
                                <input type="number" id="price-min" name="price_min" placeholder="Min" value="{{ request('price_min', request('pret_min')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#CC2E2E] focus:ring-2 focus:ring-[#CC2E2E]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
                                <input type="number" id="price-max" name="price_max" placeholder="Max" value="{{ request('price_max', request('pret_max')) }}"
                                    class="listing-filter w-full h-[46px] px-3 rounded-lg border border-gray-200 text-sm font-medium text-gray-900 bg-white focus:border-[#CC2E2E] focus:ring-2 focus:ring-[#CC2E2E]/10 outline-none dark:bg-[#2d2d2d] dark:border-[#404040] dark:text-gray-100">
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
        priceMin: document.getElementById('price-min'),
        priceMax: document.getElementById('price-max'),
        kmMin: document.getElementById('km-min'),
        kmMax: document.getElementById('km-max'),
        yearMin: document.getElementById('year-min'),
        yearMax: document.getElementById('year-max'),
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
        addParam('car_generation_id', domElements.gen?.value || '');
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
            domElements.brand, domElements.model, domElements.gen,
            domElements.body, domElements.fuel, domElements.gear, domElements.county,
            domElements.locality, domElements.priceMin,
            domElements.priceMax, domElements.kmMin, domElements.kmMax,
            domElements.yearMin, domElements.yearMax
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
        document.querySelectorAll('select.autovit-select').forEach(enhanceSelect);

        window.checkResetVisibility();

        const filterOverlay = document.getElementById('filters-overlay');
        const filterPanel = document.getElementById('filters-panel');
        const openFilters = document.getElementById('open-filters');
        const closeFilters = document.getElementById('close-filters');
        const sellerSelect = document.getElementById('seller-type-select');

        const setMobileFiltersOffset = () => {
            const nav = document.getElementById('main-nav');
            const navHeight = nav ? Math.ceil(nav.getBoundingClientRect().height) : 56;
            document.documentElement.style.setProperty('--mobile-filters-top', `${navHeight}px`);
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

        window.addEventListener('resize', setMobileFiltersOffset);
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
                if (!isMobileView()) {
                    resetLocalities();
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
                if (!isMobileView() && domElements.locality.value) {
                    window.location.href = buildSearchUrl();
                    return;
                }
                debounceLoad();
                window.checkResetVisibility();
            });
        }

        if (domElements.sort) {
            domElements.sort.addEventListener('change', () => {
                window.location.href = buildSearchUrl();
            });
        }

        if (sellerSelect) {
            sellerSelect.addEventListener('change', () => {
                if (domElements.sellerType) {
                    domElements.sellerType.value = sellerSelect.value || 'all';
                }
                if (!isMobileView()) {
                    window.location.href = buildSearchUrl();
                } else {
                    window.checkResetVisibility();
                }
            });
        }

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

                resetSelect(domElements.model, 'Model');
                resetSelect(domElements.gen, 'Generație');

                if (slug && !isMobileView()) {
                    window.location.href = buildSearchUrl();
                    return;
                }

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
                        window.location.href = buildSearchUrl();
                        return;
                    }
                }

                window.checkResetVisibility();
            });
        }

        if (domElements.county && domElements.county.value) {
            loadLocalities(domElements.county.value, initialLocalityId);
        } else {
            resetLocalities();
        }

        [domElements.gen, domElements.body, domElements.fuel, domElements.gear].forEach(el => {
            if (el) {
                el.addEventListener('change', () => {
                    debounceLoad();
                    window.checkResetVisibility();
                });
            }
        });

        [domElements.priceMin, domElements.priceMax, domElements.kmMin, domElements.kmMax, domElements.yearMin, domElements.yearMax].forEach(el => {
            if (el) {
                el.addEventListener('input', () => {
                    debounceLoad();
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
    :root {
        --mobile-filters-top: 56px;
    }

    @media (max-width: 1023px) {
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
        border-color: #CC2E2E;
        box-shadow: 0 0 0 3px rgba(204, 46, 46, 0.12);
    }

    .custom-select.is-open .custom-select-chevron {
        color: #CC2E2E;
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
        border: 1px solid rgba(204, 46, 46, 0.22);
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
        color: #CC2E2E;
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
        color: #b02222;
    }

    .custom-select-option.is-selected {
        background: #CC2E2E;
        color: #ffffff;
        font-weight: 700;
    }

    .custom-select-option.is-selected:hover,
    .custom-select-option.is-selected:focus-visible {
        background: #b02222;
        color: #ffffff;
    }

    .custom-select-option.is-placeholder:not(.is-selected) {
        color: #6b7280;
    }

    .custom-select-option:disabled {
        color: #9ca3af;
        cursor: not-allowed;
    }

    .dark .custom-select-trigger {
        border-color: #404040;
        background: #2d2d2d;
        color: #e5e7eb;
    }

    .dark .custom-select.is-disabled .custom-select-trigger {
        background: #1a1a1a;
        color: #555555;
    }

    .dark .custom-select-menu {
        border-color: rgba(204, 46, 46, 0.35);
        background: #252525;
        box-shadow: 0 18px 36px rgba(0, 0, 0, 0.36);
    }

    .dark .custom-select-group + .custom-select-group {
        border-top-color: #333333;
    }

    .dark .custom-select-option {
        color: #e5e7eb;
    }

    .dark .custom-select-option:hover,
    .dark .custom-select-option:focus-visible {
        background: rgba(204, 46, 46, 0.16);
        color: #ffffff;
    }

    .dark .custom-select-option.is-selected {
        background: #CC2E2E;
        color: #ffffff;
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
