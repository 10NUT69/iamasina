@props(['items' => [], 'markLastCurrent' => true])

@php
    $markLastCurrent = filter_var($markLastCurrent, FILTER_VALIDATE_BOOLEAN);

    $crumbs = collect($items)
        ->map(static function ($item) {
            $item = is_array($item) ? $item : (array) $item;

            return [
                'label' => trim((string) ($item['label'] ?? $item['name'] ?? '')),
                'url' => $item['url'] ?? $item['href'] ?? $item['item'] ?? null,
            ];
        })
        ->filter(static fn ($item) => $item['label'] !== '')
        ->values();

    $count = $crumbs->count();
@endphp

@if($count)
    <nav {{ $attributes->merge(['class' => 'min-w-0 overflow-x-auto no-scrollbar']) }} aria-label="Breadcrumb">
        <ol class="flex min-w-max items-center whitespace-nowrap text-[13px] font-semibold">
            @foreach($crumbs as $crumb)
                @php
                    $isCurrent = $markLastCurrent && $loop->last;
                    $clipPath = $loop->first
                        ? 'polygon(0 0, calc(100% - 9px) 0, 100% 50%, calc(100% - 9px) 100%, 0 100%)'
                        : 'polygon(0 0, calc(100% - 9px) 0, 100% 50%, calc(100% - 9px) 100%, 0 100%, 9px 50%)';
                    $segmentPadding = $loop->first ? 'pl-2 pr-4' : 'pl-4 pr-4';
                    $segmentClass = $segmentPadding . ' inline-flex h-7 max-w-[11rem] items-center bg-[#EEF2F8] text-[#172033] transition hover:bg-[#E5EBF4] active:bg-[#DCE4EF] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C81424]/20 dark:bg-[#1F2937] dark:text-gray-100 dark:hover:bg-[#273449] sm:max-w-[14rem]';
                    $currentClass = 'inline-flex h-7 max-w-[12rem] items-center pl-2.5 text-[#172033] dark:text-gray-100 sm:max-w-[18rem]';
                @endphp

                <li class="{{ $loop->first ? '' : '-ml-[7px]' }}">
                    @if($isCurrent)
                        @if($crumb['url'])
                            <a href="{{ $crumb['url'] }}" class="{{ $currentClass }} transition hover:text-[#C81424] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C81424]/20" aria-current="page">
                                <span class="truncate">{{ $crumb['label'] }}</span>
                            </a>
                        @else
                            <span class="{{ $currentClass }}" aria-current="page">
                                <span class="truncate">{{ $crumb['label'] }}</span>
                            </span>
                        @endif
                    @elseif($crumb['url'])
                        <a href="{{ $crumb['url'] }}" class="{{ $segmentClass }}" style="clip-path: {{ $clipPath }};">
                            <span class="truncate">{{ $crumb['label'] }}</span>
                        </a>
                    @else
                        <span class="{{ $segmentClass }}" style="clip-path: {{ $clipPath }};">
                            <span class="truncate">{{ $crumb['label'] }}</span>
                        </span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
