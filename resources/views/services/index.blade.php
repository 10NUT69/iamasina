@extends('layouts.app')

@section('title', 'Anunțuri auto – autoturisme și servicii auto')
@section('meta_description', 'Caută autoturisme după marcă, model, generație, caroserie, combustibil, cutie de viteze și locație. Publică sau găsește rapid anunțuri auto.')
@section('meta_image', asset('images/social-share.webp'))

{{-- ======================= HERO + FILTRE ======================= --}}
@section('hero')
<div class="relative w-full bg-gray-900 group">
    {{-- FUNDAL --}}
    <div class="absolute inset-0 h-[260px] md:h-[300px] w-full overflow-hidden z-0">
        <img src="{{ asset('images/hero-desktop.webp') }}" alt="Fundal auto" 
             class="hidden md:block w-full h-full object-cover object-center">
        <img src="{{ asset('images/hero-mobile.webp') }}" alt="Fundal auto" 
             class="block md:hidden w-full h-full object-cover object-center">
        <div class="absolute inset-0 bg-black/55"></div>
    </div>

    <div class="relative z-10 max-w-6xl mx-auto px-4 pt-8 pb-4">
        {{-- TITLU --}}
        <div class="mb-3 md:mb-4">
            <h1 class="text-white font-extrabold tracking-tight text-xl md:text-2xl lg:text-3xl">
                Găsește rapid <span class="text-red-300">autoturisme</span> după filtre precise
            </h1>
            <p class="text-gray-200 text-xs md:text-sm mt-1">
                Alege marca, modelul, generația, caroseria, combustibilul, cutia și locația.
            </p>
        </div>

        {{-- CARD CU TABURI + FILTRE --}}
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-lg border border-gray-100 dark:border-[#333]
            px-3 py-3 md:px-4 md:py-4 w-full md:w-[1000px]">

            
            {{-- TABURI SIMPLE --}}
            <div class="flex gap-4 pb-3 border-b border-gray-200 dark:border-[#333] text-xs md:text-sm font-semibold">
                <button type="button"
                    class="flex items-center gap-1 text-[#CC2E2E] border-b-2 border-[#CC2E2E] pb-2">
                    <span>🚗</span>
                    <span>Autoturisme</span>
                </button>
                <button type="button"
                    class="hidden md:flex items-center gap-1 text-gray-400 pb-2 cursor-not-allowed">
                    <span>⚙️</span>
                    <span>Piese</span>
                </button>
                <button type="button"
                    class="hidden md:flex items-center gap-1 text-gray-400 pb-2 cursor-not-allowed">
                    <span>🚚</span>
                    <span>Camioane</span>
                </button>
            </div>

            {{-- FILTRE --}}
            <form id="search-form" onsubmit="event.preventDefault(); loadServices(1);" class="mt-4">
                <input type="hidden" name="vehicle_type" id="vehicle-type" value="autoturisme">

                {{-- 2 rânduri × 4 coloane pe desktop, 2 coloane pe mobil --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-x-2 md:gap-x-3 gap-y-2 items-end text-xs">

                    {{-- RÂND 1: MARCĂ, MODEL, GENERAȚIE, CAROSERIE --}}
                    {{-- MARCĂ --}}
<div>
    <label class="filter-label block">Marcă</label>
    <select id="brand-filter" name="brand"
            class="filter-field w-full md:w-44">
        <option value="">Alege marcă</option>
        @foreach($brands as $brand)
            <option value="{{ $brand->name }}">{{ $brand->name }}</option>
        @endforeach
    </select>
</div>

{{-- MODEL --}}
<div>
    <label class="filter-label block">Model</label>
    <select id="model-filter" name="model"
            class="filter-field w-full md:w-44 bg-gray-100 text-gray-400" disabled>
        <option value="">Alege model</option>
    </select>
</div>


                    <div>
                        <label class="filter-label">Generație</label>
                        <select id="generation-filter" name="car_generation_id"
                            class="filter-field w-full md:w-52 bg-gray-100 text-gray-400" disabled>
                            <option value="">Generație</option>
                        </select>
                    </div>

                    <div>
                        <label class="filter-label">Caroserie</label>
                        <select id="body-filter" name="caroserie_id" class="filter-field w-full md:w-44">
                            <option value="">Caroserie</option>
                            @foreach($bodies as $body)
                                <option value="{{ $body->id }}">{{ $body->nume }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- RÂND 2: COMBUSTIBIL, CUTIE VITEZE, LOCAȚIE, BUTOANE --}}
                    <div>
                        <label class="filter-label">Combustibil</label>
                        <select id="fuel-filter" name="combustibil_id" class="filter-field w-full md:w-44">
                            <option value="">Combustibil</option>
                            @foreach($fuels as $fuel)
                                <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="filter-label">Cutie viteze</label>
                        <select id="gearbox-filter" name="cutie_viteze_id" class="filter-field w-full md:w-40">
                            <option value="">Cutie viteze</option>
                            @foreach($transmissions as $trans)
                                <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="filter-label">Locație</label>
                        <select id="county-input" name="county" class="filter-field w-full md:w-52">
                            <option value="">Toate județele</option>
                            @foreach($counties as $county)
                                <option value="{{ $county->id }}">{{ $county->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex gap-2 justify-end col-span-2 md:col-span-1">
                        <button type="button" id="reset-btn" onclick="resetFilters()" class="reset-btn flex-1 md:flex-none hidden">
                            ✖ Resetare
                        </button>
                        <button type="submit" class="search-btn flex-1 md:flex-none">
                            🔍 Caută
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

{{-- ======================= CONȚINUT LISTĂ ANUNȚURI ======================= --}}
@section('content')

<div class="mt-8 md:mt-12 mb-8 flex items-center gap-3 max-w-7xl mx-auto px-4">
    <span class="w-1.5 h-8 bg-[#CC2E2E] rounded-full shadow-sm"></span>      
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-[#F2F2F2]">
        Anunțuri recente
    </h2>
</div>

<div id="services-container" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 pb-10 relative z-0 max-w-7xl mx-auto px-4">
    @include('services.partials.service_cards', ['services' => $services])
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
    let isLoading = false;
    let currentPage = 2; 
    let hasMore = document.getElementById('load-more-trigger').dataset.hasMore === 'true';
    let debounceTimer;

    // ====== CAR DATA (brand -> model -> generații) ======
    const carData = @json($carData ?? []);
    const brandFilter = document.getElementById('brand-filter');
    const modelFilter = document.getElementById('model-filter');
    const generationFilter = document.getElementById('generation-filter');

    function resetSelect(el, placeholder) {
        if (!el) return;
        el.innerHTML = `<option value="">${placeholder}</option>`;
        el.disabled = true;
        el.classList.add('bg-gray-100', 'text-gray-400');
    }

    function enableSelect(el) {
        if (!el) return;
        el.disabled = false;
        el.classList.remove('bg-gray-100', 'text-gray-400');
    }

    document.addEventListener('DOMContentLoaded', () => {
        checkResetVisibility();

        // Brand -> Model
        if (brandFilter) {
            brandFilter.addEventListener('change', function () {
                const brand = this.value;
                resetSelect(modelFilter, 'Model');
                resetSelect(generationFilter, 'Generație');

                if (brand && carData[brand]) {
                    enableSelect(modelFilter);
                    Object.keys(carData[brand]).forEach(modelName => {
                        const opt = document.createElement('option');
                        opt.value = modelName;
                        opt.textContent = modelName;
                        modelFilter.appendChild(opt);
                    });
                }
                debounceLoad();
            });
        }

        // Model -> Generație
        if (modelFilter) {
            modelFilter.addEventListener('change', function () {
                const brand = brandFilter.value;
                const model = this.value;
                resetSelect(generationFilter, 'Generație');

                if (brand && model && carData[brand] && carData[brand][model]) {
                    const generations = carData[brand][model];
                    if (generations.length) {
                        enableSelect(generationFilter);
                        generations.forEach(g => {
                            const opt = document.createElement('option');
                            opt.value = g.id;  // id din DB
                            opt.textContent = `${g.name} (${g.start} - ${g.end || 'Prezent'})`;
                            generationFilter.appendChild(opt);
                        });
                    }
                }
                debounceLoad();
            });
        }

        if (generationFilter) {
            generationFilter.addEventListener('change', () => debounceLoad());
        }

        ['body-filter','fuel-filter','gearbox-filter','county-input'].forEach(id => {
            const el = document.getElementById(id);
            if (el) el.addEventListener('change', () => debounceLoad());
        });

        observer.observe(document.getElementById('load-more-trigger'));
    });

    function checkResetVisibility() {
        const filters = {
            brand: document.getElementById('brand-filter')?.value,
            model: document.getElementById('model-filter')?.value,
            gen: document.getElementById('generation-filter')?.value,
            body: document.getElementById('body-filter')?.value,
            fuel: document.getElementById('fuel-filter')?.value,
            gear: document.getElementById('gearbox-filter')?.value,
            county: document.getElementById('county-input')?.value,
        };

        const btn = document.getElementById('reset-btn');
        if (!btn) return;

        const hasAny = Object.values(filters).some(v => v && v !== '');

        if (hasAny) {
            btn.classList.remove('hidden');
        } else {
            btn.classList.add('hidden');
        }
    }

    function resetFilters() {
        if (brandFilter) brandFilter.value = '';
        if (modelFilter) resetSelect(modelFilter, 'Model');
        if (generationFilter) resetSelect(generationFilter, 'Generație');
        const body = document.getElementById('body-filter');
        const fuel = document.getElementById('fuel-filter');
        const gear = document.getElementById('gearbox-filter');
        const county = document.getElementById('county-input');

        if (body) body.value = '';
        if (fuel) fuel.value = '';
        if (gear) gear.value = '';
        if (county) county.value = '';

        checkResetVisibility();
        loadServices(1);
    }

    function loadServices(page) {
        const isNewFilter = page === 1;
        if (isLoading) return;
        if (!hasMore && !isNewFilter) return;

        if (isNewFilter) {
            currentPage = 2;
            hasMore = true;
            document.getElementById('services-container').style.opacity = '0.5'; 
            document.getElementById('load-more-trigger').dataset.hasMore = 'true';
            checkResetVisibility();
        } else {
            document.getElementById('loading-indicator').classList.remove('hidden');
        }

        isLoading = true;

        const params = new URLSearchParams({
            page: page,
            ajax: 1,
            vehicle_type: document.getElementById('vehicle-type')?.value || '',
            brand: document.getElementById('brand-filter')?.value || '',
            model: document.getElementById('model-filter')?.value || '',
            car_generation_id: document.getElementById('generation-filter')?.value || '',
            caroserie_id: document.getElementById('body-filter')?.value || '',
            combustibil_id: document.getElementById('fuel-filter')?.value || '',
            cutie_viteze_id: document.getElementById('gearbox-filter')?.value || '',
            county: document.getElementById('county-input')?.value || '',
        });

        fetch(`{{ route('services.index') }}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(res => res.json())
        .then(data => {
            if (isNewFilter) {
                document.getElementById('services-container').innerHTML = data.html;
                document.getElementById('services-container').style.opacity = '1';
                if (data.loadedCount === 0) {
                     document.getElementById('services-container').innerHTML = `
                        <div class="col-span-full flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-[#1E1E1E] rounded-3xl border-2 border-dashed border-gray-200 dark:border-[#333333]">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Nu am găsit anunțuri</h3>
                            <button type="button" onclick="resetFilters()" class="px-8 py-3.5 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold rounded-xl shadow-lg">Resetează filtrele</button>
                        </div>
                    `;
                }
            } else {
                document.getElementById('services-container').insertAdjacentHTML('beforeend', data.html);
            }
            hasMore = data.hasMore;
            document.getElementById('load-more-trigger').dataset.hasMore = hasMore;
            if (hasMore) currentPage++;
            if (hasMore) {
                observer.unobserve(document.getElementById('load-more-trigger'));
                observer.observe(document.getElementById('load-more-trigger'));
            }
        })
        .finally(() => {
            isLoading = false;
            document.getElementById('loading-indicator').classList.add('hidden');
        });
    }

    function debounceLoad() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(() => loadServices(1), 400);
    }

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            loadServices(currentPage);
        }
    }, { rootMargin: '0px 0px 400px 0px' });

    function toggleHeart(btn, serviceId) {
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
    .filter-label {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        font-weight: 600;
        color: #6b7280;
        margin-bottom: 0.15rem;
    }
    .filter-field {
        height: 2.4rem;
        font-size: 0.8rem;
        padding-left: 0.5rem;
        padding-right: 1.8rem;
        border-radius: 0.6rem;
        border: 1px solid #e5e7eb;
        background-color: #ffffff;
    }
    .dark .filter-field {
        background-color: #1E1E1E;
        border-color: #333333;
        color: #f9fafb;
    }
    .reset-btn {
        background: #ffffff;
        border: 1px solid #e5e7eb;
        padding: 0 0.9rem;
        height: 2.4rem;
        border-radius: 0.6rem;
        font-size: 0.78rem;
        font-weight: 600;
        color: #6b7280;
        white-space: nowrap;
    }
    .dark .reset-btn {
        background-color: #1E1E1E;
        border-color: #444444;
        color: #e5e7eb;
    }
    .search-btn {
        background: #CC2E2E;
        color: #ffffff;
        border: none;
        padding: 0 1.1rem;
        height: 2.4rem;
        border-radius: 0.6rem;
        font-size: 0.82rem;
        font-weight: 700;
        white-space: nowrap;
    }
    .search-btn:hover {
        background: #b72626;
    }
</style>

@endsection
