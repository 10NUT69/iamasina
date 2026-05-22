@forelse($services as $service)
    @php
        // 1. Logica Favorite
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());

        // 2. Logica Locație
        $loc = $service->locality->name ?? '';
        $jud = $service->county->name ?? '';
        $locationLabel = $loc ? "$loc, $jud" : $jud;

        // 3. Construcție Titlu (Brand + Model dacă nu există titlu custom)
        $brandName = optional($service->brandRel)->name
            ?: ($service->brand ?? 'Marca');
        $modelName = optional($service->modelRel)->name
            ?: ($service->model ?? 'Model');
        
        $titluCalculat = trim("{$brandName} {$modelName}");
        $listingTitle = $service->title ?: $titluCalculat;
        
        // Badge-uri (ex: Promovat)
        $isPromoted = $service->promoted ?? false; 

        // 4. Formatare Valori Tehnice
        $an = $service->an_fabricatie ?? '-';
        $km = $service->km ? number_format($service->km, 0, ',', '.') : null;
        $kmLabel = $km ? $km . ' km' : '-';
        $fuel = $service->combustibil->nume ?? '-';
        $gearbox = $service->cutieViteze->nume ?? '-';
        $engineSize = $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, ',', '.') : null;
        $power = $service->putere ?? null;
        $norma = $service->normaPoluare->nume ?? null;

        // 5. Procesare Imagini
        $imagesList = $service->images ?? [];
        if (is_string($imagesList)) $imagesList = json_decode($imagesList, true) ?: [];
        if (is_object($imagesList) && method_exists($imagesList, 'all')) $imagesList = $imagesList->all();
        $imagesList = is_array($imagesList) ? $imagesList : [];
        $imgCount = count($imagesList);
        $sliderImages = $imagesList;
        if ($imgCount === 0 && $service->main_image_url) {
            $sliderImages = [$service->main_image_url];
        }
        $slideCount = count($sliderImages);

        // 6. Data afisata ramane data publicarii/reactualizarii, nu data editarii.
        $dateLabel = $service->listing_date_label;
    @endphp

    {{-- CARD ANUNȚ (DESIGN 2025-2026) --}}
    <article class="group relative flex flex-col md:flex-row bg-white dark:bg-[#18181b] rounded-xl border border-gray-200 dark:border-[#27272a] shadow-sm hover:shadow-xl hover:border-[#C81424]/30 transition-all duration-300 overflow-hidden mb-5">
        
        {{-- A. ZONA FOTO (Slider + Badges) --}}
        <div class="relative w-full md:w-[320px] lg:w-[340px] shrink-0 aspect-[4/3] md:aspect-auto md:min-h-[240px] overflow-hidden bg-gray-100 dark:bg-[#09090b]"
             x-data="{ 
                activeSlide: 0, 
                slides: {{ $slideCount > 0 ? $slideCount : 1 }},
                next() { this.activeSlide = (this.activeSlide === this.slides - 1) ? 0 : this.activeSlide + 1 },
                prev() { this.activeSlide = (this.activeSlide === 0) ? this.slides - 1 : this.activeSlide - 1 },
                touchStartX: null,
                onTouchStart(event) { this.touchStartX = event.changedTouches[0].clientX },
                onTouchEnd(event) {
                    if (this.touchStartX === null || this.slides < 2) return;

                    const diff = this.touchStartX - event.changedTouches[0].clientX;

                    if (Math.abs(diff) > 40) {
                        diff > 0 ? this.next() : this.prev();
                    }

                    this.touchStartX = null;
                }
             }"
             @touchstart.passive="onTouchStart($event)"
             @touchend.passive="onTouchEnd($event)">

            {{-- Link principal pe imagine --}}
            <a href="{{ $service->public_url }}" class="block w-full h-full relative group/img">
                @if($slideCount > 0)
                    @foreach($sliderImages as $index => $img)
                        @php
                            $imageUrl = $service->imageCardUrl($img);
                        @endphp
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" 
                                 x-show="activeSlide === {{ $index }}"
                                 x-transition:enter="transition transform duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 scale-105"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0 w-full h-full object-cover transition-transform duration-700 group-hover/img:scale-105"
                                 alt="{{ $listingTitle }}" loading="lazy">
                        @endif
                    @endforeach
                @else
                    {{-- Placeholder --}}
                    <div class="absolute inset-0 flex items-center justify-center text-gray-400 bg-gray-100 dark:bg-[#121212]">
                        <svg class="w-12 h-12 opacity-20" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                @endif
                
                {{-- Gradient Overlay (pentru contrast text jos) --}}
                <div class="absolute inset-x-0 bottom-0 h-20 bg-gradient-to-t from-black/60 via-black/20 to-transparent opacity-60"></div>
            </a>

            {{-- Navigare Slider (Doar la Hover) --}}
            @if($imgCount > 1)
                <div class="absolute inset-y-0 left-0 flex items-center pl-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                    <button @click.prevent="prev()" class="pointer-events-auto p-1.5 rounded-full bg-white/90 text-black hover:bg-white shadow-lg transition-transform hover:scale-110 dark:bg-black/60 dark:text-white dark:hover:bg-black/80">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                </div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 opacity-100 md:opacity-0 md:group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                    <button @click.prevent="next()" class="pointer-events-auto p-1.5 rounded-full bg-white/90 text-black hover:bg-white shadow-lg transition-transform hover:scale-110 dark:bg-black/60 dark:text-white dark:hover:bg-black/80">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

                {{-- Counter (ex: 1/12) --}}
                <div class="absolute bottom-3 left-3 z-10">
                    <div class="flex items-center gap-1.5 px-2 py-1 rounded-md bg-black/60 backdrop-blur-md text-white text-[10px] font-bold tracking-wide">
                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                        <span x-text="activeSlide + 1"></span>/{{ $imgCount }}
                    </div>
                </div>
            @endif

            {{-- Badge Promovat --}}
            @if($isPromoted)
                <div class="absolute top-3 left-3 z-10">
                    <span class="px-2.5 py-1 rounded bg-[#C81424] text-white text-[10px] font-bold uppercase tracking-wider shadow-sm">
                        Promovat
                    </span>
                </div>
            @endif
            
            {{-- Buton Favorite Mobile (Peste poză) --}}
            <button onclick="toggleHeart(this, {{ $service->id }})" 
                    class="md:hidden absolute top-3 right-3 z-20 p-2 rounded-full bg-black/30 backdrop-blur-md border border-white/20 text-white hover:bg-red-500 hover:border-red-500 transition-all">
                <svg class="w-5 h-5 {{ $isFav ? 'text-red-500 fill-red-500' : 'text-white fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        </div>

        {{-- B. ZONA CONȚINUT --}}
        <div class="flex-1 flex flex-col p-4 md:p-5 relative justify-between">
            
            <div>
                {{-- Header: Titlu & Preț --}}
                <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2 mb-2">
                    <div class="flex-1 min-w-0 pr-2">
                        <a href="{{ $service->public_url }}" class="group-hover:text-[#C81424] dark:group-hover:text-red-300 transition-colors">
                            <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-gray-100 leading-snug truncate">
                                {{ $listingTitle }}
                            </h3>
                        </a>
                        {{-- Subtitlu Motoare --}}
                        <div class="text-sm text-gray-500 dark:text-gray-400 mt-1 font-medium flex flex-wrap gap-2 items-center">
                            @if($engineSize) <span>{{ $engineSize }} cm³</span> <span class="text-gray-300">•</span> @endif
                            @if($power) <span>{{ $power }} CP</span> <span class="text-gray-300">•</span> @endif
                            @if($norma) <span class="hidden sm:inline">{{ $norma }}</span> @endif
                        </div>
                    </div>

                    {{-- Preț --}}
                    <div class="mt-1 md:mt-0 md:text-right shrink-0 flex items-center md:flex-col md:items-end gap-2 md:gap-0">
                        @if(!empty($service->price_value))
                            <span class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white tracking-tight">
                                {{ number_format($service->price_value, 0, ',', '.') }} <span class="text-base font-semibold">{{ $service->currency }}</span>
                            </span>
                            @if($service->price_type === 'negotiable')
                                <span class="text-[10px] uppercase tracking-wide text-green-600 dark:text-green-400 font-bold bg-green-50 dark:bg-green-900/20 px-1.5 py-0.5 rounded md:mt-1">
                                    Negociabil
                                </span>
                            @endif
                        @else
                            <span class="text-lg font-bold text-[#C81424]">La cerere</span>
                        @endif
                    </div>
                </div>

                {{-- Specificații (Chips / Pastile) --}}
                <div class="grid max-w-xl grid-cols-4 gap-x-2 gap-y-3 mt-3 mb-4">
                    {{-- An --}}
                    <div class="flex min-w-0 flex-col items-center gap-1 text-center text-xs font-medium leading-tight text-gray-700 dark:text-gray-200">
                        <svg class="h-5 w-5 shrink-0 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ $an }}
                    </div>
                    {{-- KM --}}
                    <div class="flex min-w-0 flex-col items-center gap-1 text-center text-xs font-medium leading-tight text-gray-700 dark:text-gray-200">
                        <svg class="h-5 w-5 shrink-0 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 15a2 2 0 100-4 2 2 0 000 4z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 13l3.8-4.4M5.6 18.4a9 9 0 1112.8 0" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7.5 14H6m12 0h-1.5M12 6.5V5" /></svg>
                        {{ $kmLabel }}
                    </div>
                    {{-- Combustibil --}}
                    <div class="flex min-w-0 flex-col items-center gap-1 text-center text-xs font-medium leading-tight text-gray-700 dark:text-gray-200">
                        <svg class="h-5 w-5 shrink-0 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7 21V5a2 2 0 012-2h5a2 2 0 012 2v16M6 21h11M9 8h5M16 7h1.5L20 9.5V18a2 2 0 01-2 2h-1" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M20 10h-3" /></svg>
                        {{ $fuel }}
                    </div>
                    {{-- Transmisie --}}
                    <div class="flex min-w-0 flex-col items-center gap-1 text-center text-xs font-medium leading-tight text-gray-700 dark:text-gray-200">
                        <svg class="h-5 w-5 shrink-0 text-gray-900 dark:text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7 5v14m10-14v14M7 12h10M12 5v7" /><circle cx="7" cy="5" r="2" stroke-width="1.9" /><circle cx="17" cy="5" r="2" stroke-width="1.9" /><circle cx="7" cy="19" r="2" stroke-width="1.9" /><circle cx="17" cy="19" r="2" stroke-width="1.9" /></svg>
                        {{ $gearbox }}
                    </div>
                </div>
            </div>

            {{-- Footer: Locație, Dată & Acțiuni --}}
            <div class="mt-auto pt-3 flex items-end justify-between border-t border-gray-100 dark:border-[#27272a]">
                
                <div class="flex flex-col gap-1.5">
                    {{-- Locație --}}
                    <div class="flex items-center text-gray-600 dark:text-gray-400 text-xs font-semibold">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-red-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate max-w-[140px] md:max-w-[180px]">{{ $locationLabel }}</span>
                    </div>
                    {{-- Dată Publicare (UPDATED) --}}
                    <div class="flex items-center text-[11px] text-gray-400 dark:text-gray-500 ml-5">
                        <svg class="w-3 h-3 mr-1 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ $dateLabel }}</span>
                    </div>
                </div>

                {{-- Acțiuni Desktop --}}
                <div class="flex items-center gap-3">
                    <button onclick="toggleHeart(this, {{ $service->id }})" 
                            class="hidden md:flex group/btn items-center justify-center w-8 h-8 rounded-full bg-gray-50 dark:bg-[#27272a] hover:bg-red-50 dark:hover:bg-red-900/20 text-gray-400 hover:text-red-500 transition-all border border-gray-100 dark:border-gray-700"
                            title="Salvează anunțul">
                        <svg class="w-4.5 h-4.5 transition-colors {{ $isFav ? 'text-red-500 fill-red-500' : 'fill-none' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                    
                    <a href="{{ $service->public_url }}" class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-[#C81424] text-white text-xs font-bold uppercase tracking-wide rounded-lg shadow-sm shadow-red-700/20 transition-colors hover:bg-[#94111B]">
                        Vezi anunț
                    </a>
                </div>
            </div>

        </div>
    </article>

@empty
    {{-- Empty State Modern --}}
    <div class="col-span-full py-16 md:py-24 px-4 text-center">
        <div class="inline-block p-6 rounded-full bg-gray-50 dark:bg-[#27272a] mb-5 shadow-inner">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
        <h3 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white mb-3">Nu am găsit încă anunțuri potrivite</h3>
        <p class="mx-auto max-w-2xl text-sm md:text-base leading-relaxed text-gray-500 dark:text-gray-400">
            iaAuto.ro este în continuă creștere, iar oferta se actualizează constant. Încearcă să lărgești căutarea sau să elimini câteva filtre.
        </p>

        <div class="mt-7 flex flex-col items-center justify-center gap-3 sm:flex-row">
            <a href="{{ route('cars.index') }}" onclick="if (typeof resetFilters === 'function') { event.preventDefault(); resetFilters(); }"
               class="inline-flex w-full max-w-xs items-center justify-center rounded-xl bg-[#C81424] px-5 py-3 text-sm font-black text-white shadow-sm shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98] sm:w-auto">
                Șterge filtrele
            </a>
            <a href="{{ route('cars.index') }}" onclick="if (typeof resetFilters === 'function') { event.preventDefault(); resetFilters(); }"
               class="inline-flex w-full max-w-xs items-center justify-center rounded-xl border border-[#C81424]/40 bg-white px-5 py-3 text-sm font-black text-[#C81424] transition hover:border-[#C81424] hover:bg-[#fff4f5] dark:border-red-900/50 dark:bg-[#18181b] dark:text-red-200 dark:hover:bg-[#2a1013] sm:w-auto">
                Vezi toate anunțurile
            </a>
        </div>
    </div>
@endforelse
