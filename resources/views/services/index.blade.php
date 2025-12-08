@extends('layouts.app')

@section('title', 'MeseriasBun.ro – Găsește Meseriași, Constructori și Instalatori')
@section('meta_description', 'Ai nevoie de un profesionist? Găsește rapid oferte pentru construcții, renovări și instalații sau publică anunț gratuit pe MeseriasBun.ro.')
@section('meta_image', asset('images/social-share.webp'))

{{-- SECȚIUNEA HERO --}}
@section('hero')
<div class="relative w-full bg-gray-900 group">
    
    {{-- A. IMAGINE + OVERLAY --}}
    <div class="absolute inset-0 h-[450px] md:h-[480px] w-full overflow-hidden z-0">
        {{-- Imagine Desktop --}}
        <img src="{{ asset('images/hero-desktop.webp') }}" alt="Meserias Bun Fundal" 
             class="hidden md:block w-full h-full object-cover object-center transform transition duration-1000 group-hover:scale-105">
        
        {{-- Imagine Mobile --}}
        <img src="{{ asset('images/hero-mobile.webp') }}" alt="Meserias Bun Fundal" 
             class="block md:hidden w-full h-full object-cover object-center">
             
        {{-- 
            OVERLAY SIMPLU (FĂRĂ GRADIENT COMPLEX):
            Un strat negru uniform cu 50% opacitate. 
            Este cel mai sigur pentru lizibilitate pe orice dispozitiv.
        --}}
        <div class="absolute inset-0 bg-black/50"></div>
    </div>

    {{-- B. CONȚINUT TEXT --}}
    {{-- 
        MODIFICARE CRITICĂ MOBIL:
        h-auto (pe mobil): Containerul are înălțimea strict cât textul.
        md:h-[480px] (pe desktop): Are înălțime fixă pentru centrare.
        
        pt-24 (pe mobil): Împinge textul puțin sub header.
        md:justify-center: Centrează vertical doar pe desktop.
    --}}
    <div class="relative z-10 max-w-7xl mx-auto px-4 h-auto md:h-[480px] flex flex-col justify-start md:justify-center items-start pt-24 pb-4 md:pt-16 md:pb-8">
        
        <h1 class="text-white font-extrabold tracking-tight drop-shadow-xl text-left mb-2 md:mb-4 max-w-2xl animate-in slide-in-from-left-4 duration-700">
            <span class="block text-3xl md:text-4xl lg:text-5xl mb-1 md:mb-2">GĂSEȘTI MEȘTERI</span>
            
            {{-- TEXT ALB CURAT (FĂRĂ GRADIENT) --}}
            <span class="block text-3xl md:text-4xl lg:text-5xl text-white font-black">
                VERIFICAȚI, RAPID!
            </span>
        </h1>

        <p class="text-gray-200 text-sm md:text-lg max-w-xl font-medium mb-4 md:mb-8 leading-relaxed shadow-black drop-shadow-md hidden md:block">
            Cea mai simplă metodă să găsești profesioniști pentru proiectul tău, oriunde în România.
        </p>

    </div>

    {{-- C. BARA DE CĂUTARE --}}
    {{-- 
        MODIFICARE CRITICĂ MOBIL:
        mt-4 (pe mobil): O margine mică pozitivă. Deoarece textul de sus e h-auto, bara vine imediat sub el.
        md:-mt-32 (pe desktop): Margine negativă mare ca să urce peste textul centrat.
    --}}
    <div class="relative z-30 max-w-7xl mx-auto px-4 mt-4 md:-mt-32 pb-4">
        
        <form id="search-form" onsubmit="event.preventDefault(); loadServices(1);"
            class="relative w-full transition-all duration-300 ease-out grid grid-cols-1 md:grid-cols-12 gap-3 md:gap-4 bg-transparent p-0">

            {{-- 1. SEARCH INPUT --}}
            <div class="col-span-1 md:col-span-4 relative group">
                <div class="flex items-center h-[3.25rem] w-full rounded-xl px-4 transition-all duration-200 cursor-text
                              bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333333] shadow-lg shadow-gray-200/20
                              hover:bg-gray-50 dark:hover:bg-[#252525]
                              focus-within:bg-white dark:focus-within:bg-[#252525] 
                              focus-within:ring-2 focus-within:ring-[#CC2E2E]/20 focus-within:border-[#CC2E2E]"
                     onclick="this.querySelector('input').focus()">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-[#CC2E2E] opacity-80 group-focus-within:opacity-100 transition-opacity mr-3 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <div class="flex-1 min-w-0">
                        <input type="text" name="search" id="search-input" placeholder="Ce cauți? (ex: Instalator...)" value="{{ request('search') }}"
                            class="w-full bg-transparent border-none p-0 focus:ring-0 text-base truncate leading-normal font-medium text-gray-900 dark:text-white placeholder-gray-400"
                            oninput="checkResetVisibility(); debounceLoad()">
                    </div>
                </div>
            </div>

            {{-- 2. CATEGORY DROPDOWN --}}
            <div id="cat-col" class="col-span-1 md:col-span-3 relative group">
                <input type="hidden" name="category" id="category-input" value="{{ request('category') }}">
                <button type="button" onclick="toggleCategoryDropdown()" 
                    class="flex items-center justify-between h-[3.25rem] w-full rounded-xl px-4 transition-all duration-200 outline-none text-left whitespace-nowrap
                           bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333333] shadow-lg shadow-gray-200/20
                           hover:bg-gray-50 dark:hover:bg-[#252525]
                           group-focus:ring-2 group-focus:ring-[#CC2E2E]/20">
                    <span id="category-display" class="font-medium text-gray-700 dark:text-gray-200 truncate mr-2">
                        {{ $categories->firstWhere('id', request('category'))->name ?? 'Toate categoriile' }}
                    </span>
                    <svg id="category-arrow" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transform transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="category-list" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#252525] rounded-xl shadow-2xl z-[999] overflow-hidden origin-top animate-in fade-in zoom-in-95 duration-100 border border-gray-100 dark:border-[#404040] max-h-80 overflow-y-auto custom-scrollbar">
                    <div class="p-1.5">
                        <div onclick="selectCategory('', 'Toate categoriile')" class="px-3 py-2.5 rounded-lg cursor-pointer text-base font-medium transition-all select-none mb-1 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]">Toate categoriile</div>
                        @foreach($categories as $category)
                        <div onclick="selectCategory('{{ $category->id }}', '{{ $category->name }}')" class="px-3 py-2.5 rounded-lg cursor-pointer text-base font-medium transition-all select-none {{ request('category') == $category->id ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">{{ $category->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 3. COUNTY DROPDOWN --}}
            <div id="county-col" class="col-span-1 md:col-span-3 relative group">
                <input type="hidden" name="county" id="county-input" value="{{ request('county') }}">
                <button type="button" onclick="toggleCountyDropdown()" 
                    class="flex items-center justify-between h-[3.25rem] w-full rounded-xl px-4 transition-all duration-200 outline-none text-left whitespace-nowrap
                           bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333333] shadow-lg shadow-gray-200/20
                           hover:bg-gray-50 dark:hover:bg-[#252525]">
                    <span id="county-display" class="font-medium text-gray-700 dark:text-gray-200 truncate mr-2">
                        {{ $counties->firstWhere('id', request('county'))->name ?? 'Toate județele' }}
                    </span>
                    <svg id="county-arrow" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400 transform transition-transform duration-200 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                    </svg>
                </button>
                <div id="county-list" class="hidden absolute top-full left-0 right-0 mt-2 bg-white dark:bg-[#252525] rounded-xl shadow-2xl z-[999] overflow-hidden origin-top animate-in fade-in zoom-in-95 duration-100 border border-gray-100 dark:border-[#404040] max-h-80 overflow-y-auto custom-scrollbar">
                    <div class="p-1.5">
                        <div onclick="selectCounty('', 'Toate județele')" class="px-3 py-2.5 rounded-lg cursor-pointer transition-all text-base font-medium select-none mb-1 text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]">Toate județele</div>
                        @foreach($counties as $county)
                            <div onclick="selectCounty('{{ $county->id }}', '{{ $county->name }}')" class="px-3 py-2.5 rounded-lg cursor-pointer transition-all text-base font-medium select-none {{ request('county') == $county->id ? 'bg-red-50 dark:bg-red-900/20 text-[#CC2E2E]' : 'text-gray-700 dark:text-gray-300 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-[#CC2E2E]' }}">{{ $county->name }}</div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- 4. ACTIONS --}}
            <div id="actions-col" class="col-span-1 md:col-span-2 flex gap-2 h-[3.25rem] overflow-visible">
                <button type="button" id="reset-btn" onclick="resetFilters()"
                   class="hidden flex-1 h-full items-center justify-center gap-2 rounded-xl transition-all duration-300 group animate-in slide-in-from-right-4 fade-in whitespace-nowrap
                          bg-white text-[#CC2E2E] border border-red-100 shadow-lg shadow-red-100/50 hover:bg-red-50 font-bold text-sm
                          dark:bg-red-900/20 dark:text-red-400 dark:border-red-900/30 dark:hover:bg-red-900/40 dark:shadow-none">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 transition-transform group-hover:rotate-90 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                    <span>Șterge</span>
                </button>

                <button type="submit" class="flex-1 h-full bg-[#CC2E2E] text-white font-bold rounded-xl shadow-lg shadow-red-600/30 hover:bg-[#B72626] transition-all flex items-center gap-2 justify-center text-lg tracking-wide hover:-translate-y-0.5 active:translate-y-0 min-w-[120px]">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                    <span>Caută</span>
                </button>
            </div>

        </form>
    </div>
</div>
@endsection

@section('content')

{{-- TITLU - SPAȚIERE MOBIL --}}
{{-- 
    mt-8 pe mobil: Deoarece bara de căutare nu mai are margine negativă, 
    ea ocupă spațiu fizic. Nu avem nevoie de margine mare aici.
--}}
<div class="mt-8 md:mt-12 mb-8 flex items-center gap-3 max-w-7xl mx-auto px-4">
    <span class="w-1.5 h-8 bg-[#CC2E2E] rounded-full shadow-sm"></span>      
    <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-[#F2F2F2]">
        Anunțuri recente
    </h2>
</div>

{{-- CONTAINER CARDURI --}}
<div id="services-container" class="grid grid-cols-2 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 md:gap-6 pb-10 relative z-0 max-w-7xl mx-auto px-4">
    @include('services.partials.service_cards', ['services' => $services])
</div>

{{-- LOADING & SCRIPTS (IDENTICE CU CELE ANTERIOARE) --}}
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

    document.addEventListener('DOMContentLoaded', () => {
        checkResetVisibility();
    });

    function checkResetVisibility() {
        const s = document.getElementById('search-input').value;
        const c = document.getElementById('category-input').value;
        const j = document.getElementById('county-input').value;
        const btn = document.getElementById('reset-btn');
        const catCol = document.getElementById('cat-col');
        const countyCol = document.getElementById('county-col');
        const actionsCol = document.getElementById('actions-col');

        if (s || c || j) {
            btn.classList.remove('hidden');
            btn.classList.add('flex');
            catCol.classList.remove('md:col-span-3'); catCol.classList.add('md:col-span-2');
            countyCol.classList.remove('md:col-span-3'); countyCol.classList.add('md:col-span-2');
            actionsCol.classList.remove('md:col-span-2'); actionsCol.classList.add('md:col-span-4');
        } else {
            btn.classList.add('hidden');
            btn.classList.remove('flex');
            catCol.classList.remove('md:col-span-2'); catCol.classList.add('md:col-span-3');
            countyCol.classList.remove('md:col-span-2'); countyCol.classList.add('md:col-span-3');
            actionsCol.classList.remove('md:col-span-4'); actionsCol.classList.add('md:col-span-2');
        }
    }

    function resetFilters() {
        document.getElementById('search-input').value = '';
        document.getElementById('category-input').value = '';
        document.getElementById('county-input').value = '';
        document.getElementById('category-display').innerText = 'Toate categoriile';
        document.getElementById('county-display').innerText = 'Toate județele';
        document.getElementById('category-list').classList.add('hidden');
        document.getElementById('county-list').classList.add('hidden');
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
            search: document.getElementById('search-input').value,
            category: document.getElementById('category-input').value,
            county: document.getElementById('county-input').value,
            page: page,
            ajax: 1
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
                            <button type="button" onclick="resetFilters()" class="px-8 py-3.5 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold rounded-xl shadow-lg">Resetează Filtrele</button>
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
        debounceTimer = setTimeout(() => loadServices(1), 500);
    }

    const observer = new IntersectionObserver((entries) => {
        if (entries[0].isIntersecting && !isLoading && hasMore) {
            loadServices(currentPage);
        }
    }, { rootMargin: '0px 0px 400px 0px' });

    document.addEventListener('DOMContentLoaded', () => {
        observer.observe(document.getElementById('load-more-trigger'));
    });

    function selectCounty(id, name) {
        document.getElementById('county-input').value = id;
        document.getElementById('county-display').innerText = name;
        toggleCountyDropdown(); 
        checkResetVisibility();
        loadServices(1);
    }

    function selectCategory(id, name) {
        document.getElementById('category-input').value = id;
        document.getElementById('category-display').innerText = name;
        toggleCategoryDropdown(); 
        checkResetVisibility();
        loadServices(1);
    }
    
    function toggleCountyDropdown() { 
        const list = document.getElementById('county-list');
        const arrow = document.getElementById('county-arrow');
        document.getElementById('category-list').classList.add('hidden');
        list.classList.toggle('hidden');
        arrow.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }
    
    function toggleCategoryDropdown() { 
        const list = document.getElementById('category-list');
        const arrow = document.getElementById('category-arrow');
        document.getElementById('county-list').classList.add('hidden');
        list.classList.toggle('hidden');
        arrow.style.transform = list.classList.contains('hidden') ? 'rotate(0deg)' : 'rotate(180deg)';
    }

    document.addEventListener('click', function(event) {
        const catGroup = document.getElementById('cat-col');
        const countyGroup = document.getElementById('county-col');
        
        if (catGroup && !catGroup.contains(event.target)) {
            document.getElementById('category-list').classList.add('hidden');
            const arrow = document.getElementById('category-arrow');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
        if (countyGroup && !countyGroup.contains(event.target)) {
            document.getElementById('county-list').classList.add('hidden');
             const arrow = document.getElementById('county-arrow');
            if(arrow) arrow.style.transform = 'rotate(0deg)';
        }
    });

    function toggleHeart(btn, serviceId) {
         @if(!auth()->check())
            window.location.href = "{{ route('login') }}"; return;
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
    .custom-scrollbar::-webkit-scrollbar { width: 6px; }
    .custom-scrollbar::-webkit-scrollbar-thumb { background-color: #e5e7eb; border-radius: 20px; }
</style>

@endsection