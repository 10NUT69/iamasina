@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
    @endphp

    {{-- CARD INDIVIDUAL --}}
    <div class="card-animate relative bg-white dark:bg-[#1E1E1E] rounded-xl md:rounded-2xl border border-gray-200 dark:border-[#333333] shadow-sm 
                hover:shadow-xl dark:hover:shadow-none dark:hover:border-[#555555] 
                transition-all duration-300 overflow-hidden group flex flex-col h-full">

        {{-- Favorite Button --}}
        <button type="button"
                onclick="toggleHeart(this, {{ $service->id }})"
                @if(!auth()->check()) onclick="window.location.href='{{ route('login') }}'" @endif
                class="absolute top-2 right-2 md:top-3 md:right-3 z-30 p-1.5 md:p-2 rounded-full backdrop-blur-md shadow-sm transition-all duration-200
                        bg-white/80 dark:bg-black/50 hover:bg-white dark:hover:bg-[#2C2C2C] group/heart border border-white/20"
                title="Adaugă la favorite">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="heart-icon h-4 w-4 md:h-5 md:w-5 transition-transform duration-300 ease-in-out group-active/heart:scale-75
                        {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E] scale-110' : 'text-gray-600 dark:text-gray-300 fill-none group-hover/heart:text-[#CC2E2E]' }}"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </button>

        {{-- Link Către Anunț --}}
        <a href="{{ $service->public_url }}" class="block flex-grow flex flex-col">

            {{-- Image Area --}}
            <div class="relative w-full aspect-[4/3] bg-gray-100 dark:bg-[#121212] overflow-hidden">
                <img src="{{ $service->main_image_url }}"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    alt="{{ $service->title }}"
                    @if($loop->index < 2) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                    width="400" height="300">

                {{-- Badge Categorie --}}
                <span class="absolute bottom-2 left-2 md:bottom-3 md:left-3 bg-black/70 text-white text-[9px] md:text-xs px-2 py-0.5 md:px-2.5 md:py-1 rounded-md font-bold uppercase backdrop-blur-md border border-white/10 shadow-lg">
                    {{ $service->category->name }}
                </span>
            </div> 

            {{-- Card Content --}}
            <div class="p-3 md:p-4 flex flex-col flex-grow">
               <h3 class="text-sm md:text-lg font-bold text-gray-900 dark:text-[#F2F2F2] mb-2 
           uppercase tracking-tight line-clamp-2 leading-snug overflow-hidden 
           group-hover:text-[#CC2E2E] transition-colors 
           min-h-[2.5rem] md:min-h-[3.5rem]"
    title="{{ $service->title }}">
    {{ $service->title }}
</h3>


                <div class="mb-3">
                    @if(!empty($service->price_value))
                        <div class="flex items-baseline gap-1">
                            <span class="text-base md:text-xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                            </span>
                            @if($service->price_type === 'negotiable')
                                <span class="text-gray-500 dark:text-gray-400 text-[10px] md:text-xs font-normal">Neg.</span>
                            @endif
                        </div>
                    @else
                        <span class="text-sm md:text-lg font-bold text-[#CC2E2E]">Cere ofertă</span>
                    @endif
                </div>

                <div class="mt-auto pt-2 flex items-center justify-between text-[10px] md:text-sm text-gray-500 dark:text-[#A1A1AA] border-t border-gray-100 dark:border-[#333333]">
                    <div class="flex items-center gap-1 truncate max-w-[40%]" title="{{ $service->city ?? $service->county->name }}">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4 text-[#CC2E2E] opacity-70 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="truncate font-medium">{{ $service->city ?? $service->county->name }}</span>
                    </div>

                    <div class="flex items-center gap-2 md:gap-3">
                        <div class="flex items-center gap-0.5 opacity-80" title="Vizualizări">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-3 w-3 md:h-4 md:w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                            </svg>
                            <span class="font-medium">{{ $service->views ?? 0 }}</span>
                        </div>
                        <span class="opacity-80 whitespace-nowrap">
                            @if($service->created_at->isToday())
                                <span class="text-green-600 dark:text-green-400 font-bold">Azi</span>
                            @elseif($service->created_at->isYesterday())
                                <span>Ieri</span>
                            @else
                                {{ $service->created_at->format('d.m.Y') }}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </a>
    </div>
@empty
    {{-- 🔥 EMPTY STATE GRAFIC (DESIGN COMPLET) --}}
    <div class="col-span-full flex flex-col items-center justify-center py-20 px-4 text-center bg-white dark:bg-[#1E1E1E] rounded-3xl border-2 border-dashed border-gray-200 dark:border-[#333333]">
        <div class="inline-flex items-center justify-center w-24 h-24 rounded-full bg-red-50 dark:bg-red-900/10 mb-6 animate-pulse">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-10 w-10 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
        </div>
        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">
            Nu am găsit anunțuri
        </h3>
        <p class="text-gray-500 dark:text-gray-400 max-w-md mx-auto mb-8 leading-relaxed">
            Din păcate nu există anunțuri care să corespundă filtrelor selectate. Încearcă să cauți altceva sau să resetezi filtrele.
        </p>
        <button type="button" onclick="resetFilters()" 
                class="inline-flex items-center gap-2 px-8 py-3.5 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold rounded-xl shadow-lg shadow-red-600/20 transition-all hover:-translate-y-1 active:translate-y-0">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
            </svg>
            Resetează Filtrele
        </button>
    </div>
@endforelse