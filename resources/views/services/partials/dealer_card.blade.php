@php
    $cleanDealerCardValue = static function ($value) {
        $value = trim((string) $value);

        return $value === '' ? null : trim(preg_replace('/\s+/', ' ', $value));
    };

    $formatDealerLocationPart = static function ($value) use ($cleanDealerCardValue) {
        $value = $cleanDealerCardValue($value);

        if ($value === null) {
            return null;
        }

        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    };

    $dealerName = $cleanDealerCardValue($dealer->company_name ?: $dealer->name) ?: 'Parc auto';
    $dealerCity = $formatDealerLocationPart($dealer->city);
    $dealerCounty = $formatDealerLocationPart($dealer->county);
    $dealerLocationCity = $dealerCity;

    if ($dealerCity && $dealerCounty) {
        $cityParts = array_values(array_filter(array_map('trim', explode(',', $dealerCity))));
        $lastCityPart = end($cityParts);

        if ($lastCityPart && \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($lastCityPart)) === \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii($dealerCounty))) {
            array_pop($cityParts);
            $dealerLocationCity = $cleanDealerCardValue(implode(', ', $cityParts)) ?: $dealerCity;
        }
    }

    $dealerLocation = $dealerLocationCity && $dealerCounty && $dealerLocationCity !== $dealerCounty
        ? $dealerLocationCity . ', ' . $dealerCounty
        : ($dealerLocationCity ?: ($dealerCounty ?: 'România'));
    $dealerLogoUrl = $dealer->dealer_logo_url ?? null;
    $dealerUrl = $dealer->dealer_public_url ?: route('cars.index', ['seller_type' => 'dealer']);
    $initials = collect(preg_split('/\s+/', $dealerName, -1, PREG_SPLIT_NO_EMPTY))
        ->take(2)
        ->map(fn ($part) => \Illuminate\Support\Str::upper(\Illuminate\Support\Str::substr($part, 0, 1)))
        ->implode('');
    $initials = $initials !== '' ? $initials : 'D';
@endphp

<a href="{{ $dealerUrl }}"
   class="group flex h-full min-h-[104px] flex-col justify-between rounded-lg border border-gray-200 bg-white p-3 shadow-sm transition duration-300 hover:-translate-y-0.5 hover:border-[#C81424]/30 hover:shadow-[0_12px_28px_rgba(15,23,42,0.10)] active:scale-[0.99] dark:border-[#333333] dark:bg-[#1E1E1E] dark:hover:border-red-500/30"
   aria-label="Vezi stocul dealerului {{ $dealerName }}">
    <span class="flex min-w-0 items-center gap-3">
        <span class="flex h-11 w-11 shrink-0 items-center justify-center overflow-hidden rounded-md border border-gray-200 text-sm font-black text-[#C81424] dark:border-[#333333] {{ $dealerLogoUrl ? 'bg-white p-1.5' : 'bg-gray-50 dark:bg-[#252525]' }}">
            @if($dealerLogoUrl)
                <img src="{{ $dealerLogoUrl }}" alt="Logo {{ $dealerName }}" class="h-full w-full object-contain object-center" loading="lazy">
            @else
                {{ $initials }}
            @endif
        </span>

        <span class="line-clamp-2 min-w-0 text-left text-sm font-black leading-tight text-gray-950 transition group-hover:text-[#C81424] dark:text-white sm:text-[15px]">
            {{ $dealerName }}
        </span>
    </span>

    <span class="mt-3 flex min-w-0 items-end justify-between gap-2">
        <span class="line-clamp-2 min-w-0 text-left text-[11px] font-bold leading-snug text-gray-500 dark:text-gray-400 sm:text-xs">
            {{ $dealerLocation }}
        </span>

        <span class="inline-flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-[#C81424] transition group-hover:bg-[#C81424] group-hover:text-white dark:text-red-300 dark:group-hover:bg-[#C81424] dark:group-hover:text-white" aria-hidden="true">
            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.3" d="M5 12h14m-6-6 6 6-6 6" />
            </svg>
        </span>
    </span>
</a>
