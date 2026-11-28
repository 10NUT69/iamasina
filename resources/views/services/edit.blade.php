@extends('layouts.app')

@section('title', 'Editează anunț - ' . $service->title)

@section('content')

@php
    /**
     * IMPORTANT (fără “carpeală”):
     * Controller-ul pentru edit trebuie să trimită ACELEAȘI variabile ca la create:
     * $brands, $colors, $colorOpts, $bodies, $fuels, $transmissions, $tractiuni,
     * $counties, $normePoluare, $carData, (și dacă ai categoria fixă auto) $autoCategoryId.
     *
     * În plus, $service trebuie să aibă câmpurile noi:
     * brand_id, model_id, an_fabricatie, caroserie_id, combustibil_id, cutie_viteze_id, tractiune_id,
     * culoare_id, culoare_opt_id, vin, km, putere, capacitate_cilindrica, norma_poluare_id, numar_usi, numar_locuri,
     * importata, avariata, filtru_particule, title, description, price_value, currency, price_type, phone, county_id, images
     */

    // ID-uri preselectate (prioritate: old() -> DB)
    $savedBrandId = old('brand_id', $service->brand_id ?: $service->generation?->model?->brand?->id);
    $savedModelId = old('model_id', $service->model_id ?: $service->generation?->model?->id);
    $savedYear    = old('an_fabricatie', $service->an_fabricatie);

    // fallback text (compatibilitate veche – ca în create)
    $savedBrandText = old('brand', $service->brand ?? '');
    $savedModelText = old('model', $service->model ?? '');

    $savedBrandIdForQuery = $savedBrandId ? (int) $savedBrandId : null;
    $savedModelOptions = $savedBrandIdForQuery
        ? \App\Models\CarModel::query()
            ->where('car_brand_id', $savedBrandIdForQuery)
            ->ordered()
            ->get(['id', 'name', 'slug'])
        : collect();

    if ($savedModelId && ! $savedModelOptions->contains(fn ($model) => (string) $model->id === (string) $savedModelId)) {
        $currentModelOption = \App\Models\CarModel::query()
            ->find($savedModelId, ['id', 'name', 'slug']);

        if ($currentModelOption) {
            $savedModelOptions = $savedModelOptions
                ->push($currentModelOption)
                ->sortBy(fn ($model) => mb_strtolower((string) $model->name))
                ->values();
        }
    }

    $savedYearInt = is_numeric($savedYear) ? (int) $savedYear : null;
    $yearStart = $savedYearInt ? min(1990, $savedYearInt) : 1990;
    $yearEnd = $savedYearInt ? max((int) now()->year, $savedYearInt) : (int) now()->year;
    $yearOptions = collect(range($yearEnd, $yearStart))
        ->map(fn ($year) => ['value' => $year, 'label' => (string) $year]);

    // pills / select-uri
    $savedColorId     = old('culoare_id', $service->culoare_id);
    $savedColorOptId  = old('culoare_opt_id', $service->culoare_opt_id);

    $savedBodyId      = old('caroserie_id', $service->caroserie_id);
    $savedFuelId      = old('combustibil_id', $service->combustibil_id);
    $savedTransId     = old('cutie_viteze_id', $service->cutie_viteze_id);
    $savedTractiuneId = old('tractiune_id', $service->tractiune_id);

    $savedVin         = old('vin', $service->vin);
    $savedKm          = old('km', $service->km);
    $savedPutere      = old('putere', $service->putere);
    $savedCil         = old('capacitate_cilindrica', $service->capacitate_cilindrica);

    $savedNormaId     = old('norma_poluare_id', $service->norma_poluare_id);
    $savedDoors       = old('numar_usi', $service->numar_usi);
    $savedSeats       = old('numar_locuri', $service->numar_locuri);

    $savedTitle       = old('title', $service->title);
    $savedDesc        = old('description', $service->description);

    $savedPriceValue  = old('price_value', $service->price_value);
    $savedCurrency    = old('currency', $service->currency ?: 'EUR');
    $savedPriceType   = old('price_type', $service->price_type ?: 'fixed');

    $savedPhone       = old('phone', $service->phone);
    $savedCountyId    = old('county_id', $service->county_id);
    $savedLocalityId  = old('locality_id', $service->locality_id);

    // categorie (dacă la create e hidden autoCategoryId, aici păstrăm categoria existentă)
    $savedCategoryId  = old('category_id', $service->category_id ?? ($autoCategoryId ?? null));

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
    $brandComboboxGroups = [
        ['label' => 'Populare', 'options' => $popularBrands],
        ['label' => 'A-Z', 'options' => $alphabeticalBrands],
    ];
@endphp

