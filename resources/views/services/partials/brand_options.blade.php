@php
    $selectedBrandId = isset($selectedBrandId) ? (string) $selectedBrandId : null;
    $popularLabel = $popularLabel ?? 'Populare';
    $allLabel = $allLabel ?? 'A-Z';
    $brandItems = collect($brands ?? []);
    $popularBrands = $brandItems->where('is_popular', true);
    $regularBrands = $brandItems->where('is_popular', false);
@endphp

@if($popularBrands->isNotEmpty())
    <optgroup label="{{ $popularLabel }}">
        @foreach($popularBrands as $brand)
            <option
                value="{{ $brand->id }}"
                data-name="{{ $brand->name }}"
                data-slug="{{ $brand->slug }}"
                @selected($selectedBrandId !== null && (string) $brand->id === $selectedBrandId)
            >
                {{ $brand->name }}
            </option>
        @endforeach
    </optgroup>
@endif

@if($regularBrands->isNotEmpty())
    <optgroup label="{{ $allLabel }}">
        @foreach($regularBrands as $brand)
            <option
                value="{{ $brand->id }}"
                data-name="{{ $brand->name }}"
                data-slug="{{ $brand->slug }}"
                @selected($selectedBrandId !== null && (string) $brand->id === $selectedBrandId)
            >
                {{ $brand->name }}
            </option>
        @endforeach
    </optgroup>
@endif
