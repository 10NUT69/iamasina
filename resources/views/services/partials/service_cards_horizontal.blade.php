@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
        $locationLabel = $service->locality->name ?? $service->city ?? $service->county->name;
    @endphp

    <div class="card-animate relative bg-white dark:bg-[#1E1E1E] rounded-2xl border border-gray-200 dark:border-[#333333] shadow-sm
                hover:shadow-lg dark:hover:shadow-none dark:hover:border-[#555555]
                transition-all duration-300 overflow-hidden group flex flex-col md:flex-row">

        {{-- Favorite Button --}}
        <button type="button"
                onclick="toggleHeart(this, {{ $service->id }})"
                @if(!auth()->check()) onclick="window.location.href='{{ route('login') }}'" @endif
                class="absolute top-3 right-3 z-30 p-2 rounded-full backdrop-blur-md shadow-sm transition-all duration-200
                        bg-white/80 dark:bg-black/50 hover:bg-white dark:hover:bg-[#2C2C2C] group/heart border border-white/20"
                title="Adaugă la favorite">
            <svg xmlns="http://www.w3.org/2000/svg"
                class="heart-icon h-5 w-5 transition-transform duration-300 ease-in-out group-active/heart:scale-75
                        {{ $isFav ? 'text-[#CC2E2E] fill-[#CC2E2E] scale-110' : 'text-gray-600 dark:text-gray-300 fill-none group-hover/heart:text-[#CC2E2E]' }}"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
            </svg>
        </button>

        <a href="{{ $service->public_url }}" class="flex flex-col md:flex-row w-full">
            <div class="relative w-full md:w-[320px] lg:w-[360px] bg-gray-100 dark:bg-[#121212] overflow-hidden">
                <div class="aspect-[4/3] md:aspect-[5/4] lg:aspect-[4/3]">
                    <img src="{{ $service->main_image_url }}"
                        class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                        alt="{{ $service->title }}"
                        @if($loop->index < 2) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                        width="480" height="360">
                </div>

                <span class="absolute bottom-3 left-3 bg-black/70 text-white text-[10px] px-2.5 py-1 rounded-md font-bold uppercase backdrop-blur-md border border-white/10 shadow-lg">
                    {{ $service->category->name }}
                </span>
            </div>

            <div class="flex-1 p-4 md:p-5 flex flex-col gap-3">
                <div>
                    <h3 class="text-base md:text-xl font-bold text-gray-900 dark:text-[#F2F2F2] uppercase tracking-tight line-clamp-2 leading-snug group-hover:text-[#CC2E2E] transition-colors"
                        title="{{ $service->title }}">
                        {{ $service->title }}
                    </h3>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                        {{ $locationLabel }}
                    </p>
                </div>

                <div class="flex flex-wrap items-center gap-3 text-xs md:text-sm text-gray-600 dark:text-gray-300">
                    <span class="inline-flex items-center gap-1">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#CC2E2E] opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        {{ $locationLabel }}
                    </span>
                    <span class="opacity-80">Vizualizări: {{ $service->views ?? 0 }}</span>
                </div>

                <div class="mt-auto flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 border-t border-gray-100 dark:border-[#333333] pt-4">
                    <div>
                        @if(!empty($service->price_value))
                            <div class="flex items-baseline gap-1">
                                <span class="text-xl md:text-2xl font-bold text-gray-900 dark:text-white">
                                    {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                                </span>
                                @if($service->price_type === 'negotiable')
                                    <span class="text-gray-500 dark:text-gray-400 text-xs font-normal">Neg.</span>
                                @endif
                            </div>
                        @else
                            <span class="text-lg font-bold text-[#CC2E2E]">Cere ofertă</span>
                        @endif
                    </div>

                    <span class="text-xs text-gray-500 dark:text-[#A1A1AA] whitespace-nowrap">
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
        </a>
    </div>
@empty
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