<div class="max-w-[1536px] mx-auto mt-8 mb-20 px-4 sm:px-6 lg:px-8">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Editează anunțul
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Modifică detaliile în 3 pași.</p>
        </div>

        {{-- STEPPER --}}
        <div class="flex items-center gap-2 bg-white dark:bg-[#1E1E1E] p-1.5 rounded-full shadow-sm border border-gray-100 dark:border-[#333]">
            <div id="step-dot-1" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-[#C81424] text-white transition-all">1</div>
            <div class="w-8 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div id="step-dot-2" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all">2</div>
            <div class="w-8 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div id="step-dot-3" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all">3</div>
        </div>
    </div>

    <form action="{{ route('services.update', $service->id) }}" method="POST" enctype="multipart/form-data" id="wizardForm">
        @csrf
        @method('PUT')

        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-xl border border-gray-100 dark:border-[#2A2A2A] overflow-hidden min-h-[500px] flex flex-col relative">

            {{-- ================= PASUL 1: SPECIFICAȚII ================= --}}
            <div class="step-content p-6 md:p-8 animate-fade-in" data-step="1">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-[#C81424]">🚙</span> Detalii Vehicul
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    {{-- STÂNGA: SELECTOARE CASCADATE --}}
                    <div class="lg:col-span-5 space-y-5">
                        <div class="bg-gray-50 dark:bg-[#252525] p-5 rounded-xl border border-gray-100 dark:border-[#333]">
                            <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-3 block">Identificare</label>

                            <div class="space-y-4">
                                {{-- Brand (FK) + fallback text --}}
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🏢</span>
                                    <input type="hidden" name="brand" id="brandText" value="{{ $savedBrandText }}">

                                    <x-combobox
                                        id="brandSelect"
                                        name="brand_id"
                                        label="Marca"
                                        placeholder="Marca"
                                        :groups="$brandComboboxGroups"
                                        option-label="name"
                                        :selected="$savedBrandId"
                                        :required="true"
                                    />
                                </div>

                                {{-- Model (FK) + fallback text --}}
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🚘</span>
                                    <input type="hidden" name="model" id="modelText" value="{{ $savedModelText }}">

                                    <x-combobox
                                        id="modelSelect"
                                        name="model_id"
                                        label="Model"
                                        placeholder="Model"
                                        :options="$savedModelOptions"
                                        :selected="$savedModelId"
                                        :disabled="!$savedBrandId"
                                        :required="true"
                                    />
                                </div>

                                {{-- Year --}}
                                <div class="relative group">
                                    <x-combobox
                                        id="yearSelect"
                                        name="an_fabricatie"
                                        label="An"
                                        placeholder="An"
                                        :options="$yearOptions"
                                        :selected="$savedYear"
                                        :disabled="!$savedModelId"
                                        :required="true"
                                    />
                                </div>
                            </div>
                        </div>

                        {{-- Categorie (dacă e fixă la create, o poți lăsa hidden; aici o păstrăm hidden ca în create) --}}
                        @if(!is_null($savedCategoryId))
                            <input type="hidden" name="category_id" value="{{ $savedCategoryId }}">
                        @endif
                    </div>

                    {{-- DREAPTA: SELECT CULOARE + PILLS --}}
                    <div class="lg:col-span-7 space-y-6">

                        {{-- CULOARE + FINISAJ --}}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
                            {{-- SELECT CULOARE --}}
                            <div>
                                <x-combobox
                                    id="inputColor"
                                    name="culoare_id"
                                    label="Culoare"
                                    placeholder="Culoare"
                                    :options="$colors"
                                    option-label="nume"
                                    :selected="$savedColorId"
                                    :required="true"
                                />
                            </div>

                            {{-- BUTOANE FINISAJ --}}
                            <div>
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Finisaj</label>
                                <input type="hidden" name="culoare_opt_id" id="inputColorOpt" value="{{ $savedColorOptId ?: '' }}">

                                <div class="flex flex-wrap gap-2">
                                    @foreach($colorOpts as $opt)
                                        <button type="button"
                                                onclick="selectPill('inputColorOpt', '{{ $opt->id }}', this)"
                                                class="pill-btn px-4 py-2 rounded-lg border border-gray-200 dark:border-[#333]
                                                       text-xs font-medium text-gray-600 dark:text-gray-300
                                                       hover:border-[#C81424] hover:text-[#C81424] transition-all
                                                       bg-white dark:bg-[#252525] {{ (string)$savedColorOptId === (string)$opt->id ? 'selected' : '' }}">
                                            {{ $opt->nume }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- CAROSERIE --}}
                        <div>
                            <x-combobox
                                id="inputBodyType"
                                name="caroserie_id"
                                label="Tip caroserie"
                                placeholder="Tip caroserie"
                                :options="$bodies"
                                option-label="nume"
                                :selected="$savedBodyId"
                            />
                            <p id="err-body" class="text-red-500 text-xs mt-1 hidden">Selectează caroseria.</p>
                        </div>

                        {{-- COMBUSTIBIL --}}
                        <div>
                            <x-combobox
                                id="inputFuel"
                                name="combustibil_id"
                                label="Combustibil"
                                placeholder="Combustibil"
                                :options="$fuels"
                                option-label="nume"
                                :selected="$savedFuelId"
                            />
                            <p id="err-fuel" class="text-red-500 text-xs mt-1 hidden">Alege combustibil.</p>
                        </div>

                        {{-- TRANSMISIE --}}
                        <div>
                            <x-combobox
                                id="inputTrans"
                                name="cutie_viteze_id"
                                label="Transmisie"
                                placeholder="Transmisie"
                                :options="$transmissions"
                                option-label="nume"
                                :selected="$savedTransId"
                            />
                            <p id="err-trans" class="text-red-500 text-xs mt-1 hidden">Alege transmisia.</p>
                        </div>

                        {{-- TRACTIUNE --}}
                        <div>
                            <x-combobox
                                id="inputTractiune"
                                name="tractiune_id"
                                label="Tracțiune"
                                placeholder="Tracțiune"
                                :options="$tractiuni"
                                option-label="nume"
                                :selected="$savedTractiuneId"
                            />
                            <p id="err-tractiune" class="text-red-500 text-xs mt-1 hidden">Alege tracțiunea.</p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ================= PASUL 2: ISTORIC & DESCRIERE ================= --}}
            <div class="step-content p-6 md:p-8 hidden opacity-0" data-step="2">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-[#C81424]">📝</span> Detalii Tehnice & Istoric
                </h2>

                {{-- VIN CODE --}}
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Serie Șasiu (VIN)</label>
                    <input type="text" name="vin" value="{{ $savedVin }}" maxlength="17"
                           class="w-full pl-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                  bg-white dark:bg-[#252525] text-gray-900 dark:text-white uppercase font-mono">
                    <p class="text-xs text-gray-400 mt-1">Recomandat pentru verificare.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Rulaj (km)</label>
                        <div class="relative">
                            <input type="number" name="km" value="{{ $savedKm }}"
                                   class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                          bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all font-mono">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Putere</label>
                        <div class="relative">
                            <input type="number" name="putere" value="{{ $savedPutere }}"
                                   class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                          bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all font-mono"
                                   required>
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">CP</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Capacitate</label>
                        <div class="relative">
                            <input type="number" name="capacitate_cilindrica" value="{{ $savedCil }}"
                                   class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                          bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all font-mono">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">cm³</span>
                        </div>
                    </div>
                </div>

                {{-- NORMA POLUARE --}}
                <div class="mb-6">
                    <x-combobox
                        id="inputNormaPoluare"
                        name="norma_poluare_id"
                        label="Norma poluare"
                        placeholder="Norma poluare"
                        :options="$normePoluare"
                        option-label="nume"
                        :selected="$savedNormaId"
                    />
                </div>

                {{-- USI + LOCURI --}}
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <x-combobox
                            id="inputDoors"
                            name="numar_usi"
                            label="Număr uși"
                            placeholder="Număr uși"
                            :options="collect([2, 3, 4, 5])->map(fn ($usi) => ['value' => $usi, 'label' => (string) $usi])"
                            :selected="$savedDoors"
                            :searchable="false"
                        />
                    </div>

                    <div>
                        <x-combobox
                            id="inputSeats"
                            name="numar_locuri"
                            label="Număr locuri"
                            placeholder="Număr locuri"
                            :options="collect(range(2, 9))->map(fn ($locuri) => ['value' => $locuri, 'label' => (string) $locuri])"
                            :selected="$savedSeats"
                            :searchable="false"
                        />
                    </div>
                </div>

                {{-- CHECKBOX-URI --}}
                <div class="grid grid-cols-2 gap-4 mb-6 lg:grid-cols-4">
                    @foreach(\App\Models\Service::FEATURE_OPTIONS as $name => $label)
                        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="{{ $name }}" value="1" @checked(old($name, $service->{$name}))
                                   class="rounded border-gray-300 dark:border-[#404040] text-[#C81424] focus:ring-[#C81424]">
                            {{ $label }}
                        </label>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Titlu Anunț</label>
                        <input type="text" name="title" value="{{ $savedTitle }}" maxlength="90" data-character-counter data-character-counter-target="edit-title-counter" placeholder="Maxim 90 caractere"
                               class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-[#444]
                                      focus:border-[#C81424] outline-none text-gray-900 dark:text-white font-medium"
                               required>
                        <p class="mt-1 text-right text-xs font-semibold text-slate-400"><span id="edit-title-counter">90</span> caractere ramase</p>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Descriere</label>
                        <textarea name="description" rows="8" maxlength="10000" data-character-counter data-character-counter-target="edit-description-counter"
                                  class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-[#444]
                                         focus:border-[#C81424] outline-none text-sm text-gray-900 dark:text-white resize-none"
                                  required>{{ $savedDesc }}</textarea>
                        <p class="mt-1 text-right text-xs font-semibold text-slate-400"><span id="edit-description-counter">10.000</span> caractere ramase</p>
                    </div>
                </div>
            </div>

            {{-- ================= PASUL 3: FINALIZARE ================= --}}
            <div class="step-content p-6 md:p-8 hidden opacity-0" data-step="3">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-[#C81424]">📸</span> Galerie & Preț
                </h2>

                {{-- DRAG & DROP FOTO NOI --}}
                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Adaugă imagini noi</label>
                    <div class="relative w-full group">
                        <input type="file" id="imageInput" multiple accept="image/jpeg,image/png,image/webp" aria-label="Adaugă poze la galerie" aria-describedby="imageCapacity" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer disabled:cursor-not-allowed z-20">
                        <div class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 dark:border-[#444] rounded-xl bg-gray-50 dark:bg-[#252525] group-hover:bg-[#fff4f5] dark:group-hover:bg-[#2a2a2a] group-hover:border-[#C81424] transition-all">
                            <div class="p-3 bg-white dark:bg-[#333] rounded-full shadow-sm mb-2">
                                <svg class="w-6 h-6 text-[#C81424]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <p id="imagePickerPrompt" class="text-sm font-semibold text-gray-700 dark:text-gray-300">Click sau trage poze aici</p>
                            <p id="imageCapacity" class="mt-1 px-2 text-center text-sm font-semibold text-slate-700 dark:text-slate-200" role="status"></p>
                            <p class="text-xs text-gray-400 mt-1">Maxim {{ \App\Support\ServiceImageStorage::MAX_IMAGES }} imagini în total, {{ \App\Support\ServiceImageStorage::MAX_IMAGE_MIB }} MB fiecare. Poți seta poza principală.</p>
                        </div>
                    </div>
                    <input type="hidden" name="gallery_revision" value="{{ $galleryRevision }}">
                    <div id="temporaryImageInputs"></div>
                    <p class="mt-3 text-xs text-gray-600 dark:text-gray-300">Folosește săgețile pentru a schimba ordinea. Prima poză este coperta. Modificările se aplică la salvare.</p>
                    <p id="imageSelectionNotice" role="status" class="mt-3 rounded-lg bg-slate-100 px-4 py-3 text-sm text-slate-700 empty:hidden dark:bg-[#333] dark:text-slate-200"></p>
                    <p id="imageError" class="hidden mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"></p>
                    <div id="previewContainer" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3 mt-4"></div>
                </div>

                <hr class="border-gray-100 dark:border-[#333] mb-8">

                {{-- PRET SI CONTACT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    {{-- PRET --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Preț Vânzare</label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" name="price_value" step="0.01" value="{{ $savedPriceValue }}"
                                       class="w-full pl-4 pr-16 py-3 rounded-xl bg-gray-50 dark:bg-[#252525] border-none text-2xl font-bold text-gray-900 dark:text-white outline-none ring-1 ring-gray-200 dark:ring-[#444] focus:ring-2 focus:ring-green-500"
                                       required>
                                <div class="absolute right-2 top-2 bottom-2 flex bg-white dark:bg-[#333] rounded-lg p-1 border border-gray-100 dark:border-[#444]">
                                    <input type="hidden" name="currency" id="inputCurrency" value="{{ $savedCurrency }}">
                                    <button type="button" onclick="selectPill('inputCurrency', 'EUR', this)" class="pill-btn px-2 text-xs font-bold rounded text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors {{ $savedCurrency === 'EUR' ? 'selected' : '' }}">EUR</button>
                                    <button type="button" onclick="selectPill('inputCurrency', 'RON', this)" class="pill-btn px-2 text-xs font-bold rounded text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors {{ $savedCurrency === 'RON' ? 'selected' : '' }}">RON</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <input type="hidden" name="price_type" id="inputPriceType" value="{{ $savedPriceType }}">
                            <button type="button" onclick="selectPill('inputPriceType', 'negotiable', this)" class="pill-btn px-3 py-1 text-xs border rounded-md border-gray-200 dark:border-[#444] text-gray-500 dark:text-gray-400 hover:border-green-500 hover:text-green-600 transition-colors {{ $savedPriceType === 'negotiable' ? 'selected' : '' }}">Negociabil</button>
                            <button type="button" onclick="selectPill('inputPriceType', 'fixed', this)" class="pill-btn px-3 py-1 text-xs border rounded-md border-gray-200 dark:border-[#444] text-gray-500 dark:text-gray-400 hover:border-green-500 hover:text-green-600 transition-colors {{ $savedPriceType === 'fixed' ? 'selected' : '' }}">Preț Fix</button>
                        </div>
                    </div>

                    {{-- CONTACT --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Telefon</label>
                            <input type="text" name="phone" value="{{ $savedPhone }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white"
                                   required>
                        </div>
                        <div>
                            <x-combobox
                                id="county-select"
                                name="county_id"
                                label="Județ"
                                placeholder="Județ"
                                :options="$counties"
                                option-label="name"
                                :selected="$savedCountyId"
                                :required="true"
                            />
                        </div>
                        <div>
                            <x-combobox
                                id="locality-select"
                                name="locality_id"
                                label="Localitate"
                                placeholder="Localitate"
                                :options="[]"
                                :selected="$savedLocalityId"
                                :disabled="true"
                                :required="true"
                            />
                        </div>
                    </div>
                </div>

                {{-- AUTH SECTION --}}
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-[#333] flex items-center gap-3 text-green-600 dark:text-green-400 font-medium">
                     <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                     Autentificat ca: {{ auth()->user()->name }}
                </div>
            </div>

            {{-- FOOTER NAVIGARE --}}
            <div class="mt-auto p-5 bg-gray-50 dark:bg-[#252525] border-t border-gray-100 dark:border-[#333] flex justify-between items-center">
                <button type="button" id="prevBtn" class="hidden text-gray-500 dark:text-gray-400 font-bold text-sm px-4 py-2 hover:bg-gray-200 dark:hover:bg-[#333] rounded-lg transition-colors">
                    ← Înapoi
                </button>
                <button type="button" id="nextBtn" class="ml-auto bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm px-8 py-3 rounded-xl hover:shadow-lg hover:scale-[1.02] transition-all">
                    Continuă
                </button>
                <div id="publishActions" class="hidden ml-auto flex max-w-sm flex-col items-end gap-2 pl-2">
                    <p id="photoUploadStatus" class="text-right text-sm font-semibold text-slate-700 dark:text-slate-200" role="status"></p>
                    <p id="photoGalleryTotal" class="hidden text-right text-sm font-bold text-slate-900 dark:text-white"></p>
                    <progress id="photoUploadProgress" class="hidden h-2 w-full accent-[#C81424]" aria-label="Progresul încărcării pozelor" max="100" value="0"></progress>
                    <button type="submit" id="submitBtn" aria-describedby="photoUploadStatus photoGalleryTotal" class="bg-[#C81424] text-white font-bold text-sm px-8 py-3 rounded-xl transition-all hover:bg-[#94111B] disabled:cursor-not-allowed disabled:opacity-60" disabled>
                        Salvează Modificările
                    </button>
                </div>
            </div>

        </div>
    </form>
</div>

<div id="submitOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-[#1E1E1E]">
        <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-[#C81424]"></div>
        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Se salvează anunțul</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Te rugăm să aștepți până se finalizează salvarea modificărilor.
        </p>
    </div>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .pill-btn.selected { background-color: #C81424; color: white !important; border-color: #C81424; }
    button[onclick*="inputPriceType"].selected, button[onclick*="inputCurrency"].selected { background-color: #10B981 !important; color: white !important; border-color: #10B981 !important; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    window.iaCombobox?.init(document);

    // ================== 1) WIZARD NAV ==================
    let currentStep = 1;
    const totalSteps = 3;
    const steps = document.querySelectorAll('.step-content');
    const dots = document.querySelectorAll('.step-dot');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const publishActions = document.getElementById('publishActions');
    const submitBtn = document.getElementById('submitBtn');
    const wizardForm = document.getElementById('wizardForm');
    const countySelect = document.getElementById('county-select');
    const localitySelect = document.getElementById('locality-select');
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const presetLocalityId = "{{ $savedLocalityId }}";
    const serverValidationErrors = @json($errors->messages());

    function scrollToStepStart(stepEl) {
        if (!stepEl) return;

        const target = stepEl.querySelector('h2') || stepEl;
        const top = target.getBoundingClientRect().top + window.pageYOffset - 110;

        window.scrollTo({
            top: Math.max(0, top),
            behavior: 'smooth',
        });
    }

    function updateStep({ scrollToStart = false } = {}) {
        let activeStep = null;

        steps.forEach(s => {
            if (parseInt(s.dataset.step) === currentStep) {
                activeStep = s;
                s.classList.remove('hidden');
                setTimeout(() => s.classList.remove('opacity-0'), 50);
            } else {
                s.classList.add('hidden', 'opacity-0');
            }
        });

        dots.forEach((dot, idx) => {
            if (idx + 1 === currentStep) {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-[#C81424] text-white transition-all scale-110 shadow-md';
                dot.innerHTML = String(idx + 1);
            } else if (idx + 1 < currentStep) {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-green-500 text-white transition-all';
                dot.innerHTML = '✓';
            } else {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all';
                dot.innerHTML = String(idx + 1);
            }
        });

        prevBtn.classList.toggle('hidden', currentStep === 1);
        nextBtn.classList.toggle('hidden', currentStep === totalSteps);
        publishActions.classList.toggle('hidden', currentStep !== totalSteps);

        if (scrollToStart && activeStep) {
            setTimeout(() => scrollToStepStart(activeStep), 80);
        }
    }

    function resetLocalities() {
        if (!localitySelect) return;
        if (window.iaCombobox?.get(localitySelect)) {
            window.iaCombobox.setOptions(localitySelect, [], '');
            window.iaCombobox.disable(localitySelect);
            return;
        }
        localitySelect.innerHTML = '<option value="">Selectează orașul</option>';
        localitySelect.disabled = true;
    }

    function populateLocalities(localities, selectedId) {
        if (!localitySelect) return;
        if (window.iaCombobox?.get(localitySelect)) {
            window.iaCombobox.setOptions(localitySelect, localities.map((locality) => ({
                value: locality.id,
                label: locality.name,
                name: locality.name,
                slug: locality.slug,
            })), selectedId || '');
            window.iaCombobox.enable(localitySelect);
            return;
        }
        localitySelect.innerHTML = '<option value="">Selectează orașul</option>';
        localities.forEach(locality => {
            const option = document.createElement('option');
            option.value = locality.id;
            option.textContent = locality.name;
            if (String(selectedId) === String(locality.id)) {
                option.selected = true;
            }
            localitySelect.appendChild(option);
        });
        localitySelect.disabled = false;
    }

    async function loadLocalities(countyId, selectedId = null) {
        if (!countyId) {
            resetLocalities();
            return;
        }

        try {
            const response = await fetch(`${localityBaseUrl}/${countyId}`);
            const data = await response.json();
            populateLocalities(data, selectedId);
        } catch (error) {
            console.error(error);
            resetLocalities();
        }
    }

    // ================== 2) PILL SELECTORS ==================
    window.selectPill = function(inputId, value, btnElement) {
        document.getElementById(inputId).value = value;

        const parent = btnElement.parentElement;
        const siblings = parent.querySelectorAll('.pill-btn');
        siblings.forEach(el => el.classList.remove('selected'));
        btnElement.classList.add('selected');

        if (inputId === 'inputBodyType') clearCustomPillError(inputId, 'err-body');
        if (inputId === 'inputFuel') clearCustomPillError(inputId, 'err-fuel');
        if (inputId === 'inputTrans') clearCustomPillError(inputId, 'err-trans');
        if (inputId === 'inputTractiune') clearCustomPillError(inputId, 'err-tractiune');
    };

    [
        ['inputBodyType', 'err-body'],
        ['inputFuel', 'err-fuel'],
        ['inputTrans', 'err-trans'],
        ['inputTractiune', 'err-tractiune'],
    ].forEach(([inputId, errorId]) => {
        document.getElementById(inputId)?.addEventListener('change', () => {
            clearCustomPillError(inputId, errorId);
        });
    });

    // ================== 3) VALIDATION (ca în create) ==================
    function ensureStepErrorBox(stepEl) {
        let box = stepEl.querySelector('[data-step-error]');
        if (box) return box;

        box = document.createElement('div');
        box.dataset.stepError = 'true';
        box.className = 'hidden mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700 dark:border-red-900/50 dark:bg-red-950/30 dark:text-red-100';
        box.setAttribute('role', 'alert');
        box.setAttribute('tabindex', '-1');

        const heading = stepEl.querySelector('h2');
        if (heading) {
            heading.insertAdjacentElement('afterend', box);
        } else {
            stepEl.prepend(box);
        }

        return box;
    }

    function hideStepError(stepEl) {
        const box = stepEl?.querySelector('[data-step-error]');
        if (!box) return;
        box.textContent = '';
        box.classList.add('hidden');
    }

    function showStepError(stepEl, messages) {
        const box = ensureStepErrorBox(stepEl);
        const uniqueMessages = [...new Set(messages.filter(Boolean))];
        box.textContent = uniqueMessages.length > 1
            ? `Verifică aceste câmpuri: ${uniqueMessages.join(', ')}.`
            : (uniqueMessages[0] || 'Verifică câmpurile evidențiate înainte să continui.');
        box.classList.remove('hidden');
    }

    function fieldLabel(input) {
        const fieldLabels = {
            brand_id: 'marca',
            model_id: 'modelul',
            an_fabricatie: 'anul',
            culoare_id: 'culoarea',
            km: 'rulajul',
            putere: 'puterea',
            title: 'titlul anunțului',
            description: 'descrierea',
            price_value: 'prețul',
            phone: 'telefonul',
            county_id: 'județul',
            locality_id: 'orașul',
        };

        if (fieldLabels[input.name]) {
            return fieldLabels[input.name];
        }

        const explicitLabel = input.id ? document.querySelector(`label[for="${input.id}"]`) : null;
        let nearbyLabel = null;
        let parent = input.parentElement;

        while (parent && !parent.classList.contains('step-content')) {
            nearbyLabel = Array.from(parent.children).find(child => child.tagName === 'LABEL');
            if (nearbyLabel) break;
            parent = parent.parentElement;
        }

        const selectPlaceholder = input.tagName === 'SELECT' ? input.options?.[0]?.textContent : '';
        const text = (explicitLabel?.textContent || nearbyLabel?.textContent || selectPlaceholder || input.placeholder || input.name || 'acest câmp')
            .replace(/\s+/g, ' ')
            .trim();

        return text.replace(/[:*]+$/, '').toLowerCase();
    }

    function validationMessageFor(input) {
        const label = fieldLabel(input);

        if (input.validity?.valueMissing || !input.value) {
            return `Completează ${label}.`;
        }

        if (input.validity?.typeMismatch && input.type === 'email') {
            return 'Introdu o adresă de email validă.';
        }

        return input.validationMessage || `Verifică ${label}.`;
    }

    function markInvalidInput(input) {
        input.classList.add('ring-2', 'ring-red-500', 'border-red-500');
        input.setAttribute('aria-invalid', 'true');
        window.iaCombobox?.setInvalid(input, true);

        const clear = () => {
            const isComboboxValue = input.matches('[data-combobox-value]');
            const isValid = isComboboxValue
                ? (!input.required || !!input.value)
                : input.checkValidity();

            if (isValid) {
                input.classList.remove('ring-2', 'ring-red-500', 'border-red-500');
                input.removeAttribute('aria-invalid');
                window.iaCombobox?.setInvalid(input, false);
            }
        };

        input.addEventListener('input', clear);
        input.addEventListener('change', clear);
    }

    function clearCustomPillError(inputId, errorId) {
        const input = document.getElementById(inputId);
        const wrapper = input?.closest('div');
        input?.classList.remove('ring-2', 'ring-red-500', 'border-red-500');
        input?.removeAttribute('aria-invalid');
        window.iaCombobox?.setInvalid(input, false);
        wrapper?.querySelectorAll('.pill-btn').forEach(btn => {
            btn.classList.remove('ring-2', 'ring-red-500', 'border-red-500');
        });
        document.getElementById(errorId)?.classList.add('hidden');
    }

    function markCustomPillInvalid(inputId, errorId, message) {
        const input = document.getElementById(inputId);
        const wrapper = input?.closest('div');
        const error = document.getElementById(errorId);

        error?.classList.remove('hidden');
        const buttons = wrapper?.querySelectorAll('.pill-btn') || [];

        buttons.forEach(btn => {
            btn.classList.add('ring-2', 'ring-red-500', 'border-red-500');
        });

        if (!buttons.length && input) {
            input.classList.add('ring-2', 'ring-red-500', 'border-red-500');
            input.setAttribute('aria-invalid', 'true');
            window.iaCombobox?.setInvalid(input, true);
        }

        return {
            target: wrapper?.querySelector('.pill-btn') || window.iaCombobox?.get(input)?.root || input || error,
            message,
        };
    }

    function scrollToValidationTarget(target) {
        if (!target) return;

        const comboState = window.iaCombobox?.get(target);
        const visibleTarget = comboState?.root || target;

        visibleTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            const focusTarget = comboState?.input || visibleTarget;
            if (typeof focusTarget.focus === 'function') {
                focusTarget.focus({ preventScroll: true });
            }
        }, 350);
    }

    function validateStep(stepNumber, { reveal = true } = {}) {
        const stepEl = document.querySelector(`.step-content[data-step="${stepNumber}"]`);
        if (!stepEl) return { valid: true, messages: [], firstTarget: null };

        const invalidItems = [];
        const inputs = stepEl.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            const isComboboxValue = input.matches('[data-combobox-value]');
            if (input.disabled || (input.type === 'hidden' && !isComboboxValue)) return;

            const isInvalidCombobox = isComboboxValue && input.required && !input.value;
            if (isInvalidCombobox || !input.checkValidity()) {
                const message = validationMessageFor(input);
                invalidItems.push({ target: input, message });
                if (reveal) markInvalidInput(input);
            }
        });

        if (stepNumber === 1) {
            [
                ['inputBodyType', 'err-body', 'Selectează caroseria'],
                ['inputFuel', 'err-fuel', 'Alege combustibilul'],
                ['inputTrans', 'err-trans', 'Alege transmisia'],
                ['inputTractiune', 'err-tractiune', 'Alege tracțiunea'],
            ].forEach(([inputId, errorId, message]) => {
                if (!document.getElementById(inputId)?.value) {
                    const item = reveal
                        ? markCustomPillInvalid(inputId, errorId, message + '.')
                        : { target: document.getElementById(inputId), message: message + '.' };
                    invalidItems.push(item);
                }
            });
        }

        if (invalidItems.length) {
            if (reveal) showStepError(stepEl, invalidItems.map(item => item.message));
            return {
                valid: false,
                messages: invalidItems.map(item => item.message),
                firstTarget: invalidItems[0]?.target || stepEl,
            };
        }

        if (reveal) hideStepError(stepEl);
        return { valid: true, messages: [], firstTarget: null };
    }

    function validateCurrentStep() {
        const result = validateStep(currentStep);
        if (!result.valid) {
            scrollToValidationTarget(result.firstTarget);
        }

        return result.valid;
    }

    function validateAllSteps() {
        for (let stepNumber = 1; stepNumber <= totalSteps; stepNumber++) {
            const result = validateStep(stepNumber, { reveal: stepNumber === currentStep });

            if (!result.valid) {
                currentStep = stepNumber;
                updateStep();

                setTimeout(() => {
                    const visibleResult = validateStep(stepNumber);
                    scrollToValidationTarget(visibleResult.firstTarget);
                }, 80);

                return false;
            }
        }

        return true;
    }

    function findFieldForError(fieldName) {
        const normalizedName = String(fieldName).replace(/\.\d+$/, '');
        if (normalizedName === 'gallery_order' || normalizedName === 'gallery_revision') {
            return document.getElementById('imageInput');
        }
        const allFields = wizardForm ? Array.from(wizardForm.querySelectorAll('[name]')) : [];

        return allFields.find(field => {
            return field.name === normalizedName || field.name === `${normalizedName}[]`;
        }) || null;
    }

    function applyServerValidationErrors(errors) {
        const entries = Object.entries(errors || {});
        if (!entries.length) return false;

        const firstField = findFieldForError(entries[0][0]);
        const firstStep = firstField?.closest('.step-content');
        currentStep = Number(firstStep?.dataset.step || 1);
        updateStep();

        setTimeout(() => {
            const stepEl = document.querySelector(`.step-content[data-step="${currentStep}"]`);
            const messages = entries.flatMap(([, fieldMessages]) => Array.isArray(fieldMessages) ? fieldMessages : [String(fieldMessages)]);
            const target = firstField || stepEl;

            if (stepEl) {
                showStepError(stepEl, messages);
            }
            if (firstField) {
                markInvalidInput(firstField);
            }
            scrollToValidationTarget(target);
        }, 100);

        return true;
    }

    nextBtn.addEventListener('click', () => {
        if (validateCurrentStep()) {
            currentStep++;
            updateStep({ scrollToStart: true });
        }
    });
    prevBtn.addEventListener('click', () => { currentStep--; updateStep({ scrollToStart: true }); });

    if (countySelect) {
        countySelect.addEventListener('change', () => {
            loadLocalities(countySelect.value);
        });
    }

    if (countySelect && countySelect.value) {
        loadLocalities(countySelect.value, presetLocalityId);
    } else {
        resetLocalities();
    }

    // ================== 4) CASCADING SELECTS (ID-uri) ==================
    const carData = @json($carData ?? []);
    const brandSel = document.getElementById('brandSelect');
    const modelSel = document.getElementById('modelSelect');
    const yearSel = document.getElementById('yearSelect');

    const savedBrandId = @json($savedBrandId);
    const savedModelId = @json($savedModelId);
    const savedYear    = @json($savedYear);

    function populateYears(start, end, selectedYear = null) {
        const selectedYearNumber = Number(selectedYear);
        const hasSelectedYear = Number.isFinite(selectedYearNumber) && selectedYearNumber > 0;
        const finalStart = hasSelectedYear ? Math.min(start, selectedYearNumber) : start;
        const finalEnd = hasSelectedYear ? Math.max(end || new Date().getFullYear(), selectedYearNumber) : (end || new Date().getFullYear());
        const years = [];
        for (let i = finalEnd; i >= finalStart; i--) {
            years.push({ value: i, label: String(i) });
        }

        if (window.iaCombobox?.get(yearSel)) {
            window.iaCombobox.setOptions(yearSel, years, selectedYear || '');
            window.iaCombobox.enable(yearSel);
            return;
        }

        yearSel.innerHTML = '<option value="">An</option>';
        yearSel.disabled = false;
        years.forEach((year) => {
            const selected = selectedYear && String(selectedYear) === String(year.value) ? 'selected' : '';
            yearSel.innerHTML += `<option value="${year.value}" ${selected}>${year.label}</option>`;
        });
    }

    function resetSelect(el, defaultText) {
        if (window.iaCombobox?.get(el)) {
            window.iaCombobox.setOptions(el, [], '');
            window.iaCombobox.disable(el);
            return;
        }
        el.innerHTML = `<option value="">${defaultText}</option>`;
        el.disabled = true;
        el.value = "";
    }

    function selectedOptionMeta(el) {
        const comboOption = window.iaCombobox?.selectedOption(el);
        if (comboOption) return comboOption;

        return el?.selectedOptions?.[0] || null;
    }

    function syncBrandText() {
        const brandOption = selectedOptionMeta(brandSel);
        document.getElementById('brandText').value = brandOption?.name || brandOption?.label || brandOption?.dataset?.name || '';
    }

    function syncModelText() {
        const modelOption = selectedOptionMeta(modelSel);
        document.getElementById('modelText').value = modelOption?.name || modelOption?.label || modelOption?.dataset?.name || '';
    }

    function populateModels(brandId, selectedModelId = '') {
        resetSelect(modelSel, 'Model');
        resetSelect(yearSel, 'An');

        const models = (brandId && carData[brandId])
            ? carData[brandId].map((model) => ({
                value: model.id,
                label: model.name,
                name: model.name,
                slug: model.slug,
            }))
            : [];

        if (!models.length) return;

        if (window.iaCombobox?.get(modelSel)) {
            window.iaCombobox.setOptions(modelSel, models, selectedModelId || '');
            window.iaCombobox.enable(modelSel);
            return;
        }

        modelSel.disabled = false;
        models.forEach(m => {
            const selected = selectedModelId && String(selectedModelId) === String(m.value) ? 'selected' : '';
            modelSel.innerHTML += `<option value="${m.value}" data-name="${m.name}" ${selected}>${m.label}</option>`;
        });
    }

    brandSel.addEventListener('change', function() {
        const brandId = this.value;

        syncBrandText();
        populateModels(brandId);
    });

    modelSel.addEventListener('change', function() {
        const brandId = brandSel.value;
        const modelId = this.value;

        syncModelText();

        resetSelect(yearSel, 'An');

        if (brandId && modelId && carData[brandId]) {
            populateYears(1990, new Date().getFullYear());
        }
    });

    // ---- INIT preselect (brand->model->year) ----
    function initCascadeFromSaved() {
        if (!savedBrandId) return;

        if (window.iaCombobox?.get(brandSel)) {
            window.iaCombobox.setValue(brandSel, savedBrandId, { dispatch: false });
        } else {
            brandSel.value = String(savedBrandId);
        }
        syncBrandText();

        resetSelect(modelSel, 'Model');
        resetSelect(yearSel, 'An');

        if (carData[savedBrandId]) {
            populateModels(savedBrandId, savedModelId);

            if (savedModelId) {
                syncModelText();
                populateYears(1990, new Date().getFullYear(), savedYear);
            }
        }
    }

    @include('services.partials.service-image-uploads')

    // init
    initCascadeFromSaved();
    if (!applyServerValidationErrors(serverValidationErrors)) {
        updateStep();
    }
});
</script>

@endsection
