@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());

        // Construim locația: Localitate, Județ
        $loc = $service->locality->name ?? '';
        $jud = $service->county->name ?? '';
        $locationLabel = $loc ? "$loc, $jud" : $jud;

        // Construim Titlul: Brand + Model
        $brandName = optional(optional(optional($service->generation)->model)->brand)->name
            ?: optional($service->brandRel)->name
            ?: ($service->brand ?? 'Marca');
        $modelName = optional(optional($service->generation)->model)->name
            ?: optional($service->modelRel)->name
            ?: ($service->model ?? 'Model');
        $titlu = trim("{$brandName} {$modelName}");
        $listingTitle = $service->title ?: $titlu;
        $badgeLabel = $titlu ?: 'Auto';

        // Detalii tehnice
        $an = $service->an_fabricatie ?? '-';
        $km = $service->km ? number_format($service->km, 0, ',', '.') . ' km' : '-';
        $fuel = $service->combustibil->nume ?? '-';
        $transmisie = $service->transmission->nume ?? $service->cutieViteze->nume ?? '-';
        $engineSize = $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, ',', '.') . ' cm³' : '-';
        $power = $service->putere ? $service->putere . ' CP' : '-';
        $normaPoluare = $service->normaPoluare->nume ?? '-';

        // Imagini
        $imagesList = $service->images ?? [];
        if (is_string($imagesList)) {
            $imagesList = json_decode($imagesList, true) ?: [];
        }
        if (is_object($imagesList) && method_exists($imagesList, 'all')) {
            $imagesList = $imagesList->all();
        }
        $imagesList = is_array($imagesList) ? $imagesList : [];
        $imgCount = count($imagesList);
    @endphp

    <div class="group relative bg-white dark:bg-[#1E1E1E] rounded-xl border border-gray-200 dark:border-[#333333] shadow-sm hover:shadow-lg transition-all duration-300 flex flex-col md:flex-row overflow-hidden mb-4 md:min-h-[300px]">

        {{-- 1. GALERIE FOTO (Cu Alpine.js pentru Săgeți și Swipe) --}}
        <div class="relative w-full md:basis-1/2 md:shrink-0 h-80 md:h-[300px] bg-gray-100 dark:bg-[#121212]"
             x-data="{ 
                activeSlide: 0, 
                slides: {{ $imgCount > 0 ? $imgCount : 1 }},
                next() { this.activeSlide = (this.activeSlide === this.slides - 1) ? 0 : this.activeSlide + 1 },
                prev() { this.activeSlide = (this.activeSlide === 0) ? this.slides - 1 : this.activeSlide - 1 }
             }">

            {{-- Container Imagini --}}
            <div class="relative w-full h-full overflow-hidden">
                @if($imgCount > 0)
                    @foreach($imagesList as $index => $img)
                        @php
                            $path = '';
                            if (is_string($img)) {
                                $path = $img;
                            } elseif (is_array($img)) {
                                $path = $img['path'] ?? $img['url'] ?? '';
                            } elseif (is_object($img)) {
                                $path = $img->path ?? $img->url ?? '';
                            }
                            $imageUrl = $path
                                ? (\Illuminate\Support\Str::startsWith($path, ['http://', 'https://'])
                                    ? $path
                                    : asset('storage/services/' . ltrim($path, '/')))
                                : '';
                        @endphp

                        @if($imageUrl)
                            <div class="absolute inset-0 transition-transform duration-300 ease-out w-full h-full"
                                 x-show="activeSlide === {{ $index }}"
                                 x-transition:enter="transition transform ease-out duration-300"
                                 x-transition:enter-start="opacity-0 scale-95"
                                 x-transition:enter-end="opacity-100 scale-100"
                                 x-transition:leave="transition transform ease-in duration-300"
                                 x-transition:leave-start="opacity-100 scale-100"
                                 x-transition:leave-end="opacity-0 scale-95">

                                <a href="{{ $service->public_url }}" class="block w-full h-full">
                                    <img src="{{ $imageUrl }}" 
                                         class="w-full h-full object-cover" 
                                         alt="{{ $titlu }}" loading="lazy">
                                </a>
                            </div>
                        @endif
                    @endforeach
                @else
                    {{-- Fallback Image --}}
                    <a href="{{ $service->public_url }}" class="block w-full h-full">
                        <img src="{{ $service->main_image_url }}" class="w-full h-full object-cover" alt="No image">
                    </a>
                @endif
            </div>

            {{-- Săgeți Navigare (Doar dacă sunt mai multe poze) --}}
            @if($imgCount > 1)
                {{-- Stânga --}}
                <button @click.prevent="prev()" 
                        class="absolute left-2 top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-black/50 text-white hover:bg-black/70 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" /></svg>
                </button>
                {{-- Dreapta --}}
                <button @click.prevent="next()" 
                        class="absolute right-2 top-1/2 -translate-y-1/2 p-1.5 rounded-full bg-black/50 text-white hover:bg-black/70 backdrop-blur-sm opacity-0 group-hover:opacity-100 transition-opacity z-10">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                </button>

                {{-- Dots Indicator (Jos) --}}
                <div class="absolute bottom-3 left-0 right-0 flex justify-center gap-1.5 z-10">
                    <template x-for="i in slides">
                        <div class="w-1.5 h-1.5 rounded-full transition-colors duration-200"
                             :class="activeSlide === i-1 ? 'bg-white' : 'bg-white/40'"></div>
                    </template>
                </div>
            @endif

            {{-- Badge Categorie (Stânga Jos) --}}
            <div class="absolute bottom-3 left-3 z-10">
                 <span class="text-[10px] font-bold text-white px-2 py-0.5 rounded bg-[#CC2E2E] shadow-sm uppercase tracking-wide">
                    {{ $badgeLabel }}
                 </span>
            </div>
        </div>

        {{-- 2. ZONA CONȚINUT (Centru + Dreapta) --}}
        <div class="flex flex-col md:flex-row flex-1 p-4 gap-4">

            {{-- Centru: Informații --}}
            <div class="flex-1 md:basis-[35%] flex flex-col min-w-0">
                <div class="flex flex-col gap-3">
                    {{-- Titlu (Anunț) --}}
                    <a href="{{ $service->public_url }}">
                        <h3 class="text-xl font-extrabold text-gray-900 dark:text-white hover:text-[#CC2E2E] transition-colors truncate">
                            {{ $listingTitle }}
                        </h3>
                    </a>

                    {{-- Linie: capacitate cilindrică + CP --}}
                    <div class="text-sm text-gray-600 dark:text-gray-300 flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ $engineSize }}</span>
                        <span class="text-gray-400">•</span>
                        <span class="font-medium">{{ $power }}</span>
                    </div>

                    {{-- Linie: an fabricație, combustibil, km --}}
                    <div class="flex flex-wrap items-center gap-4 text-sm text-gray-700 dark:text-gray-300">
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span class="font-medium">{{ $an }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span class="font-medium">{{ $fuel }}</span>
                        </span>
                        <span class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span class="font-medium">{{ $km }}</span>
                        </span>
                    </div>

                    {{-- Linie: capacitate cilindrică, CP, normă poluare --}}
                    <div class="text-sm text-gray-600 dark:text-gray-300 flex flex-wrap items-center gap-2">
                        <span class="font-medium">{{ $engineSize }}</span>
                        <span class="text-gray-400">•</span>
                        <span class="font-medium">{{ $power }}</span>
                        <span class="text-gray-400">•</span>
                        <span class="font-medium">{{ $normaPoluare }}</span>
                    </div>
                </div>

                {{-- Locație (Jos) --}}
                <div class="mt-4 pt-3 border-t border-gray-100 dark:border-[#2C2C2C] flex items-center text-sm text-gray-500 dark:text-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1.5 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    {{ $locationLabel }}
                </div>
            </div>

            {{-- Dreapta: Preț și Favorite --}}
            <div class="md:basis-[15%] md:max-w-[15%] flex flex-row md:flex-col justify-between items-center md:items-end border-t md:border-t-0 md:border-l border-gray-100 dark:border-[#2C2C2C] pt-3 md:pt-0 md:pl-4 mt-1 md:mt-0">

                {{-- Zona Preț --}}
                <div class="text-left md:text-right">
                    @if(!empty($service->price_value))
                        <div class="flex flex-col md:items-end">
                            <span class="text-2xl font-bold text-[#CC2E2E] dark:text-[#FF4444]">
                                {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                            </span>
                            @if($service->price_type === 'negotiable')
                                <span class="text-[10px] uppercase font-bold text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded mt-1">
                                    Negociabil
                                </span>
                            @endif
                        </div>
                    @else
                        <span class="text-lg font-bold text-blue-600">La cerere</span>
                    @endif
                </div>

                {{-- Buton Favorite --}}
                <button type="button"
                    onclick="toggleHeart(this, {{ $service->id }})"
                    class="group/heart flex items-center justify-center p-2 rounded-full hover:bg-gray-50 dark:hover:bg-[#333] transition-colors"
                    title="Adaugă la favorite">
                    <svg xmlns="http://www.w3.org/2000/svg" 
                         class="h-7 w-7 transition-transform duration-300 {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E]' : 'text-gray-400 fill-none group-hover/heart:text-[#CC2E2E]' }}" 
                         viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>

            </div>
        </div>
    </div>
@empty
   <div class="col-span-full py-20 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#252525] mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Nu am găsit anunțuri</h3>
   </div>
@endforelse
