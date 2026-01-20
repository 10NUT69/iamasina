@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
        $locationLabel = $service->locality->name ?? $service->city ?? $service->county->name;
        
        // Simulare date pentru layout (Autovit are An, Km, Combustibil). 
        // Dacă nu ai aceste câmpuri, poți șterge secțiunea "Specs Grid".
        $year = $service->year ?? '2017'; 
        $mileage = $service->mileage ?? 'N/A km';
        $fuel = $service->fuel ?? 'Diesel';
    @endphp

    <div class="group relative bg-white dark:bg-[#1E1E1E] rounded-lg border border-gray-200 dark:border-[#333333] shadow-sm hover:shadow-md transition-all duration-300 flex flex-col md:flex-row overflow-hidden mb-4">
        
        {{-- 1. SECTIUNEA IMAGINE (Stânga) --}}
        <a href="{{ $service->public_url }}" class="relative w-full md:w-[300px] lg:w-[340px] shrink-0 h-56 md:h-auto block overflow-hidden bg-gray-100 dark:bg-[#121212]">
            <img src="{{ $service->main_image_url }}" 
                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105"
                 alt="{{ $service->title }}"
                 @if($loop->index < 2) loading="eager" fetchpriority="high" @else loading="lazy" @endif>
            
            {{-- Badge Categorie (Stil Autovit - discret jos stânga) --}}
            <div class="absolute bottom-0 left-0 p-2 w-full bg-gradient-to-t from-black/80 to-transparent">
                 <span class="text-xs font-semibold text-white px-2 py-0.5 rounded bg-[#CC2E2E]">
                    {{ $service->category->name }}
                 </span>
            </div>

            {{-- Badge Promovat (Dacă e cazul) --}}
            @if($service->is_promoted ?? false)
                <span class="absolute top-2 left-2 bg-[#00B4D8] text-white text-[10px] font-bold px-2 py-1 rounded shadow-sm">
                    PROMOVAT
                </span>
            @endif
        </a>

        {{-- 2. & 3. CONTAINER CONTINUT (Centru + Dreapta) --}}
        <div class="flex flex-col md:flex-row flex-1 p-4 gap-4">
            
            {{-- 2. INFORMAȚII PRINCIPALE (Centru) --}}
            <div class="flex-1 flex flex-col justify-between">
                <div>
                    <a href="{{ $service->public_url }}">
                        <h3 class="text-lg md:text-xl font-bold text-gray-900 dark:text-gray-100 line-clamp-2 hover:text-[#CC2E2E] transition-colors mb-2">
                            {{ $service->title }}
                        </h3>
                    </a>
                    
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 flex items-center gap-1">
                         {{-- Iconiță subtilă mașină/categorie --}}
                         <span class="text-xs font-medium text-gray-400 uppercase tracking-wide">
                             {{ $service->subtitle ?? 'Descriere scurtă' }}
                         </span>
                    </p>

                    {{-- Specs Grid (Stil Autovit: An | Km | Combustibil) --}}
                    {{-- Adaptează variabilele de aici cu ce ai tu în baza de date --}}
                    <div class="flex flex-wrap items-center gap-y-1 gap-x-3 text-sm text-gray-700 dark:text-gray-300 mb-3">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            <span>{{ $year }}</span>
                        </div>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                            <span>{{ $mileage }}</span>
                        </div>
                        <span class="w-1 h-1 rounded-full bg-gray-300"></span>
                        <div class="flex items-center gap-1">
                             <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.384-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" /></svg>
                            <span>{{ $fuel }}</span>
                        </div>
                    </div>
                </div>

                {{-- Locație și Seller --}}
                <div class="mt-2 pt-3 border-t border-gray-100 dark:border-[#2C2C2C] flex items-center justify-between">
                    <div class="flex items-center gap-1.5 text-xs md:text-sm text-gray-500 dark:text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        {{ $locationLabel }}
                    </div>
                    <div class="text-xs text-gray-400">
                        {{ $service->created_at->diffForHumans() }}
                    </div>
                </div>
            </div>

            {{-- 3. PREȚ ȘI ACȚIUNI (Dreapta) --}}
            <div class="md:w-48 lg:w-56 flex flex-row md:flex-col justify-between md:items-end md:text-right border-t md:border-t-0 md:border-l border-gray-100 dark:border-[#2C2C2C] pt-3 md:pt-0 md:pl-4 mt-1 md:mt-0">
                
                {{-- Pret --}}
                <div class="flex flex-col md:items-end">
                    @if(!empty($service->price_value))
                        <span class="text-2xl font-bold text-[#CC2E2E] dark:text-[#FF4444]">
                            {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                        </span>
                        @if($service->price_type === 'negotiable')
                            <span class="text-xs text-gray-500 bg-gray-100 dark:bg-gray-800 px-1.5 py-0.5 rounded mt-1 inline-block">Negociabil</span>
                        @endif
                        {{-- Calcul Rata (Fake Link ca pe Autovit) --}}
                        <a href="#" class="text-xs text-blue-600 dark:text-blue-400 underline mt-2 hover:no-underline hidden md:block">
                            Calculează rata
                        </a>
                    @else
                        <span class="text-xl font-bold text-[#CC2E2E]">Cere ofertă</span>
                    @endif
                </div>

                {{-- Favorite Buton --}}
                <div class="flex items-center gap-2 mt-auto">
                    <button type="button"
                        onclick="toggleHeart(this, {{ $service->id }})"
                        class="group/heart flex items-center gap-2 px-0 md:px-3 py-2 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 transition-colors"
                        title="Adaugă la favorite">
                        
                        <svg xmlns="http://www.w3.org/2000/svg" 
                             class="h-6 w-6 transition-transform duration-300 {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E]' : 'fill-none group-hover/heart:text-[#CC2E2E]' }}" 
                             viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                        </svg>
                        <span class="md:hidden text-sm font-medium text-gray-600 dark:text-gray-300">Salvează</span>
                    </button>
                </div>

            </div>
        </div>
    </div>
@empty
    {{-- Sectiunea Empty State (am pastrat-o pe a ta, e ok) --}}
    <div class="col-span-full flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-[#1E1E1E] rounded-xl border border-dashed border-gray-300 dark:border-[#333333]">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-white/5 mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nu am găsit rezultate</h3>
        <p class="text-gray-500 mb-6">Încearcă să modifici filtrele de căutare.</p>
        <button onclick="resetFilters()" class="text-[#CC2E2E] font-bold hover:underline">Resetează filtrele</button>
    </div>
@endforelse