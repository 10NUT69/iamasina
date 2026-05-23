@php
    $selectedBrandId = isset($selectedBrandId) ? (string) $selectedBrandId : null;
    $popularLabel = $popularLabel ?? 'Populare';
    $allLabel = $allLabel ?? 'A-Z';
    $brandItems = collect($brands ?? []);
    $popularBrands = $brandItems->where('is_popular', true);
    $alphabeticalBrands = $brandItems->sortBy(function ($brand) {
        $name = (string) ($brand->name ?? '');
        $slug = (string) ($brand->slug ?? '');
        $normalizedName = mb_strtolower(\Illuminate\Support\Str::ascii($name));
        $isOtherBrand = in_array($slug, ['altul', 'alta-marca'], true)
            || in_array($normalizedName, ['alta marca', 'altul'], true);

        return sprintf('%d-%s', $isOtherBrand ? 1 : 0, $normalizedName);
    });
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

@if($alphabeticalBrands->isNotEmpty())
    <optgroup label="{{ $allLabel }}">
        @foreach($alphabeticalBrands as $brand)
            <option
                value="{{ $brand->id }}"
                data-name="{{ $brand->name }}"
                data-slug="{{ $brand->slug }}"
                @selected($selectedBrandId !== null && (string) $brand->id === $selectedBrandId && !($brand->is_popular ?? false))
            >
                {{ $brand->name }}
            </option>
        @endforeach
    </optgroup>
@endif
