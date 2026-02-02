@forelse($services as $service)
    @php
        // 1. Logica Favorite
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());

        // 2. Logica Locație
        $loc = $service->locality->name ?? '';
        $jud = $service->county->name ?? '';
        $locationLabel = $loc ? "$loc, $jud" : $jud;

        // 3. Construcție Titlu (Brand + Model dacă nu există titlu custom)
        $brandName = optional(optional(optional($service->generation)->model)->brand)->name
            ?: optional($service->brandRel)->name
            ?: ($service->brand ?? 'Marca');
        $modelName = optional(optional($service->generation)->model)->name
            ?: optional($service->modelRel)->name
            ?: ($service->model ?? 'Model');
        
        $titluCalculat = trim("{$brandName} {$modelName}");
        $listingTitle = $service->title ?: $titluCalculat;
        
        // Badge-uri (ex: Promovat)
        $isPromoted = $service->promoted ?? false; 

        // 4. Formatare Valori Tehnice
        $an = $service->an_fabricatie ?? '-';
        $km = $service->km ? number_format($service->km, 0, ',', '.') : null;
        $fuel = $service->combustibil->nume ?? '-';
        $engineSize = $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, ',', '.') : null;
        $power = $service->putere ?? null;
        $norma = $service->normaPoluare->nume ?? null;

        // 5. Procesare Imagini
        $imagesList = $service->images ?? [];
        if (is_string($imagesList)) $imagesList = json_decode($imagesList, true) ?: [];
        if (is_object($imagesList) && method_exists($imagesList, 'all')) $imagesList = $imagesList->all();
        $imagesList = is_array($imagesList) ? $imagesList : [];
        $imgCount = count($imagesList);

        // 6. LOGICĂ DATĂ (TimeAgo / Calendaristic)
        $updated = \Carbon\Carbon::parse($service->updated_at);
        if ($updated->isToday()) {
            $dateLabel = 'Azi ' . $updated->format('H:i');
        } elseif ($updated->isYesterday()) {
            $dateLabel = 'Ieri ' . $updated->format('H:i');
        } else {
            // Data formatată scurt (ex: 22 Ian 2026)
            $dateLabel = $updated->translatedFormat('d M Y'); 
        }
    @endphp

    {{-- CARD ANUNȚ (DESIGN 2025-2026) --}}
    <article class="group relative flex flex-col md:flex-row bg-white dark:bg-[#18181b] rounded-xl border border-gray-200 dark:border-[#27272a] shadow-sm hover:shadow-xl hover:border-blue-500/30 transition-all duration-300 overflow-hidden mb-5">
        
        {{-- A. ZONA FOTO (Slider + Badges) --}}
        <div class="relative w-full md:w-[320px] lg:w-[340px] shrink-0 aspect-[4/3] md:aspect-auto md:min-h-[240px] overflow-hidden bg-gray-100 dark:bg-[#09090b]"
             x-data="{ 
                activeSlide: 0, 
                slides: {{ $imgCount > 0 ? $imgCount : 1 }},
                next() { this.activeSlide = (this.activeSlide === this.slides - 1) ? 0 : this.activeSlide + 1 },
                prev() { this.activeSlide = (this.activeSlide === 0) ? this.slides - 1 : this.activeSlide - 1 }
             }">

            {{-- Link principal pe imagine --}}
            <a href="{{ $service->public_url }}" class="block w-full h-full relative group/img">
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
                <div class="absolute inset-y-0 left-0 flex items-center pl-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                    <button @click.prevent="prev()" class="pointer-events-auto p-1.5 rounded-full bg-white/90 text-black hover:bg-white shadow-lg transition-transform hover:scale-110">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                    </button>
                </div>
                <div class="absolute inset-y-0 right-0 flex items-center pr-2 opacity-0 group-hover:opacity-100 transition-opacity z-10 pointer-events-none">
                    <button @click.prevent="next()" class="pointer-events-auto p-1.5 rounded-full bg-white/90 text-black hover:bg-white shadow-lg transition-transform hover:scale-110">
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
                    <span class="px-2.5 py-1 rounded bg-blue-600 text-white text-[10px] font-bold uppercase tracking-wider shadow-sm">
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
                        <a href="{{ $service->public_url }}" class="group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">
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
                            <span class="text-lg font-bold text-blue-600">La cerere</span>
                        @endif
                    </div>
                </div>

                {{-- Specificații (Chips / Pastile) --}}
                <div class="flex flex-wrap gap-2 mt-3 mb-4">
                    {{-- An --}}
                    <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-[#27272a] text-gray-700 dark:text-gray-300 text-xs font-semibold border border-gray-100 dark:border-gray-700/50">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        {{ $an }}
                    </div>
                    {{-- KM --}}
                    @if($km)
                    <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-[#27272a] text-gray-700 dark:text-gray-300 text-xs font-semibold border border-gray-100 dark:border-gray-700/50">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        {{ $km }} km
                    </div>
                    @endif
                    {{-- Combustibil --}}
                    <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-[#27272a] text-gray-700 dark:text-gray-300 text-xs font-semibold border border-gray-100 dark:border-gray-700/50">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                        {{ $fuel }}
                    </div>
                    {{-- Transmisie --}}
                    <div class="inline-flex items-center px-2.5 py-1.5 rounded-lg bg-gray-50 dark:bg-[#27272a] text-gray-700 dark:text-gray-300 text-xs font-semibold border border-gray-100 dark:border-gray-700/50">
                        <svg class="w-3.5 h-3.5 mr-1.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4" /></svg>
                        {{ $service->transmission->nume ?? 'Automată' }}
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
                    
                    <a href="{{ $service->public_url }}" class="hidden md:inline-flex items-center justify-center px-4 py-2 bg-gray-900 dark:bg-white text-white dark:text-black text-xs font-bold uppercase tracking-wide rounded-lg hover:bg-blue-600 dark:hover:bg-gray-200 transition-colors shadow-sm">
                        Vezi anunț
                    </a>
                </div>
            </div>

        </div>
    </article>

@empty
    {{-- Empty State Modern --}}
    <div class="col-span-full py-20 text-center">
        <div class="inline-block p-6 rounded-full bg-gray-50 dark:bg-[#27272a] mb-5 shadow-inner">
            <svg class="w-12 h-12 text-gray-300 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nu s-au găsit anunțuri</h3>
        <p class="text-gray-500 dark:text-gray-400">Încearcă să ștergi filtrele sau să cauți alt model.</p>
    </div>
@endforelse