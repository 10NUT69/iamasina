@extends('layouts.app')

@section('title', 'Anunțuri auto – autoturisme și servicii auto')
@section('meta_description', 'Caută autoturisme după marcă, model, generație, caroserie, combustibil, cutie de viteze și locație.')
@section('meta_image', asset('images/social-share.webp'))

@section('hero')
{{-- HERO: Gradient subtil în loc de culoare plată pentru un look premium --}}
<div class="w-full bg-gradient-to-b from-blue-50 to-white dark:from-[#1a1a1a] dark:to-[#121212] pt-8 md:pt-14 pb-12">
    <div class="max-w-7xl mx-auto px-4">
        
        {{-- TITLU HERO (Opțional, pentru impact) --}}
        <div class="text-center mb-8 hidden md:block">
            <h1 class="text-3xl md:text-4xl font-black text-gray-900 dark:text-white tracking-tight">
                Găsește mașina <span class="text-[#CC2E2E]">perfectă</span> pentru tine
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg">Mii de anunțuri verificate de la proprietari și parcuri auto.</p>
        </div>

        {{-- CARD FILTRE --}}
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-2xl shadow-blue-900/10 dark:shadow-black/50 overflow-hidden w-full md:max-w-4xl mx-auto border border-gray-100 dark:border-[#333] relative z-20">

            {{-- TABURI (De unde cumperi) - Stil Modern --}}
            <div class="flex flex-col md:flex-row items-start md:items-center justify-between border-b border-gray-100 dark:border-[#2C2C2C] bg-gray-50/50 dark:bg-[#252525] p-2">
                <div class="px-3 py-2 text-xs font-bold text-gray-400 uppercase tracking-wider">
                    Sursă anunț
                </div>
                <div class="flex w-full md:w-auto p-1 bg-gray-200/50 dark:bg-[#1a1a1a] rounded-lg">
                    <button type="button" data-seller="all"
                        class="seller-tab flex-1 md:flex-none px-4 py-1.5 text-sm font-semibold rounded-md transition-all duration-200 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Toate
                    </button>
                    <button type="button" data-seller="individual"
                        class="seller-tab flex-1 md:flex-none px-4 py-1.5 text-sm font-semibold rounded-md transition-all duration-200 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Proprietari
                    </button>
                    <button type="button" data-seller="dealer"
                        class="seller-tab flex-1 md:flex-none px-4 py-1.5 text-sm font-semibold rounded-md transition-all duration-200 text-gray-600 dark:text-gray-300 hover:text-gray-900 dark:hover:text-white">
                        Parcuri Auto
                    </button>
                </div>
            </div>

            {{-- ZONA FORMULAR --}}
            <div class="p-5">
                <form id="search-form">
                    <input type="hidden" name="vehicle_type" id="vehicle-type" value="autoturisme">
                    <input type="hidden" name="seller_type" id="seller-type" value="{{ request('seller_type', 'all') }}">

                    {{-- GRID FILTRE --}}
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-3">

                        {{-- RÂNDUL 1 --}}
                        <div class="col-span-2 md:col-span-1">
                            @php
                                $populareNume = ['Audi', 'BMW', 'Dacia', 'Ford', 'Opel', 'Renault', 'Volkswagen', 'Mercedes-Benz', 'Skoda'];
                                $brandsPopulare = $brands->whereIn('name', $populareNume)->sortBy('name');
                                $toateMarcile = $brands->sortBy('name');
                                $currentBrandId = isset($currentBrand) ? $currentBrand->id : null;
                            @endphp
                            <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Marca</label>
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

                        <div class="col-span-1">
                             <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Model</label>
                            <select id="model-filter" name="model_id" class="autovit-select bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                                <option value="">Alege model</option>
                            </select>
                        </div>

                        <div class="col-span-1">
                             <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Generație</label>
                            <select id="generation-filter" name="car_generation_id" class="autovit-select bg-gray-50 text-gray-400 cursor-not-allowed" disabled>
                                <option value="">Generație</option>
                            </select>
                        </div>

                        <div class="col-span-2 md:col-span-1">
                             <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Caroserie</label>
                            <select id="body-filter" name="caroserie_id" class="autovit-select">
                                <option value="">Oricare</option>
                                @foreach($bodies as $body)
                                    <option value="{{ $body->id }}">{{ $body->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- RÂNDUL 2 --}}
                        <div class="col-span-1">
                             <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Combustibil</label>
                            <select id="fuel-filter" name="combustibil_id" class="autovit-select">
                                <option value="">Oricare</option>
                                @foreach($fuels as $fuel)
                                    <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-span-1">
                             <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Cutie</label>
                            <select id="gearbox-filter" name="cutie_viteze_id" class="autovit-select">
                                <option value="">Oricare</option>
                                @foreach($transmissions as $trans)
                                    <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- LOCAȚIE --}}
                        <div class="col-span-2">
                            <div class="grid grid-cols-3 gap-2">
                                <div class="col-span-1">
                                    <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Județ</label>
                                    <select id="county-input" name="county_id" class="autovit-select">
                                        <option value="">Toată țara</option>
                                        @foreach($counties as $county)
                                            <option value="{{ $county->id }}" data-slug="{{ $county->slug }}" @selected((string)request('county_id') === (string)$county->id)>{{ $county->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-span-1">
                                     <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Localitate</label>
                                    <select id="locality-input" name="locality_id" class="autovit-select" disabled>
                                        <option value="">Alege</option>
                                    </select>
                                </div>
                                <div class="col-span-1">
                                     <label class="text-[10px] font-bold text-gray-400 uppercase mb-1 block ml-1">Rază</label>
                                    <select id="radius-input" name="radius_km" class="autovit-select" disabled>
                                        <option value="">+ 0 km</option>
                                        @foreach ([5, 10, 25, 50, 100, 200] as $radius)
                                            <option value="{{ $radius }}" @selected((string)request('radius_km') === (string)$radius)>+ {{ $radius }} km</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        {{-- BUTOANE ACȚIUNE --}}
                        <div class="col-span-2 md:col-span-4 mt-2 flex gap-3 pt-4 border-t border-gray-100 dark:border-[#333]">
                            <button type="button" id="reset-btn" onclick="resetFilters()" disabled
                                    class="h-[48px] px-6 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#2C2C2C] text-gray-400 font-medium transition-all duration-200 hover:bg-gray-50 dark:hover:bg-[#333] disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                </svg>
                                <span class="hidden md:inline">Reset</span>
                            </button>

                            <button type="submit" class="h-[48px] flex-1 bg-[#CC2E2E] hover:bg-[#b02222] text-white font-bold text-base rounded-xl shadow-lg shadow-red-500/30 transition-all flex items-center justify-center gap-2 uppercase tracking-wide transform active:scale-95">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
@endsection

{{-- ======================= CONȚINUT ======================= --}}
@section('content')

<div class="max-w-7xl mx-auto px-4 mt-8">
    <div class="flex items-center justify-between mb-6">
        <div class="flex items-center gap-3">
             <div class="w-1.5 h-8 bg-[#CC2E2E] rounded-full"></div>
            <h2 class="text-2xl font-bold text-gray-900 dark:text-white">Anunțuri recente</h2>
        </div>
        
        {{-- Counter simplu --}}
        <span class="text-sm font-medium text-gray-500 bg-gray-100 dark:bg-[#333] px-3 py-1 rounded-full">
            {{ count($services) }} oferte
        </span>
    </div>

    {{-- GRID CARDURI VERTICALE (IMPECABLE DESIGN) --}}
    <div id="services-container" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6 pb-20 relative z-0">
        @forelse($services as $service)
            @php
                // --- Logică Date ---
                $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
                $loc = $service->locality->name ?? '';
                $jud = $service->county->name ?? '';
                $locationLabel = $loc ? "$loc, $jud" : $jud;

                $brandName = optional(optional(optional($service->generation)->model)->brand)->name ?: optional($service->brandRel)->name ?: ($service->brand ?? '');
                $modelName = optional(optional($service->generation)->model)->name ?: optional($service->modelRel)->name ?: ($service->model ?? '');
                $titluCalculat = trim("{$brandName} {$modelName}");
                $listingTitle = $service->title ?: $titluCalculat;

                // Formatare
                $an = $service->an_fabricatie ?? '-';
                $km = $service->km ? number_format($service->km, 0, ',', '.') : '-';
                $fuel = $service->combustibil->nume ?? '-';
                $power = $service->putere ? $service->putere . ' CP' : '-';
                $price = $service->price_value ? number_format($service->price_value, 0, ',', '.') : null;

                // Imagine principală
                $img = $service->main_image_url;
            @endphp

            {{-- CARD COMPONENT --}}
            <article class="group bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333] rounded-2xl overflow-hidden hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">
                
                {{-- 1. Imagine (Aspect 4:3) --}}
                <a href="{{ $service->public_url }}" class="block relative w-full aspect-[4/3] overflow-hidden bg-gray-200 dark:bg-[#121212]">
                    <img src="{{ $img }}" alt="{{ $listingTitle }}" 
                         class="w-full h-full object-cover transform group-hover:scale-110 transition-transform duration-700 ease-in-out" 
                         loading="lazy">
                    
                    {{-- Overlay Gradient --}}
                    <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-60"></div>

                    {{-- Preț (Pe imagine stânga jos) --}}
                    <div class="absolute bottom-3 left-3 flex flex-col items-start z-10">
                        @if($price)
                            <span class="text-xl font-black text-white drop-shadow-md">
                                {{ $price }} <span class="text-sm font-bold">{{ $service->currency }}</span>
                            </span>
                            @if($service->price_type === 'negotiable')
                                <span class="text-[9px] uppercase font-bold text-white/90 bg-black/30 px-1.5 py-0.5 rounded backdrop-blur-sm">Negociabil</span>
                            @endif
                        @else
                            <span class="text-lg font-bold text-white drop-shadow-md">La cerere</span>
                        @endif
                    </div>

                    {{-- Heart Button (Sus Dreapta) --}}
                    <button onclick="event.preventDefault(); toggleHeart(this, {{ $service->id }})" 
                        class="absolute top-2 right-2 p-2 rounded-full bg-black/20 hover:bg-white backdrop-blur-md transition-all duration-300 group/heart">
                        <svg class="w-5 h-5 transition-colors {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E]' : 'text-white fill-none group-hover/heart:text-[#CC2E2E]' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                </a>

                {{-- 2. Content --}}
                <div class="p-4 flex-1 flex flex-col">
                    {{-- Titlu --}}
                    <a href="{{ $service->public_url }}" class="block mb-3">
                        <h3 class="text-base font-bold text-gray-900 dark:text-white leading-tight line-clamp-1 group-hover:text-[#CC2E2E] transition-colors">
                            {{ $listingTitle }}
                        </h3>
                         <p class="text-xs text-gray-500 dark:text-gray-400 mt-0.5 truncate">{{ $power }} • {{ $service->capacitate_cilindrica ?? '-' }} cm³</p>
                    </a>

                    {{-- Specificații Grid (Pills) --}}
                    <div class="grid grid-cols-2 gap-2 mb-4">
                        <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-[#252525] px-2 py-1.5 rounded-lg border border-gray-100 dark:border-[#333]">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $an }}</span>
                        </div>
                         <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-[#252525] px-2 py-1.5 rounded-lg border border-gray-100 dark:border-[#333]">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $km }} km</span>
                        </div>
                         <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-[#252525] px-2 py-1.5 rounded-lg border border-gray-100 dark:border-[#333]">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $fuel }}</span>
                        </div>
                        <div class="flex items-center gap-1.5 bg-gray-50 dark:bg-[#252525] px-2 py-1.5 rounded-lg border border-gray-100 dark:border-[#333]">
                            <svg class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                            <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $service->transmission->nume ?? 'Auto' }}</span>
                        </div>
                    </div>

                    {{-- Footer: Locație --}}
                    <div class="mt-auto pt-3 border-t border-gray-100 dark:border-[#333] flex items-center text-xs text-gray-400">
                        <svg class="w-3.5 h-3.5 mr-1 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate">{{ $locationLabel }}</span>
                    </div>
                </div>
            </article>

        @empty
            <div class="col-span-full py-20 text-center">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#252525] mb-4">
                    <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-900 dark:text-white">Nu am găsit anunțuri</h3>
                <p class="text-gray-500 mt-2">Încearcă să resetezi filtrele.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- SCRIPTURI (Neschimbate funcțional, doar optimizate vizual) --}}
<script>
    const baseUrl = "{{ url('/') }}";
    const carData = @json($carData ?? []);
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const initialLocalityId = @json(optional($currentLocality)->id);
    const initialRadius = @json($currentRadius);

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

    function resetSelect(el, placeholder) {
        if (!el) return;
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled = true;
        el.classList.add('bg-gray-50', 'text-gray-400', 'cursor-not-allowed');
        el.classList.remove('bg-white', 'text-gray-900');
        el.value = "";
    }

    function enableSelect(el) {
        if (!el) return;
        el.disabled = false;
        el.classList.remove('bg-gray-50', 'text-gray-400', 'cursor-not-allowed');
        el.classList.add('bg-white', 'text-gray-900');
    }

    function resetLocalities() {
        if (!domElements.locality) return;
        domElements.locality.innerHTML = '<option value="">Alege</option>';
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
            if (String(selectedId) === String(locality.id)) option.selected = true;
            domElements.locality.appendChild(option);
        });
        domElements.locality.disabled = false;
    }

    async function loadLocalities(countyId, selectedId = null) {
        if (!countyId) { resetLocalities(); resetRadius(); return; }
        try {
            const response = await fetch(`${localityBaseUrl}/${countyId}`);
            const data = await response.json();
            populateLocalities(data, selectedId);
            if (domElements.radius && domElements.locality.value) domElements.radius.disabled = false;
        } catch (error) { console.error(error); resetLocalities(); resetRadius(); }
    }

    window.checkResetVisibility = function() {
        const btn = domElements.resetBtn;
        if (!btn) return;
        const filters = [domElements.brand, domElements.model, domElements.gen, domElements.body, domElements.fuel, domElements.gear, domElements.county, domElements.locality, domElements.radius];
        const hasAnyFilter = filters.some(el => el && el.value !== '');
        
        btn.disabled = !hasAnyFilter;
        if(hasAnyFilter) {
             btn.classList.remove('text-gray-300', 'bg-gray-50');
             btn.classList.add('text-[#CC2E2E]', 'border-[#CC2E2E]', 'bg-white');
        } else {
             btn.classList.add('text-gray-300', 'bg-gray-50');
             btn.classList.remove('text-[#CC2E2E]', 'border-[#CC2E2E]', 'bg-white');
        }
    };

    window.resetFilters = function() {
        if (domElements.brand) domElements.brand.value = '';
        resetSelect(domElements.model, 'Alege model');
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
        if (brandSlug) path += `/${brandSlug}`;
        if (brandSlug && modelSlug) path += `/${modelSlug}`;
        if (brandSlug && modelSlug && countySlug) path += `/${countySlug}`;

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
        [...params.keys()].forEach(key => !params.get(key) && params.delete(key));
        const queryString = params.toString();
        return `${baseUrl}${path}${queryString ? `?${queryString}` : ''}`;
    }

    document.addEventListener('DOMContentLoaded', () => {
        window.checkResetVisibility();
        const sellerTabs = document.querySelectorAll('.seller-tab');
        const sellerInput = domElements.sellerType;

        const setActiveSellerTab = (val) => {
            sellerTabs.forEach(tab => {
                const isActive = tab.dataset.seller === val;
                tab.classList.toggle('bg-white', isActive);
                tab.classList.toggle('text-black', isActive);
                tab.classList.toggle('shadow-sm', isActive);
                tab.classList.toggle('text-gray-500', !isActive);
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

        if (domElements.county) domElements.county.addEventListener('change', () => { loadLocalities(domElements.county.value); window.checkResetVisibility(); });
        if (domElements.locality) domElements.locality.addEventListener('change', () => { if(domElements.radius) domElements.radius.disabled = !domElements.locality.value; window.checkResetVisibility(); });
        if (domElements.radius) domElements.radius.addEventListener('change', () => window.checkResetVisibility());
        const searchForm = document.getElementById('search-form');
        if (searchForm) searchForm.addEventListener('submit', (e) => { e.preventDefault(); window.location.href = buildSearchUrl(); });

        // Dependent Dropdowns Logic
        if (domElements.brand && domElements.brand.value) {
            const brandId = domElements.brand.value;
            resetSelect(domElements.model, 'Alege model');
            resetSelect(domElements.gen, 'Generație');
            if (carData[brandId]) {
                enableSelect(domElements.model);
                carData[brandId].forEach(m => domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}">${m.name}</option>`);
            }
        }

        if (domElements.brand) {
            domElements.brand.addEventListener('change', function() {
                const brandId = this.value;
                resetSelect(domElements.model, 'Alege model');
                resetSelect(domElements.gen, 'Generație');
                if (brandId && carData[brandId]) {
                    enableSelect(domElements.model);
                    carData[brandId].forEach(m => domElements.model.innerHTML += `<option value="${m.id}" data-slug="${m.slug}">${m.name}</option>`);
                }
                window.checkResetVisibility();
            });
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
                window.checkResetVisibility();
            });
        }
        
        [domElements.gen, domElements.body, domElements.fuel, domElements.gear].forEach(el => el && el.addEventListener('change', window.checkResetVisibility));

        if (domElements.county && domElements.county.value) {
            loadLocalities(domElements.county.value, initialLocalityId).then(() => {
                if (domElements.radius) { domElements.radius.value = initialRadius || ''; domElements.radius.disabled = !domElements.locality?.value; }
            });
        } else { resetLocalities(); resetRadius(); }
    });

    window.toggleHeart = function(btn, serviceId) {
        @if(!auth()->check())
            window.location.href = "{{ route('login') }}"; return;
        @endif
        const icon = btn.querySelector('svg');
        const isLiked = icon.classList.contains('text-[#CC2E2E]');
        if (isLiked) {
            icon.classList.remove('text-[#CC2E2E]', 'fill-[#CC2E2E]');
            icon.classList.add('text-white', 'fill-none');
        } else {
            icon.classList.remove('text-white', 'fill-none');
            icon.classList.add('text-[#CC2E2E]', 'fill-[#CC2E2E]');
        }
        fetch("{{ route('favorite.toggle') }}", {
            method: "POST", headers: { "Content-Type": "application/json", "X-CSRF-TOKEN": "{{ csrf_token() }}" },
            body: JSON.stringify({ service_id: serviceId })
        }).catch(err => console.error(err));
    }
</script>

<style>
    .autovit-select {
        display: block; width: 100%; height: 48px; padding: 0 2rem 0 1rem;
        font-size: 0.85rem; font-weight: 600; color: #1f2937;
        background-color: #ffffff;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%236b7280' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e");
        background-position: right 0.75rem center; background-repeat: no-repeat; background-size: 1.25em 1.25em;
        border: 1px solid #e5e7eb; border-radius: 0.75rem; appearance: none; transition: all 0.2s ease;
        text-overflow: ellipsis; white-space: nowrap; overflow: hidden;
    }
    .autovit-select:focus { outline: none; border-color: #CC2E2E; box-shadow: 0 0 0 3px rgba(204, 46, 46, 0.1); }
    .dark .autovit-select { background-color: #2d2d2d; border-color: #404040; color: #e5e7eb; }
    .dark .autovit-select:disabled { background-color: #1a1a1a; color: #555; }
    optgroup { font-weight: 700; color: #CC2E2E; background-color: #f9fafb; }
</style>
@endsection