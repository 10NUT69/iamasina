@props([
    'name',
    'label',
    'placeholder' => null,
    'options' => [],
    'groups' => null,
    'selected' => null,
    'disabled' => false,
    'required' => false,
    'searchable' => true,
    'inputAutocomplete' => 'nope',
    'id' => null,
    'optionValue' => 'id',
    'optionLabel' => null,
    'optionSlug' => 'slug',
])

@php
    $id = $id ?: \Illuminate\Support\Str::slug($name, '-') . '-combobox';
    $selectedValue = old($name, $selected);
    $selectedValue = $selectedValue === null ? '' : (string) $selectedValue;
    $placeholder = $placeholder ?: $label;
    $listboxId = $id . '-listbox';
    $inputId = $id . '-search';

    $readValue = function ($item, $key, $fallback = null) {
        if (!$key) {
            return $fallback;
        }

        return data_get($item, $key, $fallback);
    };

    $normaliseOption = function ($item) use ($optionValue, $optionLabel, $optionSlug, $readValue) {
        $labelKey = $optionLabel ?: (data_get($item, 'label') !== null ? 'label' : (data_get($item, 'nume') !== null ? 'nume' : 'name'));
        $value = $readValue($item, 'value', null);
        $value = $value === null ? $readValue($item, $optionValue, '') : $value;
        $label = $readValue($item, 'label', null);
        $label = $label === null ? $readValue($item, $labelKey, '') : $label;
        $slug = $readValue($item, 'slug', null);
        $slug = $slug === null ? $readValue($item, $optionSlug, '') : $slug;

        return [
            'value' => (string) $value,
            'label' => (string) $label,
            'slug' => (string) ($slug ?? ''),
            'name' => (string) ($readValue($item, 'name', $label) ?? $label),
        ];
    };

    $renderedGroups = collect($groups ?: [
        [
            'label' => null,
            'options' => $options,
        ],
    ])->map(function ($group) use ($normaliseOption) {
        return [
            'label' => data_get($group, 'label'),
            'options' => collect(data_get($group, 'options', []))->map($normaliseOption)->values(),
        ];
    })->filter(fn ($group) => $group['options']->isNotEmpty())->values();
@endphp

<div
    {{ $attributes->merge(['class' => 'ia-combobox']) }}
    data-combobox
    data-combobox-label="{{ $label }}"
    data-combobox-placeholder="{{ $placeholder }}"
    data-combobox-searchable="{{ $searchable ? 'true' : 'false' }}"
>
    <input
        type="hidden"
        id="{{ $id }}"
        name="{{ $name }}"
        value="{{ $selectedValue }}"
        data-combobox-value
        data-combobox-label="{{ $label }}"
        @if($required) required @endif
        @if($disabled) disabled @endif
    >

    <div class="ia-combobox__control" data-combobox-control>
        <span class="ia-combobox__floating" data-combobox-floating>{{ $label }}</span>
        <input
            id="{{ $inputId }}"
            type="text"
            class="ia-combobox__input"
            placeholder="{{ $placeholder }}"
            autocomplete="{{ $inputAutocomplete }}"
            role="combobox"
            aria-autocomplete="list"
            aria-expanded="false"
            aria-controls="{{ $listboxId }}"
            @if(!$searchable) readonly @endif
            @if($disabled) disabled @endif
            data-combobox-input
        >
        <button type="button" class="ia-combobox__clear" aria-label="Șterge {{ $label }}" data-combobox-clear hidden>&times;</button>
        <button type="button" class="ia-combobox__toggle" aria-label="Deschide {{ $label }}" tabindex="-1" data-combobox-toggle>
            <span aria-hidden="true"></span>
        </button>
    </div>

    <div id="{{ $listboxId }}" class="ia-combobox__listbox" role="listbox" data-combobox-listbox>
        @foreach($renderedGroups as $group)
            <div class="ia-combobox__group" data-combobox-group>
                @if($group['label'])
                    <div class="ia-combobox__group-label">{{ $group['label'] }}</div>
                @endif

                @foreach($group['options'] as $option)
                    <button
                        type="button"
                        class="ia-combobox__option"
                        role="option"
                        data-combobox-option
                        data-value="{{ $option['value'] }}"
                        data-label="{{ $option['label'] }}"
                        data-name="{{ $option['name'] }}"
                        data-slug="{{ $option['slug'] }}"
                        aria-selected="{{ $selectedValue !== '' && $selectedValue === $option['value'] ? 'true' : 'false' }}"
                    >
                        {{ $option['label'] }}
                    </button>
                @endforeach
            </div>
        @endforeach
    </div>
</div>
