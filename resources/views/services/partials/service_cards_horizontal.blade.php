@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
        $locationLabel = $service->locality->name ?? $service->city ?? $service->county->name;
        $fuelLabel = $service->combustibil->nume ?? null;
        $gearLabel = $service->cutieViteze->nume ?? null;
        $bodyLabel = $service->caroserie->nume ?? null;
        $sellerType = $service->user->user_type ?? null;
        $sellerLabel = $sellerType === 'dealer' ? 'Parc auto' : ($sellerType === 'individual' ? 'Proprietar' : null);
        $specs = array_filter([
            $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, ',', '.') . ' cm³' : null,
            $service->putere ? $service->putere . ' CP' : null,
            $fuelLabel,
            $service->an_fabricatie,
        ]);
    @endphp

    <div class="card-animate relative bg-white dark:bg-[#1E1E1E] rounded-xl border border-gray-200 dark:border-[#333333] shadow-sm
                hover:shadow-xl dark:hover:shadow-none dark:hover:border-[#555555]
                transition-all duration-300 overflow-hidden group flex flex-col">

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

        <a href="{{ $service->public_url }}" class="flex flex-col md:flex-row w-full">
            <div class="relative md:w-2/5 lg:w-[38%] w-full aspect-[4/3] md:aspect-auto bg-gray-100 dark:bg-[#121212] overflow-hidden">
                <img src="{{ $service->main_image_url }}"
                    class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105"
                    alt="{{ $service->title }}"
                    @if($loop->index < 2) loading="eager" fetchpriority="high" @else loading="lazy" @endif
                    width="520" height="390">

                <span class="absolute bottom-2 left-2 md:bottom-3 md:left-3 bg-black/70 text-white text-[9px] md:text-xs px-2 py-0.5 md:px-2.5 md:py-1 rounded-md font-bold uppercase backdrop-blur-md border border-white/10 shadow-lg">
                    {{ $service->category->name ?? 'Auto' }}
                </span>
            </div>

            <div class="flex-1 p-4 md:p-5 flex flex-col gap-3">
                <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base md:text-lg font-bold text-gray-900 dark:text-[#F2F2F2] leading-snug line-clamp-2 group-hover:text-[#CC2E2E] transition-colors"
                            title="{{ $service->title }}">
                            {{ $service->title }}
                        </h3>

                        @if(!empty($specs))
                            <p class="mt-1 text-xs md:text-sm text-gray-500 dark:text-gray-400 line-clamp-1">
                                {{ implode(' • ', $specs) }}
                            </p>
                        @endif

                        <div class="mt-3 flex flex-wrap gap-3 text-xs md:text-sm text-gray-600 dark:text-gray-300">
                            @if($service->km)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                                    </svg>
                                    {{ number_format($service->km, 0, ',', '.') }} km
                                </span>
                            @endif

                            @if($fuelLabel)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                    </svg>
                                    {{ $fuelLabel }}
                                </span>
                            @endif

                            @if($gearLabel)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6l4 2" />
                                    </svg>
                                    {{ $gearLabel }}
                                </span>
                            @endif

                            @if($service->an_fabricatie)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    {{ $service->an_fabricatie }}
                                </span>
                            @endif

                            @if($bodyLabel)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 20l-5.447-2.724A2 2 0 013 15.382V7.618a2 2 0 011.553-1.894L9 3m0 17l6-3m-6 3V3m6 14l5.447-2.724A2 2 0 0021 12.382V4.618a2 2 0 00-1.553-1.894L15 3m0 14V3m0 14l-6-3" />
                                    </svg>
                                    {{ $bodyLabel }}
                                </span>
                            @endif
                        </div>

                        <div class="mt-3 flex flex-wrap gap-3 text-xs text-gray-500 dark:text-gray-400">
                            <span class="inline-flex items-center gap-1" title="{{ $locationLabel }}">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#CC2E2E] opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                {{ $locationLabel }}
                            </span>

                            @if($sellerLabel)
                                <span class="inline-flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a4 4 0 00-4-4h-1m-4 6H2v-2a4 4 0 014-4h1m6-4a4 4 0 10-8 0 4 4 0 008 0zm6 4a3 3 0 10-6 0 3 3 0 006 0z" />
                                    </svg>
                                    {{ $sellerLabel }}
                                </span>
                            @endif

                            <span class="inline-flex items-center gap-1">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 22a10 10 0 100-20 10 10 0 000 20z" />
                                </svg>
                                @if($service->created_at->isToday())
                                    Azi
                                @elseif($service->created_at->isYesterday())
                                    Ieri
                                @else
                                    {{ $service->created_at->format('d.m.Y') }}
                                @endif
                            </span>
                        </div>
                    </div>

                    <div class="shrink-0 text-right">
                        @if(!empty($service->price_value))
                            <div class="text-lg md:text-2xl font-bold text-gray-900 dark:text-white">
                                {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                            </div>
                            @if($service->price_type === 'negotiable')
                                <div class="text-xs text-gray-500 dark:text-gray-400">Negociabil</div>
                            @endif
                        @else
                            <div class="text-base md:text-lg font-bold text-[#CC2E2E]">Cere ofertă</div>
                        @endif
                    </div>
                </div>

                <div class="mt-auto pt-3 border-t border-gray-100 dark:border-[#333333] flex items-center justify-between text-xs text-gray-500 dark:text-gray-400">
                    <div class="flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        {{ $service->views ?? 0 }} vizualizări
                    </div>

                    <span class="inline-flex items-center gap-1 text-[#CC2E2E] font-semibold">
                        Vezi anunțul
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                        </svg>
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
