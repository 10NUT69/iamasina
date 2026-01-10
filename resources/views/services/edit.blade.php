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
     * brand_id, model_id, car_generation_id, an_fabricatie, caroserie_id, combustibil_id, cutie_viteze_id, tractiune_id,
     * culoare_id, culoare_opt_id, vin, km, putere, capacitate_cilindrica, norma_poluare_id, numar_usi, numar_locuri,
     * importata, avariata, filtru_particule, title, description, price_value, currency, price_type, phone, county_id, images
     */

    // ID-uri preselectate (prioritate: old() -> DB)
    $savedBrandId = old('brand_id', $service->brand_id);
    $savedModelId = old('model_id', $service->model_id);
    $savedGenId   = old('car_generation_id', $service->car_generation_id);
    $savedYear    = old('an_fabricatie', $service->an_fabricatie);

    // fallback text (compatibilitate veche – ca în create)
    $savedBrandText = old('brand', $service->brand ?? '');
    $savedModelText = old('model', $service->model ?? '');

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

    $savedImportata   = old('importata', $service->importata) ? 1 : 0;
    $savedAvariata    = old('avariata', $service->avariata) ? 1 : 0;
    $savedDPF         = old('filtru_particule', $service->filtru_particule) ? 1 : 0;

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

    // galerie
    $gallery = $service->images;
    if (is_null($gallery)) $gallery = [];
    if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
    if (!is_array($gallery)) $gallery = [];
    $gallery = array_values(array_filter($gallery));
@endphp

