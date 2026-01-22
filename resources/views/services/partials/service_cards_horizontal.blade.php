@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());

        // --- Logică Date (Păstrată și optimizată) ---
        $loc = $service->locality->name ?? '';
        $jud = $service->county->name ?? '';
        $locationLabel = $loc ? "$loc, $jud" : $jud;

        $brandName = optional(optional(optional($service->generation)->model)->brand)->name
            ?: optional($service->brandRel)->name
            ?: ($service->brand ?? 'Marca');
        $modelName = optional(optional($service->generation)->model)->name
            ?: optional($service->modelRel)->name
            ?: ($service->model ?? 'Model');
        
        $titluCalculat = trim("{$brandName} {$modelName}");
        $listingTitle = $service->title ?: $titluCalculat;
        $badgeLabel = $titluCalculat ?: 'Auto';

        // Formatare valori pentru UI curat
        $an = $service->an_fabricatie ?? '-';
        $km = $service->km ? number_format($service->km, 0, ',', '.') : '-'; // Fără 'km' aici, îl punem în UI
        $fuel = $service->combustibil->nume ?? '-';
        $engineSize = $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, ',', '.') : '-';
        $power = $service->putere ?? '-';
        $norma = $service->normaPoluare->nume ?? null;

        // Imagini
        $imagesList = $service->images ?? [];
        if (is_string($imagesList)) $imagesList = json_decode($imagesList, true) ?: [];
        if (is_object($imagesList) && method_exists($imagesList, 'all')) $imagesList = $imagesList->all();
        $imagesList = is_array($imagesList) ? $imagesList : [];
        $imgCount = count($imagesList);
    @endphp

    {{-- CARD PRINCIPAL --}}
    <article class="group relative flex flex-col md:flex-row bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-100 dark:border-[#333] shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden mb-6 h-auto md:min-h-[260px]">
        
        {{-- 1. ZONA FOTO (Stânga - Aspect Ratio fix și Zoom la hover) --}}
        <div class="relative w-full md:w-[40%] lg:w-[35%] shrink-0 h-64 md:h-auto overflow-hidden bg-gray-200 dark:bg-[#121212]"
             x-data="{ 
                activeSlide: 0, 
                slides: {{ $imgCount > 0 ? $imgCount : 1 }},
                next() { this.activeSlide = (this.activeSlide === this.slides - 1) ? 0 : this.activeSlide + 1 },
                prev() { this.activeSlide = (this.activeSlide === 0) ? this.slides - 1 : this.activeSlide - 1 }
             }">

            {{-- Slider Container --}}
            <a href="{{ $service->public_url }}" class="block w-full h-full relative z-0">
                @if($imgCount > 0)
                    @foreach($imagesList as $index => $img)
                        @php
                            $path = is_string($img) ? $img : ($img['path'] ?? $img['url'] ?? $img->path ?? '');
                            $imageUrl = $path ? (\Illuminate\Support\Str::startsWith($path, ['http', 'https']) ? $path : asset('storage/services/' . ltrim($path, '/'))) : '';
                        @endphp
                        @if($imageUrl)
                            <img src="{{ $imageUrl }}" 
                                 x-show="activeSlide === {{ $index }}"
                                 x-transition:enter="transition transform duration-500 ease-out"
                                 x-transition:enter-start="opacity-0 scale-105"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 class="absolute inset-0 w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out"
                                 alt="{{ $listingTitle }}" loading="lazy">
                        @endif
                    @endforeach
                @else
                    <img src="{{ $service->main_image_url }}" class="absolute inset-0 w-full h-full object-cover grayscale opacity-80" alt="No image">
                @endif
                
                {{-- Gradient Overlay (pentru contrast text) --}}
                <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent pointer-events-none"></div>
            </a>

            {{-- Navigare Slider (Doar dacă sunt poze multiple) --}}
            @if($imgCount > 1)
                <div class="absolute inset-x-0 top-1/2 -translate-y-1/2 flex justify-between px-2 opacity-0 group-hover:opacity-100 transition-opacity duration-300 z-10 pointer-events-none">
                    <button @click.prevent="prev()" class="pointer-events-auto p-2 rounded-full bg-white/20 backdrop-blur-md hover:bg-white text-white hover:text-black transition-colors shadow-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M15 19l-7-7 7-7" /></svg>
                    </button>
                    <button @click.prevent="next()" class="pointer-events-auto p-2 rounded-full bg-white/20 backdrop-blur-md hover:bg-white text-white hover:text-black transition-colors shadow-lg">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path d="M9 5l7 7-7 7" /></svg>
                    </button>
                </div>

                {{-- Counter Badge (ex: 1/5) - Mai profi decât dots --}}
                <div class="absolute bottom-3 right-3 z-10">
                    <span class="px-2 py-1 rounded bg-black/60 backdrop-blur-sm text-[10px] font-bold text-white tracking-wider">
                        <span x-text="activeSlide + 1"></span> / {{ $imgCount }}
                    </span>
                </div>
            @endif

            {{-- Badge Stânga Sus (Ocazie/Nou/etc - Opțional) --}}
            <div class="absolute top-3 left-3 z-10">
                <span class="px-2.5 py-1 rounded-md bg-[#CC2E2E] text-white text-[10px] font-bold uppercase tracking-wide shadow-md">
                    {{ $badgeLabel }}
                </span>
            </div>
        </div>

        {{-- 2. ZONA CONȚINUT (Centru + Dreapta) --}}
        <div class="flex-1 flex flex-col justify-between p-5 relative">
            
            <div class="flex flex-col gap-1">
                {{-- Header: Titlu + Preț (Pe mobil prețul e sub titlu, pe desktop e dreapta sus) --}}
                <div class="flex justify-between items-start gap-4">
                    <a href="{{ $service->public_url }}" class="group-hover:text-[#CC2E2E] transition-colors duration-300">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-white leading-tight line-clamp-2">
                            {{ $listingTitle }}
                        </h3>
                    </a>
                    
                    {{-- Preț (Desktop position) --}}
                    <div class="hidden md:block text-right shrink-0">
                        @if(!empty($service->price_value))
                            <div class="text-2xl font-black text-[#CC2E2E] dark:text-[#FF4444]">
                                {{ number_format($service->price_value, 0, ',', '.') }} <span class="text-base font-bold">{{ $service->currency }}</span>
                            </div>
                            @if($service->price_type === 'negotiable')
                                <div class="text-[10px] text-gray-500 uppercase tracking-wide font-medium">Negociabil</div>
                            @endif
                        @else
                            <div class="text-lg font-bold text-blue-600">La cerere</div>
                        @endif
                    </div>
                </div>

                {{-- Subtitlu / Motoare --}}
                <p class="text-sm text-gray-500 dark:text-gray-400 font-medium">
                    {{ $engineSize }} cm³ • {{ $power }} CP @if($norma) • {{ $norma }} @endif
                </p>
            </div>

            {{-- Grid Specificații (Modern Tags) --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-2 my-4">
                {{-- An --}}
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-[#252525] rounded-lg px-2.5 py-2 border border-gray-100 dark:border-[#333]">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $an }}</span>
                </div>
                {{-- KM --}}
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-[#252525] rounded-lg px-2.5 py-2 border border-gray-100 dark:border-[#333]">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200">{{ $km }} <span class="font-normal text-gray-400">km</span></span>
                </div>
                {{-- Combustibil --}}
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-[#252525] rounded-lg px-2.5 py-2 border border-gray-100 dark:border-[#333]">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $fuel }}</span>
                </div>
                {{-- Transmisie (Fallback generic dacă nu există) --}}
                <div class="flex items-center gap-2 bg-gray-50 dark:bg-[#252525] rounded-lg px-2.5 py-2 border border-gray-100 dark:border-[#333]">
                    <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                    <span class="text-xs font-semibold text-gray-700 dark:text-gray-200 truncate">{{ $service->transmission->nume ?? 'Automată' }}</span>
                </div>
            </div>

            {{-- Footer Card: Locație și Acțiuni --}}
            <div class="flex items-end justify-between mt-auto pt-4 border-t border-gray-100 dark:border-[#333]">
                
                {{-- Locație --}}
                <div class="flex flex-col">
                    <div class="flex items-center text-gray-500 dark:text-gray-400 text-sm">
                        <svg class="w-4 h-4 mr-1 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="truncate max-w-[150px] md:max-w-[200px]">{{ $locationLabel }}</span>
                    </div>
                    <span class="text-[10px] text-gray-400 ml-5 mt-0.5">Publicat recent</span>
                </div>

                {{-- Acțiuni Mobile (Prețul apare aici pe mobile) --}}
                <div class="flex items-center gap-3">
                    <div class="md:hidden flex flex-col items-end mr-2">
                        @if(!empty($service->price_value))
                            <span class="text-xl font-bold text-[#CC2E2E] dark:text-[#FF4444]">{{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}</span>
                        @endif
                    </div>

                    {{-- Buton Favorite --}}
                    <button onclick="toggleHeart(this, {{ $service->id }})" 
                            class="group/btn relative flex items-center justify-center w-10 h-10 rounded-full bg-gray-100 dark:bg-[#2C2C2C] hover:bg-[#CC2E2E] dark:hover:bg-[#CC2E2E] transition-all duration-300 shadow-sm"
                            title="Adaugă la favorite">
                        <svg class="w-5 h-5 transition-colors duration-300 {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E] group-hover/btn:text-white group-hover/btn:fill-white' : 'text-gray-500 dark:text-gray-400 fill-none group-hover/btn:text-white' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </article>

@empty
    {{-- Empty State Premium --}}
    <div class="col-span-full flex flex-col items-center justify-center py-24 px-4 text-center">
        <div class="w-24 h-24 bg-gray-50 dark:bg-[#252525] rounded-full flex items-center justify-center mb-6 shadow-inner">
            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-2">Nu am găsit anunțuri</h3>
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto">Încearcă să modifici filtrele de căutare sau șterge selecțiile actuale pentru a vedea mai multe rezultate.</p>
    </div>
@endforelse