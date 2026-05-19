@forelse($services as $service)
    @php
        $isFav = auth()->check() && $service->isFavoritedBy(auth()->user());
        $loc = $service->locality->name ?? '';
        $jud = $service->county->name ?? '';
        $locationLabel = $loc ? "$loc, $jud" : $jud;
        $listingTitle = $service->title ?: trim(($service->brandRel->name ?? '') . ' ' . ($service->modelRel->name ?? ''));
        $img = $service->card_image_url;
        $price = $service->price_value ? number_format($service->price_value, 0, ',', '.') : null;
        $priceBadge = $price ? ($service->price_type === 'negotiable' ? 'NEGOCIABIL' : 'PRET FIX') : null;
        $priceBadgeClass = 'bg-[#C81424] text-white';
        $yearLabel = $service->an_fabricatie ?: '-';
        $kmLabel = $service->km ? number_format($service->km, 0, '.', '.') . ' km' : '-';
        $fuelLabel = $service->combustibil->nume ?? '-';
        $gearboxLabel = $service->cutieViteze->nume ?? '-';
        $dateLabel = $service->listing_date_label;
    @endphp

    <article data-service-card class="group relative bg-white dark:bg-[#1E1E1E] border border-gray-100 dark:border-[#333] rounded-2xl overflow-hidden hover:shadow-[0_8px_30px_rgb(0,0,0,0.12)] hover:-translate-y-1 transition-all duration-300 flex flex-col h-full">
        <a href="{{ $service->public_url }}" class="block relative w-full aspect-[4/3] overflow-hidden bg-gray-200 dark:bg-[#121212]">
            <img src="{{ $img }}" alt="{{ $listingTitle }}" class="w-full h-full object-cover transform group-hover:scale-105 transition-transform duration-700 ease-in-out" loading="lazy">
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-transparent to-transparent opacity-80"></div>

            <div class="absolute bottom-3 left-3 sm:bottom-4 sm:left-4 flex flex-col items-start z-10">
                @if($price)
                    <span class="text-base sm:text-xl font-black text-white drop-shadow-lg tracking-tight">{{ $price }} <span class="text-[10px] sm:text-sm font-bold">{{ $service->currency }}</span></span>
                    <span class="text-[9px] uppercase font-bold {{ $priceBadgeClass }} px-1.5 py-0.5 rounded shadow-sm mt-1">{{ $priceBadge }}</span>
                @else
                    <span class="text-base sm:text-lg font-bold text-white drop-shadow-md">La cerere</span>
                @endif
            </div>

            <button onclick="event.preventDefault(); toggleHeart(this, {{ $service->id }})" class="absolute top-2 right-2 sm:top-3 sm:right-3 p-1.5 sm:p-2 rounded-full bg-black/20 hover:bg-white backdrop-blur-md transition-all duration-300 group/heart shadow-lg">
                <svg class="w-4 h-4 sm:w-5 sm:h-5 transition-colors {{ $isFav ? 'text-[#C81424] fill-[#C81424]' : 'text-white fill-none group-hover/heart:text-[#C81424]' }}" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                </svg>
            </button>
        </a>

        <div class="p-3 sm:p-5 flex-1 flex flex-col">
            <a href="{{ $service->public_url }}" class="block mb-4">
                <h3 class="text-sm sm:text-lg font-bold text-gray-900 dark:text-white leading-tight line-clamp-2 sm:line-clamp-1 group-hover:text-[#C81424] transition-colors">{{ $listingTitle }}</h3>
                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 mt-1 truncate uppercase tracking-wide">{{ $service->putere ?? '-' }} CP - {{ $service->capacitate_cilindrica ? number_format($service->capacitate_cilindrica, 0, '', '.') : '-' }} cmc</p>
            </a>

            <div class="grid grid-cols-4 gap-x-1 sm:gap-x-2 mb-4">
                <div class="spec-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3M5 11h14M7 21h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                    <span>{{ $yearLabel }}</span>
                </div>
                <div class="spec-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 15a2 2 0 100-4 2 2 0 000 4z" /><path stroke-linecap="round" stroke-linejoin="round" d="M12 13l3.8-4.4M5.6 18.4a9 9 0 1112.8 0" /><path stroke-linecap="round" stroke-linejoin="round" d="M7.5 14H6m12 0h-1.5M12 6.5V5" /></svg>
                    <span>{{ $kmLabel }}</span>
                </div>
                <div class="spec-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 21V5a2 2 0 012-2h5a2 2 0 012 2v16M6 21h11M9 8h5M16 7h1.5L20 9.5V18a2 2 0 01-2 2h-1" /><path stroke-linecap="round" stroke-linejoin="round" d="M20 10h-3" /></svg>
                    <span>{{ $fuelLabel }}</span>
                </div>
                <div class="spec-pill">
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M7 5v14m10-14v14M7 12h10M12 5v7" /><circle cx="7" cy="5" r="2" /><circle cx="17" cy="5" r="2" /><circle cx="7" cy="19" r="2" /><circle cx="17" cy="19" r="2" /></svg>
                    <span>{{ $gearboxLabel }}</span>
                </div>
            </div>

            <div class="mt-auto pt-4 border-t border-gray-100 dark:border-[#333] flex items-start justify-between gap-3 text-xs text-gray-500 dark:text-gray-400">
                <div class="flex min-w-0 flex-1 flex-col gap-1.5">
                    <div class="flex min-w-0 items-start">
                        <svg class="w-3.5 h-3.5 mr-1.5 mt-0.5 shrink-0 text-[#C81424]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span class="leading-snug">{{ $locationLabel }}</span>
                    </div>
                    <div class="flex items-center text-[11px] text-gray-400 dark:text-gray-500">
                        <svg class="w-3 h-3 mr-1 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <span>{{ $dateLabel }}</span>
                    </div>
                </div>
                <a href="{{ $service->public_url }}"
                   class="inline-flex shrink-0 items-center gap-1 font-bold text-[#C81424] transition hover:text-[#94111B] hover:underline dark:text-red-300 dark:hover:text-red-200"
                   aria-label="Vezi detalii pentru {{ $listingTitle }}">
                    Vezi detalii &rarr;
                </a>
            </div>
        </div>
    </article>
@empty
    <div class="col-span-full py-20 text-center">
        <div class="inline-flex items-center justify-center w-20 h-20 rounded-full bg-gray-50 dark:bg-[#252525] mb-4">
            <svg class="w-10 h-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" /></svg>
        </div>
        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Nu am gasit anunturi</h3>
        <p class="text-gray-500 dark:text-gray-400 mt-2">Incearca sa resetezi filtrele pentru a vedea mai multe rezultate.</p>
    </div>
@endforelse
