@extends('layouts.app')

@section('title', 'Adaugă anunț nou')

@section('content')

<div class="max-w-[1536px] mx-auto mt-8 mb-20 px-4 sm:px-6 lg:px-8">

    {{-- HEADER SIMPLIFICAT --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Vinde mașina ta
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-sm mt-1">Completează detaliile în 3 pași simpli.</p>
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

    <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data" id="wizardForm" novalidate>
        @csrf

        {{-- CONTAINER PRINCIPAL --}}
        <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-xl border border-gray-100 dark:border-[#2A2A2A] overflow-hidden min-h-[500px] flex flex-col relative">

            {{-- ================= PASUL 1: SPECIFICAȚII ================= --}}
            <div class="step-content p-6 md:p-8 animate-fade-in" data-step="1">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-[#C81424]">🚙</span> Detalii Vehicul
                </h2>
                
                <div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
                    
                    {{-- STANGA: SELECTOARE CASCADATE --}}
                    <div class="lg:col-span-5 space-y-5">
                        <div class="bg-gray-50 dark:bg-[#252525] p-5 rounded-xl border border-gray-100 dark:border-[#333]">
                            <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-3 block">Identificare</label>
                            
                            <div class="space-y-4">
                                {{-- Brand (FK) + fallback text --}}
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🏢</span>

                                    {{-- fallback pentru compatibilitate veche --}}
                                    <input type="hidden" name="brand" id="brandText" value="">

                                    <select name="brand_id" id="brandSelect" class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all appearance-none cursor-pointer" required>
                                        <option value="">Marca</option>
                                        @foreach($brands as $brand)
                                            <option value="{{ $brand->id }}" data-name="{{ $brand->name }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </select>
                                </div>

                                {{-- Model (FK) + fallback text --}}
                                <div class="relative group">
                                    <span class="absolute left-3 top-2.5 text-gray-400">🚘</span>

                                    {{-- fallback pentru compatibilitate veche --}}
                                    <input type="hidden" name="model" id="modelText" value="">

                                    <select name="model_id" id="modelSelect" disabled required class="w-full pl-10 pr-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] disabled:cursor-not-allowed focus:ring-2 focus:ring-[#C81424] outline-none transition-all appearance-none">
                                        <option value="">Model</option>
                                    </select>
                                </div>

                                <div class="grid grid-cols-2 gap-3">
                                    {{-- Generation (Trimite car_generation_id) --}}
                                    <div class="relative group">
                                         <select name="car_generation_id" id="generationSelect" disabled class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] disabled:cursor-not-allowed outline-none transition-all">
                                            <option value="">Gen</option>
                                         </select>
                                    </div>
                                    
                                    {{-- Year --}}
                                    <div class="relative group">
                                        <select name="an_fabricatie" id="yearSelect" disabled class="w-full px-3 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] disabled:cursor-not-allowed outline-none transition-all" required>
                                            <option value="">An</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Categorie (ascunsă - Autoturisme) --}}
<input type="hidden" name="category_id" value="{{ $autoCategoryId }}">
                    </div>

                    {{-- DREAPTA: PILLS (DINAMICE DIN DB) --}}
                    <div class="lg:col-span-7 space-y-6">
                        
                        {{-- CULOARE + FINISAJ (PILLS) --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 items-start">
    {{-- SELECT CULOARE --}}
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Culoare</label>
        <select name="culoare_id"
            class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-sm text-gray-900 dark:text-white outline-none focus:border-[#C81424]"
            required>
            <option value="">Alege Culoarea</option>
            @foreach($colors as $color)
                <option value="{{ $color->id }}">{{ $color->nume }}</option>
            @endforeach
        </select>
    </div>

    {{-- BUTOANE FINISAJ --}}
    <div>
        <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Finisaj</label>

        <input type="hidden" name="culoare_opt_id" id="inputColorOpt" value="">

<div class="flex flex-wrap gap-2">
    @foreach($colorOpts as $opt)
        <button type="button"
            onclick="selectPill('inputColorOpt', '{{ $opt->id }}', this)"
            class="pill-btn px-4 py-2 rounded-lg border border-gray-200 dark:border-[#333]
                   text-xs font-medium text-gray-600 dark:text-gray-300
                   hover:border-[#C81424] hover:text-[#C81424] transition-all
                   bg-white dark:bg-[#252525]">
            {{ $opt->nume }}
        </button>
    @endforeach
</div>

    </div>
</div>


                        {{-- CAROSERIE --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Caroserie</label>
                            <input type="hidden" name="caroserie_id" id="inputBodyType">
                            <div class="grid grid-cols-3 sm:grid-cols-4 gap-2">
                                @foreach($bodies as $body)
                                    <button type="button" onclick="selectPill('inputBodyType', '{{ $body->id }}', this)" 
                                        class="pill-btn px-2 py-2 rounded-lg border border-gray-200 dark:border-[#333] text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-[#C81424] hover:text-[#C81424] transition-all bg-white dark:bg-[#252525]">
                                        {{ $body->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-body" class="text-red-500 text-xs mt-1 hidden">Selectează caroseria.</p>
                        </div>

                        {{-- COMBUSTIBIL --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Combustibil</label>
                            <input type="hidden" name="combustibil_id" id="inputFuel">
                            <div class="flex flex-wrap gap-2">
                                @foreach($fuels as $fuel)
                                    <button type="button" onclick="selectPill('inputFuel', '{{ $fuel->id }}', this)" 
                                        class="pill-btn px-4 py-2 rounded-lg border border-gray-200 dark:border-[#333] text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-[#C81424] hover:text-[#C81424] transition-all bg-white dark:bg-[#252525]">
                                        {{ $fuel->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-fuel" class="text-red-500 text-xs mt-1 hidden">Alege combustibil.</p>
                        </div>

                        {{-- TRANSMISIE --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Transmisie</label>
                            <input type="hidden" name="cutie_viteze_id" id="inputTrans">
                            <div class="flex gap-3">
                                @foreach($transmissions as $trans)
                                    <button type="button" onclick="selectPill('inputTrans', '{{ $trans->id }}', this)" class="pill-btn flex-1 py-2.5 rounded-lg border border-gray-200 dark:border-[#333] text-sm font-medium bg-white dark:bg-[#252525] hover:border-[#C81424] transition-all flex items-center justify-center gap-2">
                                        {{ $trans->nume }}
                                    </button>
                                @endforeach
                            </div>
                            <p id="err-trans" class="text-red-500 text-xs mt-1 hidden">Alege transmisia.</p>
                        </div>
						{{-- TRACTIUNE --}}
<div>
    <label class="block text-xs font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Tracțiune</label>
    <input type="hidden" name="tractiune_id" id="inputTractiune">

    <div class="flex flex-wrap gap-2">
        @foreach($tractiuni as $tr)
            <button type="button"
                onclick="selectPill('inputTractiune', '{{ $tr->id }}', this)"
                class="pill-btn px-4 py-2 rounded-lg border border-gray-200 dark:border-[#333] text-xs font-medium text-gray-600 dark:text-gray-300 hover:border-[#C81424] hover:text-[#C81424] transition-all bg-white dark:bg-[#252525]">
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
                    <span class="text-[#C81424]">📝</span> Detalii Tehnice & Istoric
                </h2>

                {{-- VIN CODE --}}
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Serie Șasiu (VIN)</label>
                    <input type="text" name="vin" placeholder="Ex: WBA..." maxlength="17" class="w-full pl-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white uppercase font-mono">
                    <p class="text-xs text-gray-400 mt-1">Recomandat pentru verificare.</p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Rulaj (km)</label>
                        <div class="relative">
                            <input type="number" name="km" placeholder="150000" class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all font-mono" required>
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Putere</label>
                        <div class="relative">
                            <input type="number" name="putere" placeholder="190" class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all font-mono" required>
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">CP</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Capacitate</label>
                        <div class="relative">
                            <input type="number" name="capacitate_cilindrica" placeholder="1995" class="w-full pl-3 pr-8 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-[#C81424] outline-none transition-all font-mono">
                            <span class="absolute right-3 top-2.5 text-xs font-bold text-gray-400">cm³</span>
                        </div>
                    </div>
					{{-- NORMA POLUARE --}}
<div class="mb-6">
    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Normă poluare</label>
    <select name="norma_poluare_id"
        class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white">
        <option value="">Alege norma</option>
        @foreach($normePoluare as $norma)
            <option value="{{ $norma->id }}">{{ $norma->nume }}</option>
        @endforeach
    </select>
</div>

{{-- USI + LOCURI --}}
<div class="grid grid-cols-2 gap-6 mb-6">
    <div>
        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Număr uși</label>
        <select name="numar_usi" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white">
            <option value="">—</option>
            @foreach([2,3,4,5] as $usi)
                <option value="{{ $usi }}">{{ $usi }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Număr locuri</label>
        <select name="numar_locuri" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white">
            <option value="">—</option>
            @foreach(range(2,9) as $locuri)
                <option value="{{ $locuri }}">{{ $locuri }}</option>
            @endforeach
        </select>
    </div>
</div>

{{-- CHECKBOX-URI --}}
<div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-6">
    @foreach([
        'importata' => 'Importată',
        'avariata' => 'Avariată',
        'filtru_particule' => 'Filtru particule'
    ] as $name => $label)
        <label class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
            <input type="checkbox" name="{{ $name }}" value="1"
                class="rounded border-gray-300 dark:border-[#404040] text-[#C81424] focus:ring-[#C81424]">
            {{ $label }}
        </label>
    @endforeach
</div>

                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Titlu Anunț</label>
                        <input type="text" name="title" placeholder="Ex: BMW 320d M-Packet 2019, Unic Proprietar" class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-[#444] focus:border-[#C81424] outline-none text-gray-900 dark:text-white font-medium" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Descriere</label>
                        <textarea name="description" rows="8" placeholder="Descrie dotările, istoricul de service, starea tehnică..." class="w-full px-4 py-3 rounded-lg bg-gray-50 dark:bg-[#252525] border border-gray-200 dark:border-[#444] focus:border-[#C81424] outline-none text-sm text-gray-900 dark:text-white resize-none" required></textarea>
                    </div>
                </div>
            </div>

            {{-- ================= PASUL 3: FINALIZARE ================= --}}
            <div class="step-content p-6 md:p-8 hidden opacity-0" data-step="3">
                <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-[#C81424]">📸</span> Galerie & Preț
                </h2>

                {{-- DRAG & DROP FOTO --}}
                <div class="mb-8">
                    <div class="relative w-full group">
                        <input type="file" id="imageInput" name="images[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        <input type="hidden" id="primaryImageIndex" name="primary_image_index" value="">
                        <div class="flex flex-col items-center justify-center w-full h-40 border-2 border-dashed border-gray-300 dark:border-[#444] rounded-xl bg-gray-50 dark:bg-[#252525] group-hover:bg-[#fff4f5] dark:group-hover:bg-[#2a2a2a] group-hover:border-[#C81424] transition-all">
                            <div class="p-3 bg-white dark:bg-[#333] rounded-full shadow-sm mb-2">
                                <svg class="w-6 h-6 text-[#C81424]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Click sau trage poze aici</p>
                            <p class="text-xs text-gray-400 mt-1">Maxim 10 imagini, 15MB fiecare. Prima poză este cea principală.</p>
                        </div>
                    </div>
                    <p id="imageError" class="hidden mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"></p>
                    <div id="previewContainer" class="grid grid-cols-4 sm:grid-cols-6 gap-2 mt-4"></div>
                </div>

                <hr class="border-gray-100 dark:border-[#333] mb-8">

                {{-- PRET SI CONTACT --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                    {{-- PRET --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Preț Vânzare</label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" name="price_value" step="0.01" placeholder="0" class="w-full pl-4 pr-16 py-3 rounded-xl bg-gray-50 dark:bg-[#252525] border-none text-2xl font-bold text-gray-900 dark:text-white outline-none ring-1 ring-gray-200 dark:ring-[#444] focus:ring-2 focus:ring-green-500" required>
                                <div class="absolute right-2 top-2 bottom-2 flex bg-white dark:bg-[#333] rounded-lg p-1 border border-gray-100 dark:border-[#444]">
                                    <input type="hidden" name="currency" id="inputCurrency" value="EUR">
                                    <button type="button" onclick="selectPill('inputCurrency', 'EUR', this)" class="pill-btn px-2 text-xs font-bold rounded text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors selected">EUR</button>
                                    <button type="button" onclick="selectPill('inputCurrency', 'RON', this)" class="pill-btn px-2 text-xs font-bold rounded text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">RON</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
                             <input type="hidden" name="price_type" id="inputPriceType" value="fixed">
                             <button type="button" onclick="selectPill('inputPriceType', 'negotiable', this)" class="pill-btn px-3 py-1 text-xs border rounded-md border-gray-200 dark:border-[#444] text-gray-500 dark:text-gray-400 hover:border-green-500 hover:text-green-600 transition-colors">Negociabil</button>
                             <button type="button" onclick="selectPill('inputPriceType', 'fixed', this)" class="pill-btn px-3 py-1 text-xs border rounded-md border-green-500 bg-green-50 text-green-700 selected">Preț Fix</button>
                        </div>
                    </div>

                    {{-- CONTACT --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Telefon</label>
                            <input type="text" name="phone" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white" placeholder="07xx xxx xxx" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Județ</label>
                            <select id="county-select" name="county_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white" required>
                                <option value="">Alege județ</option>
                                @foreach ($counties as $county)
                                    <option value="{{ $county->id }}" @selected((string)old('county_id') === (string)$county->id)>{{ $county->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Oraș</label>
                            <select id="locality-select" name="locality_id" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white" disabled required>
                                <option value="">Selectează orașul</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- GUEST / AUTH SECTION --}}
                @guest
                <div class="mt-8 pt-8 border-t border-gray-100 dark:border-[#333]">
                    <div class="mb-5 flex items-start gap-3 p-4 bg-[#fff4f5] dark:bg-[#2a1013] rounded-xl border border-red-100 dark:border-red-900/40">
                        <span class="text-xl">ℹ️</span>
                        <div class="text-sm text-[#7f1d1d] dark:text-red-100">
                            <p class="font-bold mb-1">Nu ai cont?</p>
                            <p class="opacity-90">Completează datele de mai jos și îți creăm automat un cont.</p>
                        </div>
                    </div>
					<input type="hidden" name="user_type" value="individual">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div class="md:col-span-2">
                             <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Nume</label>
                             <input type="text" name="name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Email</label>
                            <input type="email" name="email" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Parolă</label>
                            <input type="password" name="password" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white">
                        </div>
                    </div>
                </div>
                @endguest
                
                @auth
                <div class="mt-8 pt-6 border-t border-gray-100 dark:border-[#333] flex items-center gap-3 text-green-600 dark:text-green-400 font-medium">
                     <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                     Autentificat ca: {{ auth()->user()->name }}
                </div>
                @endauth

            </div>

            {{-- FOOTER NAVIGARE --}}
            <div class="mt-auto p-5 bg-gray-50 dark:bg-[#252525] border-t border-gray-100 dark:border-[#333] flex justify-between items-center">
                <button type="button" id="prevBtn" class="hidden text-gray-500 dark:text-gray-400 font-bold text-sm px-4 py-2 hover:bg-gray-200 dark:hover:bg-[#333] rounded-lg transition-colors">
                    ← Înapoi
                </button>
                <button type="button" id="nextBtn" class="ml-auto bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm px-8 py-3 rounded-xl hover:shadow-lg hover:scale-[1.02] transition-all">
                    Continuă
                </button>
                <button type="submit" id="submitBtn" class="hidden ml-auto bg-[#C81424] text-white font-bold text-sm px-8 py-3 rounded-xl hover:shadow-lg hover:shadow-red-700/25 hover:scale-[1.02] transition-all">
                    Publică Anunțul
                </button>
            </div>

        </div>
    </form>
</div>

<div id="submitOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-[#1E1E1E]">
        <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-[#C81424]"></div>
        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Se încarcă anunțul</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Te rugăm să aștepți până se finalizează încărcarea pozelor și salvarea anunțului. Nu închide pagina și nu apăsa înapoi.
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
    // === 1. NAVIGATION WIZARD ===
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
    const presetLocalityId = "{{ old('locality_id') }}";
    const serverValidationErrors = @json($errors->messages());

    function updateStep() {
        steps.forEach(s => {
            if(parseInt(s.dataset.step) === currentStep) {
                s.classList.remove('hidden');
                setTimeout(() => s.classList.remove('opacity-0'), 50);
            } else {
                s.classList.add('hidden', 'opacity-0');
            }
        });
        dots.forEach((dot, idx) => {
            if (idx + 1 === currentStep) {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-[#C81424] text-white transition-all scale-110 shadow-md';
            } else if (idx + 1 < currentStep) {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-green-500 text-white transition-all';
                dot.innerHTML = '✓';
            } else {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all';
            }
        });
        prevBtn.classList.toggle('hidden', currentStep === 1);
        if(currentStep === totalSteps) {
            nextBtn.classList.add('hidden');
            submitBtn.classList.remove('hidden');
        } else {
            nextBtn.classList.remove('hidden');
            submitBtn.classList.add('hidden');
        }
    }

    function resetLocalities() {
        if (!localitySelect) return;
        localitySelect.innerHTML = '<option value=\"\">Selectează orașul</option>';
        localitySelect.disabled = true;
    }

    function populateLocalities(localities, selectedId) {
        if (!localitySelect) return;
        localitySelect.innerHTML = '<option value=\"\">Selectează orașul</option>';
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

    // === 2. PILL SELECTORS LOGIC ===
    window.selectPill = function(inputId, value, btnElement) {
        document.getElementById(inputId).value = value;
        const parent = btnElement.parentElement;
        const siblings = parent.querySelectorAll('.pill-btn');
        siblings.forEach(el => el.classList.remove('selected'));
        btnElement.classList.add('selected');

        const err = document.getElementById('err-' + inputId.replace('input','').toLowerCase());
        if(err) err.classList.add('hidden');
        if(inputId === 'inputBodyType') clearCustomPillError('inputBodyType', 'err-body');
        if(inputId === 'inputFuel') clearCustomPillError('inputFuel', 'err-fuel');
        if(inputId === 'inputTrans') clearCustomPillError('inputTrans', 'err-trans');
		if(inputId === 'inputTractiune') clearCustomPillError('inputTractiune', 'err-tractiune');

    }

    // === 3. VALIDATION ===
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
            email: 'emailul',
            password: 'parola',
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

        const clear = () => {
            if (input.checkValidity()) {
                input.classList.remove('ring-2', 'ring-red-500', 'border-red-500');
                input.removeAttribute('aria-invalid');
            }
        };

        input.addEventListener('input', clear);
        input.addEventListener('change', clear);
    }

    function clearCustomPillError(inputId, errorId) {
        const input = document.getElementById(inputId);
        const wrapper = input?.closest('div');
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
        wrapper?.querySelectorAll('.pill-btn').forEach(btn => {
            btn.classList.add('ring-2', 'ring-red-500', 'border-red-500');
        });

        return {
            target: wrapper?.querySelector('.pill-btn') || error || input,
            message,
        };
    }

    function scrollToValidationTarget(target) {
        if (!target) return;

        target.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            if (typeof target.focus === 'function') {
                target.focus({ preventScroll: true });
            }
        }, 350);
    }

    function validateStep(stepNumber, { reveal = true } = {}) {
        const stepEl = document.querySelector(`.step-content[data-step="${stepNumber}"]`);
        if (!stepEl) return { valid: true, messages: [], firstTarget: null };

        const invalidItems = [];
        const inputs = stepEl.querySelectorAll('input, select, textarea');

        inputs.forEach(input => {
            if (input.disabled || input.type === 'hidden') return;

            if (!input.checkValidity()) {
                const message = validationMessageFor(input);
                invalidItems.push({ target: input, message });
                if (reveal) markInvalidInput(input);
            }
        });

        if (stepNumber === 1) {
            const genSel = document.getElementById('generationSelect');
            if (genSel && !genSel.disabled && !genSel.value) {
                invalidItems.push({ target: genSel, message: 'Alege generația.' });
                if (reveal) markInvalidInput(genSel);
            }

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
        if(validateCurrentStep()) {
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

    if (countySelect && countySelect.value) {
        loadLocalities(countySelect.value, presetLocalityId);
    } else {
        resetLocalities();
    }

    // === 4. CASCADING SELECTS (Brand -> Model -> Generation -> Year) ===
    // IMPORTANT: $carData trebuie sa fie pe ID-uri:
    // carData[brand_id] = [{id, name, generations:[{id,name,start,end}]}]
    const carData = @json($carData ?? []);
    const brandSel = document.getElementById('brandSelect');
    const modelSel = document.getElementById('modelSelect');
    const genSel = document.getElementById('generationSelect');
    const yearSel = document.getElementById('yearSelect');

    function populateYears(start, end) {
        yearSel.innerHTML = '<option value="">An</option>';
        yearSel.disabled = false;
        for(let i = end; i >= start; i--) {
            yearSel.innerHTML += `<option value="${i}">${i}</option>`;
        }
    }
    function resetSelect(el, defaultText) {
        el.innerHTML = `<option value="">${defaultText}</option>`;
        el.disabled = true;
        el.value = "";
    }

    brandSel.addEventListener('change', function() {
        const brandId = this.value;

        // fallback text (compatibilitate veche)
        const brandName = this.options[this.selectedIndex]?.dataset?.name || '';
        document.getElementById('brandText').value = brandName;

        resetSelect(modelSel, 'Model');
        resetSelect(genSel, 'Gen');
        resetSelect(yearSel, 'An');

        if(brandId && carData[brandId]) {
            modelSel.disabled = false;
            carData[brandId].forEach(m => {
                modelSel.innerHTML += `<option value="${m.id}" data-name="${m.name}">${m.name}</option>`;
            });
        }
    });

    modelSel.addEventListener('change', function() {
        const brandId = brandSel.value;
        const modelId = this.value;

        // fallback text (compatibilitate veche)
        const modelName = this.options[this.selectedIndex]?.dataset?.name || '';
        document.getElementById('modelText').value = modelName;

        resetSelect(genSel, 'Gen');
        resetSelect(yearSel, 'An');

        if(brandId && modelId && carData[brandId]) {
            const modelObj = carData[brandId].find(x => String(x.id) === String(modelId));
            const generations = modelObj?.generations || [];

            if (generations.length > 0) {
                genSel.disabled = false;
                genSel.classList.remove('bg-gray-100', 'cursor-not-allowed', 'text-gray-400');

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
                populateYears(1990, new Date().getFullYear());
            }
        }
    });

    genSel.addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];
        if(selected && selected.dataset.start) {
            populateYears(parseInt(selected.dataset.start), parseInt(selected.dataset.end));
        }
    });

    // === 5. IMAGE PREVIEW ===
    const imageInput = document.getElementById('imageInput');
    const previewContainer = document.getElementById('previewContainer');
    const imageError = document.getElementById('imageError');
    const primaryImageIndex = document.getElementById('primaryImageIndex');
    const wizardForm = document.getElementById('wizardForm');
    const submitOverlay = document.getElementById('submitOverlay');
    const maxImages = 10;
    const maxImageBytes = 15 * 1024 * 1024;
    let selectedImages = [];
    let primaryIndex = null;

    function showImageError(message) {
        if (!imageError) return;
        imageError.textContent = message;
        imageError.classList.remove('hidden');
    }

    function clearImageError() {
        if (!imageError) return;
        imageError.textContent = '';
        imageError.classList.add('hidden');
    }

    function syncImageInput() {
        const dataTransfer = new DataTransfer();
        selectedImages.forEach(file => dataTransfer.items.add(file));
        imageInput.files = dataTransfer.files;
        primaryImageIndex.value = primaryIndex === null ? '' : String(primaryIndex);
    }

    function renderImagePreview() {
        previewContainer.innerHTML = '';

        selectedImages.forEach((file, index) => {
            const div = document.createElement('div');
            div.className = 'aspect-square rounded-lg overflow-hidden border border-gray-200 shadow-sm relative bg-white dark:bg-[#252525]';
            div.innerHTML = `
                <img src="" class="w-full h-full object-cover" alt="">
                ${primaryIndex === index ? '<span class="absolute left-1 top-1 rounded bg-[#C81424] px-2 py-0.5 text-[10px] font-bold text-white">Principală</span>' : ''}
                <div class="absolute inset-x-1 bottom-1 flex gap-1">
                    ${primaryIndex === index ? '' : '<button type="button" data-primary-index="' + index + '" class="flex-1 rounded bg-white/90 px-1 py-1 text-[10px] font-bold text-gray-800 shadow hover:bg-white">Setează ca principală</button>'}
                    <button type="button" data-remove-index="${index}" class="ml-auto rounded bg-red-600 px-2 py-1 text-[10px] font-bold text-white shadow hover:bg-red-700">×</button>
                </div>
            `;
            previewContainer.appendChild(div);

            const reader = new FileReader();
            reader.onload = function(ev) {
                const img = div.querySelector('img');
                if (img) img.src = ev.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    if (imageInput && previewContainer) {
        imageInput.addEventListener('change', function() {
            clearImageError();
            const incomingFiles = Array.from(this.files || []);

            if (selectedImages.length + incomingFiles.length > maxImages) {
                showImageError('Poți încărca maxim 10 poze. Elimină din selecție sau alege mai puține imagini.');
                this.value = '';
                syncImageInput();
                return;
            }

            const oversized = incomingFiles.find(file => file.size > maxImageBytes);
            if (oversized) {
                showImageError(`Imaginea "${oversized.name}" este prea mare. Limita este 15MB per imagine.`);
                this.value = '';
                syncImageInput();
                return;
            }

            const invalid = incomingFiles.find(file => !file.type.match('image.*'));
            if (invalid) {
                showImageError(`Fișierul "${invalid.name}" nu este o imagine validă.`);
                this.value = '';
                syncImageInput();
                return;
            }

            selectedImages = selectedImages.concat(incomingFiles);
            if (primaryIndex === null && selectedImages.length > 0) {
                primaryIndex = 0;
            }
            syncImageInput();
            renderImagePreview();
        });

        previewContainer.addEventListener('click', function(event) {
            const primaryButton = event.target.closest('[data-primary-index]');
            const removeButton = event.target.closest('[data-remove-index]');

            if (primaryButton) {
                primaryIndex = Number(primaryButton.dataset.primaryIndex);
                syncImageInput();
                renderImagePreview();
            }

            if (removeButton) {
                const removeIndex = Number(removeButton.dataset.removeIndex);
                selectedImages.splice(removeIndex, 1);
                if (selectedImages.length === 0) {
                    primaryIndex = null;
                } else if (primaryIndex === removeIndex) {
                    primaryIndex = 0;
                } else if (primaryIndex !== null && primaryIndex > removeIndex) {
                    primaryIndex--;
                }
                clearImageError();
                syncImageInput();
                renderImagePreview();
            }
        });
    }

    if (wizardForm) {
        wizardForm.addEventListener('submit', function(event) {
            if (!validateAllSteps()) {
                event.preventDefault();
                return;
            }

            submitBtn.disabled = true;
            submitBtn.classList.add('opacity-70', 'cursor-not-allowed');
            submitBtn.textContent = 'Se încarcă...';
            submitOverlay?.classList.remove('hidden');
            submitOverlay?.classList.add('flex');
        });
    }

    if (!applyServerValidationErrors(serverValidationErrors)) {
        updateStep();
    }
});
</script>

@endsection