<div class="max-w-5xl mx-auto mt-8 mb-20 px-4 sm:px-6">

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
            <div id="step-dot-1" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-blue-600 text-white transition-all">1</div>
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
                    <span class="text-blue-500">🚙</span> Detalii Vehicul
                </h2>

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">

                    {{-- STÂNGA: SELECTOARE CASCADATE --}}
                    <div class="lg:col-span-5 space-y-5">
                        <div class="bg-gray-50 dark:bg-[#252525] p-5 rounded-xl border border-gray-100 dark:border-[#333]">
                            <label class="text-xs font-bold text-gray-500 uppercase mb-3 block">Identificare</label>

                            <div class="space-y-4">
                                {{-- Brand (FK) + fallback text --}}
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🏢</span>
                                    <input type="hidden" name="brand" id="brandText" value="{{ $savedBrandText }}">

                                    <select name="brand_id" id="brandSelect"
                                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                                   bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white
                                                   focus:ring-2 focus:ring-blue-500 outline-none transition-all appearance-none cursor-pointer"
                                            required>
                                        <option value="">Marca</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}"
                                                    data-name="{{ $brand->name }}"
                                                    @selected((string)$savedBrandId === (string)$brand->id)>
                                                {{ $brand->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Model (FK) + fallback text --}}
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🚘</span>
                                    <input type="hidden" name="model" id="modelText" value="{{ $savedModelText }}">

                                    <select name="model_id" id="modelSelect" disabled
                                            class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                                   bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white
                                                   disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] disabled:cursor-not-allowed
                                                   focus:ring-2 focus:ring-blue-500 outline-none transition-all appearance-none">
                                        <option value="">Model</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Generation --}}
                                    <div class="relative group">
                                        <select name="car_generation_id" id="generationSelect" disabled
                                                class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                                       bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white
                                                       disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] disabled:cursor-not-allowed
                                                       outline-none transition-all">
                                            <option value="">Gen</option>
                                        </select>
                                    </div>

                                    {{-- Year --}}
                                    <div class="relative group">
                                        <select name="an_fabricatie" id="yearSelect" disabled
                                                class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                                       bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white
                                                       disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] disabled:cursor-not-allowed
                                                       outline-none transition-all"
                                                required>
                                            <option value="">An</option>
                                        </select>
                                    </div>
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
                                <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Culoare</label>
                                <select name="culoare_id"
                                        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                               bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white outline-none focus:border-blue-500"
                                        required>
                                    <option value="">Alege Culoarea</option>
                                    @foreach($colors as $color)
                                        <option value="{{ $color->id }}" @selected((string)$savedColorId === (string)$color->id)>{{ $color->nume }}</option>
                                    @endforeach
                                </select>
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
                                                       hover:border-blue-500 hover:text-blue-500 transition-all
                                                       bg-white dark:bg-[#252525] {{ (string)$savedColorOptId === (string)$opt->id ? 'selected' : '' }}">
                                            {{ $opt->nume }}
                                        </button>
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        {{-- CAROSERIE --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Caroserie</label>
                            <input type="hidden" name="caroserie_id" id="inputBodyType" value="{{ $savedBodyId ?: '' }}">
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                @foreach($bodies as $body)
                                    <button type="button"
                                            onclick="selectPill('inputBodyType', '{{ $body->id }}', this)"
                                            class="pill-btn px-2 py-2 rounded-lg border border-gray-200 dark:border-[#333]
                                                   text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-blue-500 hover:text-blue-500
                                                   transition-all bg-white dark:bg-[#252525] {{ (string)$savedBodyId === (string)$body->id ? 'selected' : '' }}">
                                        {{ $body->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-body" class="text-red-500 text-xs mt-1 hidden">Selectează caroseria.</p>
                        </div>

                        {{-- COMBUSTIBIL --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Combustibil</label>
                            <input type="hidden" name="combustibil_id" id="inputFuel" value="{{ $savedFuelId ?: '' }}">
                            <div class="flex flex-wrap gap-2">
                                @foreach($fuels as $fuel)
                                    <button type="button"
                                            onclick="selectPill('inputFuel', '{{ $fuel->id }}', this)"
                                            class="pill-btn px-4 py-2 rounded-lg border border-gray-200 dark:border-[#333]
                                                   text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-blue-500 hover:text-blue-500
                                                   transition-all bg-white dark:bg-[#252525] {{ (string)$savedFuelId === (string)$fuel->id ? 'selected' : '' }}">
                                        {{ $fuel->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-fuel" class="text-red-500 text-xs mt-1 hidden">Alege combustibil.</p>
                        </div>

                        {{-- TRANSMISIE --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Transmisie</label>
                            <input type="hidden" name="cutie_viteze_id" id="inputTrans" value="{{ $savedTransId ?: '' }}">
                            <div class="flex gap-3">
                                @foreach($transmissions as $trans)
                                    <button type="button"
                                            onclick="selectPill('inputTrans', '{{ $trans->id }}', this)"
                                            class="pill-btn flex-1 py-2.5 rounded-lg border border-gray-200 dark:border-[#333]
                                                   text-sm font-medium bg-white dark:bg-[#252525] hover:border-blue-500 transition-all
                                                   flex items-center justify-center gap-2 {{ (string)$savedTransId === (string)$trans->id ? 'selected' : '' }}">
                                        {{ $trans->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-trans" class="text-red-500 text-xs mt-1 hidden">Alege transmisia.</p>
                        </div>

                        {{-- TRACTIUNE --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Tracțiune</label>
                            <input type="hidden" name="tractiune_id" id="inputTractiune" value="{{ $savedTractiuneId ?: '' }}">
                            <div class="flex flex-wrap gap-2">
                                @foreach($tractiuni as $tr)
                                    <button type="button"
                                            onclick="selectPill('inputTractiune', '{{ $tr->id }}', this)"
                                            class="pill-btn px-4 py-2 rounded-lg border border-gray-200 dark:border-[#333]
                                                   text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-blue-500 hover:text-blue-500
                                                   transition-all bg-white dark:bg-[#252525] {{ (string)$savedTractiuneId === (string)$tr->id ? 'selected' : '' }}">
                                        {{ $tr->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-tractiune" class="text-red-500 text-xs mt-1 hidden">Alege tracțiunea.</p>
                        </div>

                    </div>
                </div>
            </div>

            {{-- ================= PASUL 2: ISTORIC & DESCRIERE ================= --}}
            <div class="step-content p-6 md:p-8 hidden opacity-0" data-step="2">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-blue-500">📝</span> Detalii Tehnice & Istoric
                </h2>

                {{-- VIN CODE --}}
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1">Serie Șasiu (VIN)</label>
                    <input type="text" name="vin" value="{{ $savedVin }}" maxlength="17"
                           class="w-full pl-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                  bg-white dark:bg-[#252525] text-gray-900 dark:text-white uppercase font-mono">
                    <p class="text-xs text-gray-400 mt-1">Recomandat pentru verificare.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1">Rulaj (km)</label>
                        <div class="relative">
                            <input type="number" name="km" value="{{ $savedKm }}"
                                   class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                          bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono"
                                   required>
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1">Putere</label>
                        <div class="relative">
                            <input type="number" name="putere" value="{{ $savedPutere }}"
                                   class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                          bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono"
                                   required>
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">CP</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1">Capacitate</label>
                        <div class="relative">
                            <input type="number" name="capacitate_cilindrica" value="{{ $savedCil }}"
                                   class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                          bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-mono">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">cm³</span>
                        </div>
                    </div>
                </div>

                {{-- NORMA POLUARE --}}
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 uppercase mb-1">Normă poluare</label>
                    <select name="norma_poluare_id"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444]
                                   bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white">
                        <option value="">Alege norma</option>
                        @foreach($normePoluare as $norma)
                            <option value="{{ $norma->id }}" @selected((string)$savedNormaId === (string)$norma->id)>{{ $norma->nume }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- USI + LOCURI --}}
                <div class="grid grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1">Număr uși</label>
                        <select name="numar_usi" class="w-full px-4 py-2.5 rounded-lg border bg-white dark:bg-[#252525] dark:border-[#444] text-sm">
                            <option value="">—</option>
                            @foreach([2,3,4,5] as $usi)
                                <option value="{{ $usi }}" @selected((string)$savedDoors === (string)$usi)>{{ $usi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 uppercase mb-1">Număr locuri</label>
                        <select name="numar_locuri" class="w-full px-4 py-2.5 rounded-lg border bg-white dark:bg-[#252525] dark:border-[#444] text-sm">
                            <option value="">—</option>
                            @foreach(range(2,9) as $locuri)
                                <option value="{{ $locuri }}" @selected((string)$savedSeats === (string)$locuri)>{{ $locuri }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- CHECKBOX-URI --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="importata" value="1" @checked($savedImportata == 1)
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Importată
                    </label>

                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="avariata" value="1" @checked($savedAvariata == 1)
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Avariată
                    </label>

                    <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <input type="checkbox" name="filtru_particule" value="1" @checked($savedDPF == 1)
                               class="rounded border-gray-300 text-blue-600 focus:ring-blue-500">
                        Filtru particule
                    </label>
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Titlu Anunț</label>
                        <input type="text" name="title" value="{{ $savedTitle }}"
                               class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-[#444]
                                      focus:border-blue-500 outline-none text-gray-900 dark:text-white font-medium"
                               required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Descriere</label>
                        <textarea name="description" rows="8"
                                  class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-[#444]
                                         focus:border-blue-500 outline-none text-sm text-gray-900 dark:text-white resize-none"
                                  required>{{ $savedDesc }}</textarea>
                    </div>
                </div>
            </div>

            {{-- ================= PASUL 3: FINALIZARE ================= --}}
            <div class="step-content p-6 md:p-8 hidden opacity-0" data-step="3">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-blue-500">📸</span> Galerie & Preț
                </h2>

                {{-- IMAGINI EXISTENTE --}}
                @if(count($gallery))
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Imagini existente</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                            @foreach($gallery as $img)
                                <div class="relative group aspect-square rounded-lg overflow-hidden border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525]"
                                     id="server-img-{{ $loop->index }}">
                                    <img src="{{ asset('storage/services/' . $img) }}" class="w-full h-full object-cover">

                                    <div class="absolute top-2 left-2 bg-gray-900/60 backdrop-blur-sm text-white text-[10px] px-2 py-0.5 rounded pointer-events-none">
                                        Existent
                                    </div>

                                    <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                        <button type="button"
                                                onclick="deleteServerImage('{{ $img }}', {{ $service->id }}, 'server-img-{{ $loop->index }}')"
                                                class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg shadow-lg transition-all flex items-center gap-1 text-xs font-bold">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                      d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                            </svg>
                                            Șterge
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- DRAG & DROP FOTO NOI --}}
                <div class="mb-8">
                    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Adaugă imagini noi</label>
                    <div class="relative w-full group">
                        <input type="file" id="imageInput" name="images[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        <div class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 dark:border-[#444] rounded-xl bg-gray-50 dark:bg-[#252525] group-hover:bg-blue-50 dark:group-hover:bg-[#2a2a2a] group-hover:border-blue-400 transition-all">
                            <div class="p-3 bg-white dark:bg-[#333] rounded-full shadow-sm mb-2">
                                <svg class="w-6 h-6 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Click sau trage poze aici</p>
                            <p class="text-xs text-gray-400 mt-1">Maxim 10 imagini</p>
                        </div>
                    </div>
                    <div id="previewContainer" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-4"></div>
                </div>

                <hr class="border-gray-100 dark:border-[#333] mb-8">

                {{-- PRET SI CONTACT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    {{-- PRET --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 uppercase mb-2">Preț Vânzare</label>
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
                            <button type="button" onclick="selectPill('inputPriceType', 'negotiable', this)" class="pill-btn px-3 py-1 text-xs border rounded-md border-gray-200 dark:border-[#444] text-gray-500 hover:border-green-500 hover:text-green-600 transition-colors {{ $savedPriceType === 'negotiable' ? 'selected' : '' }}">Negociabil</button>
                            <button type="button" onclick="selectPill('inputPriceType', 'fixed', this)" class="pill-btn px-3 py-1 text-xs border rounded-md border-gray-200 dark:border-[#444] text-gray-500 hover:border-green-500 hover:text-green-600 transition-colors {{ $savedPriceType === 'fixed' ? 'selected' : '' }}">Preț Fix</button>
                        </div>
                    </div>

                    {{-- CONTACT --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Telefon</label>
                            <input type="text" name="phone" value="{{ $savedPhone }}"
                                   class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm"
                                   required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Județ</label>
                            <select id="county-select" name="county_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm" required>
                                <option value="">Alege județ</option>
                                @foreach ($counties as $county)
                                    <option value="{{ $county->id }}" @selected((string)$savedCountyId === (string)$county->id)>{{ $county->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1">Localitate</label>
                            <select id="locality-select" name="locality_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm" disabled>
                                <option value="">Selectează localitatea</option>
                            </select>
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
                <button type="button" id="prevBtn" class="hidden text-gray-500 font-bold text-sm px-4 py-2 hover:bg-gray-200 dark:hover:bg-[#333] rounded-lg transition-colors">
                    ← Înapoi
                </button>
                <button type="button" id="nextBtn" class="ml-auto bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm px-8 py-3 rounded-xl hover:shadow-lg hover:scale-[1.02] transition-all">
                    Continuă
                </button>
                <button type="submit" id="submitBtn" class="hidden ml-auto bg-blue-600 text-white font-bold text-sm px-8 py-3 rounded-xl hover:shadow-lg hover:shadow-blue-500/30 hover:scale-[1.02] transition-all">
                    Salvează Modificările
                </button>
            </div>

        </div>
    </form>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    .pill-btn.selected { background-color: #3B82F6; color: white !important; border-color: #3B82F6; }
    button[onclick*="inputPriceType"].selected, button[onclick*="inputCurrency"].selected { background-color: #10B981 !important; color: white !important; border-color: #10B981 !important; }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {

    // ================== 1) WIZARD NAV ==================
    let currentStep = 1;
    const totalSteps = 3;
    const steps = document.querySelectorAll('.step-content');
    const dots = document.querySelectorAll('.step-dot');
    const prevBtn = document.getElementById('prevBtn');
    const nextBtn = document.getElementById('nextBtn');
    const submitBtn = document.getElementById('submitBtn');
    const countySelect = document.getElementById('county-select');
    const localitySelect = document.getElementById('locality-select');
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const presetLocalityId = "{{ $savedLocalityId }}";

    function normalizeText(value) {
        return value
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .toLowerCase();
    }

    function attachDiacriticsSearch(selectEl) {
        if (!selectEl) return;
        let buffer = '';
        let timer = null;

        selectEl.addEventListener('keydown', (event) => {
            if (!event.key || event.key.length !== 1) return;
            buffer += event.key;
            const normalizedBuffer = normalizeText(buffer);

            const options = Array.from(selectEl.options);
            const match = options.find(option =>
                normalizeText(option.textContent || '').startsWith(normalizedBuffer)
            );

            if (match) {
                const prevValue = selectEl.value;
                selectEl.value = match.value;
                if (selectEl.value !== prevValue) {
                    selectEl.dispatchEvent(new Event('change'));
                }
            }

            if (timer) {
                clearTimeout(timer);
            }
            timer = setTimeout(() => {
                buffer = '';
            }, 600);
        });
    }

    function updateStep() {
        steps.forEach(s => {
            if (parseInt(s.dataset.step) === currentStep) {
                s.classList.remove('hidden');
                setTimeout(() => s.classList.remove('opacity-0'), 50);
            } else {
                s.classList.add('hidden', 'opacity-0');
            }
        });

        dots.forEach((dot, idx) => {
            if (idx + 1 === currentStep) {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-blue-600 text-white transition-all scale-110 shadow-md';
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
        if (currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    function resetLocalities() {
        if (!localitySelect) return;
        localitySelect.innerHTML = '<option value="">Selectează localitatea</option>';
        localitySelect.disabled = true;
    }

    function populateLocalities(localities, selectedId) {
        if (!localitySelect) return;
        localitySelect.innerHTML = '<option value="">Selectează localitatea</option>';
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

        if (inputId === 'inputBodyType') document.getElementById('err-body')?.classList.add('hidden');
        if (inputId === 'inputFuel') document.getElementById('err-fuel')?.classList.add('hidden');
        if (inputId === 'inputTrans') document.getElementById('err-trans')?.classList.add('hidden');
        if (inputId === 'inputTractiune') document.getElementById('err-tractiune')?.classList.add('hidden');
    }

    // ================== 3) VALIDATION (ca în create) ==================
    function validateCurrentStep() {
        let valid = true;
        const currentEl = document.querySelector(`.step-content[data-step="${currentStep}"]`);
        const inputs = currentEl.querySelectorAll('input[required], select[required], textarea[required]');

        inputs.forEach(inp => {
            if (!inp.value) {
                valid = false;
                inp.classList.add('ring-2', 'ring-red-500', 'border-red-500');
                inp.addEventListener('change', () => inp.classList.remove('ring-2', 'ring-red-500', 'border-red-500'), { once:true });
            }
        });

        if (currentStep === 1) {
            const genSel = document.getElementById('generationSelect');
            if (!genSel.disabled && !genSel.value) {
                valid = false;
                genSel.classList.add('ring-2', 'ring-red-500', 'border-red-500');
                genSel.addEventListener('change', () => genSel.classList.remove('ring-2', 'ring-red-500', 'border-red-500'), { once:true });
            }

            if (!document.getElementById('inputBodyType').value) { document.getElementById('err-body').classList.remove('hidden'); valid = false; }
            if (!document.getElementById('inputFuel').value) { document.getElementById('err-fuel').classList.remove('hidden'); valid = false; }
            if (!document.getElementById('inputTrans').value) { document.getElementById('err-trans').classList.remove('hidden'); valid = false; }
            if (!document.getElementById('inputTractiune').value) { document.getElementById('err-tractiune').classList.remove('hidden'); valid = false; }
        }

        return valid;
    }

    nextBtn.addEventListener('click', () => {
        if (validateCurrentStep()) {
            currentStep++;
            updateStep();
        }
    });
    prevBtn.addEventListener('click', () => { currentStep--; updateStep(); });

    if (countySelect) {
        countySelect.addEventListener('change', () => {
            loadLocalities(countySelect.value);
        });
    }
    attachDiacriticsSearch(countySelect);
    attachDiacriticsSearch(localitySelect);

    if (countySelect && countySelect.value) {
        loadLocalities(countySelect.value, presetLocalityId);
    } else {
        resetLocalities();
    }

    // ================== 4) CASCADING SELECTS (ID-uri) ==================
    const carData = @json($carData ?? []);
    const brandSel = document.getElementById('brandSelect');
    const modelSel = document.getElementById('modelSelect');
    const genSel = document.getElementById('generationSelect');
    const yearSel = document.getElementById('yearSelect');

    const savedBrandId = @json($savedBrandId);
    const savedModelId = @json($savedModelId);
    const savedGenId   = @json($savedGenId);
    const savedYear    = @json($savedYear);

    function populateYears(start, end, selectedYear = null) {
        yearSel.innerHTML = '<option value="">An</option>';
        yearSel.disabled = false;
        const finalEnd = end || new Date().getFullYear();
        for (let i = finalEnd; i >= start; i--) {
            const opt = document.createElement('option');
            opt.value = i;
            opt.textContent = i;
            if (selectedYear && String(selectedYear) === String(i)) opt.selected = true;
            yearSel.appendChild(opt);
        }
    }

    function resetSelect(el, defaultText) {
        el.innerHTML = `<option value="">${defaultText}</option>`;
        el.disabled = true;
        el.value = "";
    }

    function syncBrandText() {
        const brandName = brandSel.options[brandSel.selectedIndex]?.dataset?.name || '';
        document.getElementById('brandText').value = brandName;
    }

    function syncModelText() {
        const modelName = modelSel.options[modelSel.selectedIndex]?.dataset?.name || '';
        document.getElementById('modelText').value = modelName;
    }

    brandSel.addEventListener('change', function() {
        const brandId = this.value;

        syncBrandText();

        resetSelect(modelSel, 'Model');
        resetSelect(genSel, 'Gen');
        resetSelect(yearSel, 'An');

        if (brandId && carData[brandId]) {
            modelSel.disabled = false;
            carData[brandId].forEach(m => {
                modelSel.innerHTML += `<option value="${m.id}" data-name="${m.name}">${m.name}</option>`;
            });
        }
    });

    modelSel.addEventListener('change', function() {
        const brandId = brandSel.value;
        const modelId = this.value;

        syncModelText();

        resetSelect(genSel, 'Gen');
        resetSelect(yearSel, 'An');

        if (brandId && modelId && carData[brandId]) {
            const modelObj = carData[brandId].find(x => String(x.id) === String(modelId));
            const generations = modelObj?.generations || [];

            if (generations.length > 0) {
                genSel.disabled = false;
                genSel.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
                genSel.innerHTML = '<option value="">Gen</option>';

                generations.forEach(g => {
                    const option = document.createElement('option');
                    option.value = g.id;
                    option.text = `${g.name} (${g.start} - ${g.end || 'Prezent'})`;
                    option.dataset.start = g.start;
                    option.dataset.end = g.end || new Date().getFullYear();
                    genSel.appendChild(option);
                });
            } else {
                genSel.disabled = true;
                genSel.innerHTML = '<option value="">N/A</option>';
                genSel.classList.add('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');
                populateYears(1990, new Date().getFullYear(), savedYear);
            }
        }
    });

    genSel.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if (selected && selected.dataset.start) {
            populateYears(parseInt(selected.dataset.start), parseInt(selected.dataset.end), savedYear);
        }
    });

    // ---- INIT preselect (brand->model->gen->year) ----
    function initCascadeFromSaved() {
        if (!savedBrandId) return;

        brandSel.value = String(savedBrandId);
        syncBrandText();

        resetSelect(modelSel, 'Model');
        resetSelect(genSel, 'Gen');
        resetSelect(yearSel, 'An');

        if (carData[savedBrandId]) {
            modelSel.disabled = false;
            modelSel.innerHTML = '<option value="">Model</option>';
            carData[savedBrandId].forEach(m => {
                const opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name;
                opt.dataset.name = m.name;
                if (String(savedModelId) === String(m.id)) opt.selected = true;
                modelSel.appendChild(opt);
            });

            if (savedModelId) {
                syncModelText();

                const modelObj = carData[savedBrandId].find(x => String(x.id) === String(savedModelId));
                const generations = modelObj?.generations || [];

                if (generations.length > 0) {
                    genSel.disabled = false;
                    genSel.innerHTML = '<option value="">Gen</option>';

                    let foundGen = null;
                    generations.forEach(g => {
                        const opt = document.createElement('option');
                        opt.value = g.id;
                        opt.textContent = `${g.name} (${g.start} - ${g.end || 'Prezent'})`;
                        opt.dataset.start = g.start;
                        opt.dataset.end = g.end || new Date().getFullYear();
                        if (String(savedGenId) === String(g.id)) { opt.selected = true; foundGen = g; }
                        genSel.appendChild(opt);
                    });

                    if (foundGen) {
                        populateYears(parseInt(foundGen.start), parseInt(foundGen.end), savedYear);
                    } else {
                        populateYears(1990, new Date().getFullYear(), savedYear);
                    }
                } else {
                    populateYears(1990, new Date().getFullYear(), savedYear);
                }
            }
        }
    }

    // ================== 5) IMAGE PREVIEW (max 10) ==================
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function() {
            previewContainer.innerHTML = '';
            if (this.files) {
                Array.from(this.files).slice(0, 10).forEach(file => {
                    if (file.type.match('image.*')) {
                        const reader = new FileReader();
                        reader.onload = function(ev) {
                            const div = document.createElement('div');
                            div.className = 'aspect-square rounded-lg overflow-hidden border border-gray-200 shadow-sm relative';
                            div.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover">`;
                            previewContainer.appendChild(div);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    // ================== 6) DELETE SERVER IMAGE ==================
    window.deleteServerImage = function(imageName, serviceId, containerId) {
        if (!confirm('Ești sigur că vrei să ștergi această imagine?')) return;

        fetch(`/services/${serviceId}/image`, {
            method: 'DELETE',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify({ image: imageName })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                const el = document.getElementById(containerId);
                if (el) {
                    el.style.opacity = '0';
                    setTimeout(() => el.remove(), 250);
                }
            } else {
                alert('Eroare la ștergere.');
            }
        })
        .catch(() => alert('Eroare la ștergere.'));
    };

    // init
    initCascadeFromSaved();
    updateStep();
});
</script>

@endsection
