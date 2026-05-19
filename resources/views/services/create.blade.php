@extends('layouts.app')

@section('title', 'Adaugă anunț nou')

@section('content')

<div class="publish-form-shell mx-auto mt-6 mb-20 w-full max-w-3xl px-2 sm:mt-8 sm:px-6 xl:max-w-[50%] xl:px-0">

    {{-- HEADER SIMPLIFICAT --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-4">
        <div>
            <h1 class="text-2xl md:text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
    Publică mașina gratuit
</h1>
<p class="text-gray-500 dark:text-gray-400 text-sm mt-1">
    Fără cont obligatoriu. Completezi 3 pași simpli și publici în 1-2 minute.
</p>
        </div>
        
        {{-- STEPPER --}}
        <div class="flex items-center gap-2 bg-white dark:bg-[#1E1E1E] p-1.5 rounded-full shadow-sm border border-gray-100 dark:border-[#333]">
            <div id="step-dot-1" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-slate-900 text-white transition-all">1</div>
            <div class="w-8 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div id="step-dot-2" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all">2</div>
            <div class="w-8 h-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div id="step-dot-3" class="step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all">3</div>
        </div>
    </div>

    <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data" id="wizardForm" novalidate>
        @csrf

        @php
            $normalizeWizardLabel = fn ($value) => strtolower(trim(\Illuminate\Support\Str::ascii((string) $value)));

            $sortWizardOptions = function ($items, array $priority) use ($normalizeWizardLabel) {
                return collect($items)->sortBy(function ($item) use ($priority, $normalizeWizardLabel) {
                    $label = $normalizeWizardLabel($item->nume ?? '');
                    $rank = 99;

                    foreach ($priority as $needle => $value) {
                        if ($label === $needle || str_contains($label, $needle)) {
                            $rank = $value;
                            break;
                        }
                    }

                    return sprintf('%02d-%s', $rank, $label);
                })->values();
            };

            $orderedBodies = $sortWizardOptions($bodies, [
                'sedan' => 0,
                'berlina' => 0,
                'break' => 1,
                'breack' => 1,
                'combi' => 1,
                'wagon' => 1,
                'hatchback' => 2,
                'coupe' => 3,
                'suv' => 4,
            ]);

            $orderedFuels = $sortWizardOptions($fuels, [
                'benzina' => 0,
                'motorina' => 1,
                'hibrid' => 2,
                'hybrid' => 2,
                'electric' => 3,
            ]);

            $orderedColorOpts = $sortWizardOptions($colorOpts, [
                'metalizata' => 0,
                'metalizat' => 0,
                'perlata' => 1,
                'perlat' => 1,
                'mata' => 2,
                'mat' => 2,
            ]);

        @endphp

        {{-- CONTAINER PRINCIPAL --}}
        <div class="wizard-card bg-white dark:bg-[#1E1E1E] rounded-[1.15rem] sm:rounded-2xl shadow-xl border border-gray-100 dark:border-[#2A2A2A] overflow-hidden min-h-[500px] flex flex-col relative">

            {{-- ================= PASUL 1: SPECIFICAȚII ================= --}}
            <div class="step-content p-4 sm:p-6 md:p-8 animate-fade-in" data-step="1">
                <h2 class="text-xl font-extrabold text-gray-900 dark:text-white mb-6 flex items-center gap-2">
                    <span class="text-slate-500">🚙</span> Detalii Vehicul
                </h2>
                
                @php
                    $populareNume = ['Audi', 'BMW', 'Dacia', 'Ford', 'Opel', 'Renault', 'Volkswagen', 'Mercedes-Benz', 'Skoda'];
                    $brandsPopulare = $brands->whereIn('name', $populareNume)->sortBy('name');
                    $toateMarcile = $brands->sortBy('name');
                @endphp

                <div class="space-y-8">
                    <div class="space-y-4">
                        <label class="block text-[13px] font-extrabold uppercase tracking-[0.16em] text-slate-500 dark:text-gray-400">Identificare</label>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
                            {{-- Brand (FK) + fallback text --}}
                            <div class="relative group">
                                {{-- fallback pentru compatibilitate veche --}}
                                <input type="hidden" name="brand" id="brandText" value="">

                                <select name="brand_id" id="brandSelect" class="wizard-big-select w-full cursor-pointer appearance-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base font-bold text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white" required>
                                    <option value="">Marcă</option>
                                    @if($brandsPopulare->isNotEmpty())
                                        <optgroup label="Populare">
                                            @foreach($brandsPopulare as $brand)
                                                <option value="{{ $brand->id }}" data-name="{{ $brand->name }}">{{ $brand->name }}</option>
                                            @endforeach
                                        </optgroup>
                                    @endif
                                    <optgroup label="A-Z">
                                        @foreach($toateMarcile as $brand)
                                            <option value="{{ $brand->id }}" data-name="{{ $brand->name }}">{{ $brand->name }}</option>
                                        @endforeach
                                    </optgroup>
                                </select>
                            </div>

                            {{-- Model (FK) + fallback text --}}
                            <div class="relative group">
                                {{-- fallback pentru compatibilitate veche --}}
                                <input type="hidden" name="model" id="modelText" value="">

                                <select name="model_id" id="modelSelect" disabled required class="wizard-big-select w-full appearance-none rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base font-bold text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white dark:disabled:bg-[#222]">
                                    <option value="">Model</option>
                                </select>
                            </div>

                            {{-- Generation (Trimite car_generation_id) --}}
                            <div class="relative group">
                                <select name="car_generation_id" id="generationSelect" disabled class="wizard-big-select w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base font-bold text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white dark:disabled:bg-[#222]">
                                    <option value="">Generație</option>
                                </select>
                            </div>

                            {{-- Year --}}
                            <div class="relative group">
                                <select name="an_fabricatie" id="yearSelect" disabled class="wizard-big-select w-full rounded-2xl border border-slate-200 bg-white px-4 py-3 text-base font-bold text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white dark:disabled:bg-[#222]" required>
                                    <option value="">An fabricație</option>
                                </select>
                            </div>
                        </div>

                        {{-- Categorie (ascunsă - Autoturisme) --}}
                        <input type="hidden" name="category_id" value="{{ $autoCategoryId }}">
                    </div>

                    <div class="space-y-6">
                        {{-- SELECT CULOARE + OPTIUNI FINISAJ --}}
                        <div class="color-finish-row">
                            <select name="culoare_id"
                                class="color-select-compact rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-sm text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white"
                                required>
                                <option value="">Culoare</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}">{{ $color->nume }}</option>
                                @endforeach
                            </select>

                            <div class="contents">
                                <input type="hidden" name="culoare_opt_id" id="inputColorOpt" value="">

                                @foreach($orderedColorOpts as $opt)
                                    <button type="button"
                                        onclick="selectPill('inputColorOpt', '{{ $opt->id }}', this)"
                                        class="pill-btn color-option-pill rounded-xl border border-slate-200 bg-slate-50 px-3 py-2 text-xs font-semibold text-slate-600 transition-all hover:border-red-200 hover:bg-red-50 hover:text-[#94111B] dark:border-[#333] dark:bg-[#252525] dark:text-gray-300 dark:hover:border-red-900/60 dark:hover:bg-red-950/20 dark:hover:text-red-100">
                                        {{ $opt->nume }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-2.5 sm:gap-4">
                        {{-- CAROSERIE --}}
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300">Caroserie</label>
                            <select name="caroserie_id" id="inputBodyType" class="choice-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 sm:px-4 sm:text-sm dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white">
                                <option value="">Selectează</option>
                                @foreach($orderedBodies as $body)
                                    <option value="{{ $body->id }}">{{ $body->nume }}</option>
                                @endforeach
                            </select>
                            <p id="err-body" class="text-red-500 text-xs mt-1 hidden">Selectează caroseria.</p>
                        </div>

                        {{-- COMBUSTIBIL --}}
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300">Combustibil</label>
                            <select name="combustibil_id" id="inputFuel" class="choice-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 sm:px-4 sm:text-sm dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white">
                                <option value="">Selectează</option>
                                @foreach($orderedFuels as $fuel)
                                    <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                                @endforeach
                            </select>
                            <p id="err-fuel" class="text-red-500 text-xs mt-1 hidden">Alege combustibil.</p>
                        </div>

                        {{-- TRANSMISIE --}}
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300">Transmisie</label>
                            <select name="cutie_viteze_id" id="inputTrans" class="choice-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 sm:px-4 sm:text-sm dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white">
                                <option value="">Selectează</option>
                                @foreach($transmissions as $trans)
                                    <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                                @endforeach
                            </select>
                            <p id="err-trans" class="text-red-500 text-xs mt-1 hidden">Alege transmisia.</p>
                        </div>

                        {{-- TRACTIUNE --}}
                        <div>
                            <label class="mb-2 block text-xs font-bold uppercase tracking-wider text-slate-600 dark:text-gray-300">Tracțiune</label>
                            <select name="tractiune_id" id="inputTractiune" class="choice-select w-full rounded-xl border border-slate-200 bg-white px-3 py-2.5 text-xs text-slate-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 sm:px-4 sm:text-sm dark:border-[#444] dark:bg-[#1a1a1a] dark:text-white">
                                <option value="">Selectează</option>
                                @foreach($tractiuni as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->nume }}</option>
                                @endforeach
                            </select>

                            <p id="err-tractiune" class="text-red-500 text-xs mt-1 hidden">Alege tracțiunea.</p>
                        </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PASUL 2: ISTORIC & DESCRIERE ================= --}}
            <div class="step-content p-4 sm:p-6 md:p-8 hidden opacity-0" data-step="2">
              <div class="mb-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <span class="text-slate-500">📝</span> Detalii Tehnice & Istoric
    </h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
        Ai trecut de primul pas. Completează ce știi sigur — detaliile corecte cresc încrederea cumpărătorilor.
    </p>
</div>

                {{-- VIN CODE --}}
                <div class="mb-6">
                    <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Serie Șasiu (VIN)</label>
                    <input type="text" name="vin" placeholder="Ex: WBA..." maxlength="17" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-4 text-gray-900 font-mono uppercase outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                    <p class="text-xs text-gray-400 mt-1">Opțional, dar recomandat. VIN-ul ajută cumpărătorul să verifice istoricul mașinii.</p>
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-3 sm:gap-4 md:gap-6 mb-5 sm:mb-6">
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Rulaj</label>
                        <div class="relative">
                            <input type="number" name="km" placeholder="150000" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-16 text-gray-900 font-mono outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white" required>
                            <span class="pointer-events-none absolute inset-y-2 right-3 flex items-center border-l border-slate-200 pl-3 text-[11px] font-bold uppercase text-slate-400 dark:border-[#444]">km</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Putere</label>
                        <div class="relative">
                            <input type="number" name="putere" placeholder="190" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-16 text-gray-900 font-mono outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white" required>
                            <span class="pointer-events-none absolute inset-y-2 right-3 flex items-center border-l border-slate-200 pl-3 text-[11px] font-bold uppercase text-slate-400 dark:border-[#444]">CP</span>
                        </div>
                    </div>
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Capacitate</label>
                        <div class="relative">
                            <input type="number" name="capacitate_cilindrica" placeholder="1995" class="w-full rounded-xl border border-slate-200 bg-white py-2.5 pl-3 pr-20 text-gray-900 font-mono outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                            <span class="pointer-events-none absolute inset-y-2 right-3 flex items-center border-l border-slate-200 pl-3 text-[11px] font-bold text-slate-400 dark:border-[#444]">cm³</span>
                        </div>
                    </div>
                </div>

                <div class="mb-5 grid grid-cols-2 gap-3 sm:mb-6 sm:gap-4 md:grid-cols-3 md:gap-6">
                    {{-- NORMA POLUARE --}}
                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Normă poluare</label>
                        <select name="norma_poluare_id"
                            class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                            <option value="">Alege norma</option>
                            @foreach($normePoluare as $norma)
                                <option value="{{ $norma->id }}">{{ $norma->nume }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Număr uși</label>
                        <select name="numar_usi" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                            <option value="">—</option>
                            @foreach([2,3,4,5] as $usi)
                                <option value="{{ $usi }}">{{ $usi }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Număr locuri</label>
                        <select name="numar_locuri" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                            <option value="">—</option>
                            @foreach(range(2,9) as $locuri)
                                <option value="{{ $locuri }}">{{ $locuri }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- CHECKBOX-URI --}}
                <div class="grid grid-cols-2 gap-2 mb-6 lg:grid-cols-4">
                    @foreach(\App\Models\Service::FEATURE_OPTIONS as $name => $label)
                        <label class="relative flex min-h-9 cursor-pointer items-center justify-center overflow-hidden rounded-full text-[10px] font-semibold transition-all sm:min-h-11 sm:text-sm">
                            <input type="checkbox" name="{{ $name }}" value="1"
                                @checked(old($name))
                                class="peer sr-only">
                            <span class="absolute inset-0 rounded-full border border-slate-200 bg-white transition-all peer-focus-visible:ring-2 peer-focus-visible:ring-slate-900/10 peer-checked:border-slate-900 peer-checked:bg-slate-900 dark:border-[#404040] dark:bg-[#252525] dark:peer-checked:border-white dark:peer-checked:bg-white"></span>
                            <span class="relative px-1.5 py-1 text-center leading-tight text-slate-600 transition-colors peer-checked:text-white sm:px-4 sm:py-2 dark:text-gray-300 dark:peer-checked:text-slate-950">{{ $label }}</span>
                        </label>
                    @endforeach
                </div>

                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Titlu Anunț</label>
                        <input type="text" name="title" placeholder="Ex: BMW 320d M-Packet 2019, Unic Proprietar" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 font-medium text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-1">Descriere</label>
                        <textarea name="description" rows="8" placeholder="Descrie dotările, istoricul de service, starea tehnică..." class="w-full resize-none rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white" required></textarea>
                    </div>
                </div>
            </div>

            {{-- ================= PASUL 3: FINALIZARE ================= --}}
            <div class="step-content p-4 sm:p-6 md:p-8 hidden opacity-0" data-step="3">
                <div class="mb-6">
    <h2 class="text-lg font-bold text-gray-900 dark:text-white flex items-center gap-2">
        <span class="text-slate-500">📸</span> Galerie & Preț
    </h2>
    <p class="mt-1 text-sm text-slate-500 dark:text-gray-400">
        Ultimul pas. Adaugă prețul și datele de contact, apoi poți publica anunțul.
    </p>
</div>

                <div class="mx-auto flex w-full max-w-3xl flex-col gap-5 sm:gap-6">
                {{-- DRAG & DROP FOTO --}}
                <div>
                    <div class="relative w-full group">
                        <input type="file" id="imageInput" name="images[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                        <input type="hidden" id="primaryImageIndex" name="primary_image_index" value="">
                        <div class="flex h-44 w-full flex-col items-center justify-center rounded-2xl border-2 border-dashed border-slate-300 bg-slate-50 transition-all group-hover:border-slate-500 group-hover:bg-white dark:border-[#444] dark:bg-[#252525] dark:group-hover:bg-[#2a2a2a]">
                            <div class="p-3 bg-white dark:bg-[#333] rounded-full shadow-sm mb-2">
                                <svg class="w-6 h-6 text-slate-700 dark:text-slate-200" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                            </div>
                            <p class="text-sm font-semibold text-gray-700 dark:text-gray-300">Click sau trage poze aici</p>
                            <p class="text-xs text-gray-400 mt-1">Maxim 10 imagini, 15MB fiecare. Prima poză este cea principală.</p>
                        </div>
                    </div>
                    <p id="imageError" class="hidden mt-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700"></p>
                    <div id="previewContainer" class="grid grid-cols-2 gap-2.5 sm:grid-cols-3 sm:gap-3 md:grid-cols-4 mt-4"></div>
                </div>

                {{-- PRET SI CONTACT --}}
                <div class="grid grid-cols-1 gap-5 border-t border-slate-100 pt-5 sm:gap-6 sm:pt-6 md:grid-cols-2 dark:border-[#333]">
                    {{-- PRET --}}
                    <div>
                        <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-2">Preț Vânzare</label>
                        <div class="flex items-center gap-3">
                            <div class="relative flex-1">
                                <input type="number" name="price_value" step="0.01" placeholder="0" class="w-full rounded-xl border-none bg-slate-50 py-3 pl-4 pr-16 text-2xl font-bold text-gray-900 outline-none ring-1 ring-slate-200 transition-all focus:ring-2 focus:ring-slate-900/10 dark:bg-[#252525] dark:text-white dark:ring-[#444]" required>
                                <div class="absolute bottom-2 right-2 top-2 flex rounded-lg border border-slate-100 bg-white p-1 dark:border-[#444] dark:bg-[#333]">
                                    <input type="hidden" name="currency" id="inputCurrency" value="EUR">
                                    <button type="button" onclick="selectPill('inputCurrency', 'EUR', this)" class="pill-btn rounded-md px-2 text-xs font-bold text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white selected">EUR</button>
                                    <button type="button" onclick="selectPill('inputCurrency', 'RON', this)" class="pill-btn rounded-md px-2 text-xs font-bold text-gray-500 transition-colors hover:text-gray-900 dark:text-gray-400 dark:hover:text-white">RON</button>
                                </div>
                            </div>
                        </div>
                        <div class="mt-3 flex gap-2">
    <input type="hidden" name="price_type" id="inputPriceType" value="fixed">

    <button
        type="button"
        onclick="selectPill('inputPriceType', 'negotiable', this)"
        class="pill-btn rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors hover:border-slate-400 hover:text-slate-900 dark:border-[#444] dark:bg-[#252525] dark:text-gray-400 dark:hover:text-white">
        Negociabil
    </button>

    <button
        type="button"
        onclick="selectPill('inputPriceType', 'fixed', this)"
        class="pill-btn selected rounded-xl border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-500 transition-colors hover:border-slate-400 hover:text-slate-900 dark:border-[#444] dark:bg-[#252525] dark:text-gray-400 dark:hover:text-white">
        Preț Fix
    </button>
</div>
                    </div>

                    {{-- CONTACT --}}
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Telefon</label>
                            <input type="text" name="phone" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white" placeholder="07xx xxx xxx" required>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Județ</label>
                            <select id="county-select" name="county_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white" required>
                                <option value="">Alege județ</option>
                                @foreach ($counties as $county)
                                    <option value="{{ $county->id }}" @selected((string)old('county_id') === (string)$county->id)>{{ $county->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Oraș</label>
                            <select id="locality-select" name="locality_id" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 disabled:cursor-not-allowed disabled:bg-slate-100 disabled:text-slate-400 dark:border-[#444] dark:bg-[#252525] dark:text-white dark:disabled:bg-[#222]" disabled required>
                                <option value="">Selectează orașul</option>
                            </select>
                        </div>
                    </div>
                </div>

                {{-- GUEST / AUTH SECTION --}}
                @guest
                <div class="border-t border-slate-100 pt-6 dark:border-[#333]">
                    <div class="mb-5 flex items-start gap-3 rounded-2xl border border-slate-200 bg-slate-50 p-4 dark:border-white/10 dark:bg-[#20242a]">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-slate-500 dark:text-slate-300" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 11v5m0-8h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <div class="text-sm text-slate-600 dark:text-slate-200">
                            <p class="font-bold mb-1">Cont opțional</p>
<p class="opacity-90">
    Poți publica și fără cont. Completează datele doar dacă vrei să administrezi mai ușor anunțul mai târziu.
</p>
                        </div>
                    </div>
					<input type="hidden" name="user_type" value="individual">
                    <div class="grid grid-cols-1 gap-4 sm:gap-5 md:grid-cols-3">
                        <div>
                             <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Nume</label>
                             <input type="text" name="name" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Email</label>
                            <input type="email" name="email" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 dark:text-gray-400 uppercase mb-1">Parolă</label>
                            <input type="password" name="password" class="w-full rounded-xl border border-slate-200 bg-white px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-slate-300 focus:ring-2 focus:ring-slate-900/5 dark:border-[#444] dark:bg-[#252525] dark:text-white">
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

            </div>

            {{-- FOOTER NAVIGARE --}}
            <div class="wizard-footer mt-auto p-4 sm:p-5 bg-gray-50 dark:bg-[#252525] border-t border-gray-100 dark:border-[#333] flex justify-between items-center">
                <button type="button" id="prevBtn" class="hidden text-gray-500 dark:text-gray-400 font-bold text-sm px-4 py-2 hover:bg-gray-200 dark:hover:bg-[#333] rounded-lg transition-colors">
                    ← Înapoi
                </button>
                <button type="button" id="nextBtn" class="ml-auto rounded-xl bg-[#C81424] px-8 py-3 text-sm font-bold text-white shadow-md shadow-red-700/20 transition-all hover:scale-[1.02] hover:bg-[#94111B] hover:shadow-lg">
                    Continuă
                </button>
                <button type="submit" id="submitBtn" class="hidden ml-auto rounded-xl bg-[#C81424] px-8 py-3 text-sm font-bold text-white shadow-md shadow-red-700/20 transition-all hover:scale-[1.02] hover:bg-[#94111B] hover:shadow-lg">
                    Publică Anunțul
                </button>
            </div>

        </div>
    </form>
</div>

<div id="submitOverlay" class="fixed inset-0 z-50 hidden items-center justify-center bg-black/60 backdrop-blur-sm px-4">
    <div class="max-w-md rounded-2xl bg-white p-6 text-center shadow-2xl dark:bg-[#1E1E1E]">
        <div class="mx-auto mb-4 h-12 w-12 animate-spin rounded-full border-4 border-gray-200 border-t-slate-900"></div>
        <h2 class="text-lg font-extrabold text-gray-900 dark:text-white">Se încarcă anunțul</h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
            Te rugăm să aștepți până se finalizează încărcarea pozelor și salvarea anunțului. Nu închide pagina și nu apăsa înapoi.
        </p>
    </div>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.3s ease-out forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
    #wizardForm select {
        accent-color: #C81424;
        appearance: none;
        background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.5 7.75L10 12.25L14.5 7.75' stroke='%23475569' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        background-position: right 0.85rem center;
        background-repeat: no-repeat;
        background-size: 1rem;
        padding-right: 2.35rem;
    }
    #wizardForm select:focus {
        border-color: #C81424;
        box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.10);
    }
    #wizardForm .native-select-hidden {
        display: none !important;
    }
    #wizardForm .custom-select {
        min-width: 0;
        position: relative;
        width: 100%;
    }
    #wizardForm .custom-select.is-open {
        z-index: 80;
    }
    #wizardForm .custom-select-trigger {
        align-items: center;
        background: #ffffff;
        border: 1px solid #e2e8f0;
        border-radius: 0.75rem;
        color: #0f172a;
        display: flex;
        font-size: 0.875rem;
        font-weight: 650;
        gap: 0.65rem;
        height: 2.85rem;
        justify-content: space-between;
        min-width: 0;
        padding: 0 0.8rem 0 1rem;
        text-align: left;
        transition: border-color 0.18s ease, box-shadow 0.18s ease, background-color 0.18s ease, color 0.18s ease;
        width: 100%;
    }
    #wizardForm .custom-select.has-leading-icon .custom-select-trigger {
        padding-left: 2.45rem;
    }
    #wizardForm .custom-select-label {
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }
    #wizardForm .custom-select-chevron {
        color: #64748b;
        display: inline-flex;
        flex: 0 0 auto;
        transition: transform 0.18s ease, color 0.18s ease;
    }
    #wizardForm .custom-select-chevron svg {
        fill: none;
        height: 1.05rem;
        stroke: currentColor;
        stroke-linecap: round;
        stroke-linejoin: round;
        stroke-width: 1.9;
        width: 1.05rem;
    }
    #wizardForm .custom-select-trigger:hover {
        border-color: #cbd5e1;
        background: #f8fafc;
    }
    #wizardForm .custom-select.is-open .custom-select-trigger {
        background: #ffffff;
        border-color: #C81424;
        box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.12);
    }
    #wizardForm .custom-select.is-invalid .custom-select-trigger {
        border-color: #ef4444;
        box-shadow: 0 0 0 3px rgba(239, 68, 68, 0.14);
    }
    #wizardForm .custom-select.is-open .custom-select-chevron {
        color: #C81424;
        transform: rotate(180deg);
    }
    #wizardForm .custom-select.is-disabled .custom-select-trigger {
        background: #f1f5f9;
        color: #94a3b8;
        cursor: not-allowed;
    }
    #wizardForm .custom-select-menu,
    body > .custom-select-menu {
        background: #ffffff;
        border: 1px solid rgba(148, 163, 184, 0.28);
        border-radius: 0.9rem;
        box-shadow: 0 18px 38px rgba(15, 23, 42, 0.16);
        display: none;
        left: 0;
        max-height: min(19rem, 46vh);
        overflow-y: auto;
        padding: 0.35rem;
        position: absolute;
        right: auto;
        top: calc(100% + 0.4rem);
        width: 100%;
        z-index: 9999;
    }
    body > .custom-select-menu.is-portal {
        position: fixed;
    }
    #wizardForm .custom-select.is-open .custom-select-menu,
    body > .custom-select-menu.is-portal.is-open {
        display: block;
    }
    #wizardForm .custom-select-group + .custom-select-group,
    body > .custom-select-menu .custom-select-group + .custom-select-group {
        border-top: 1px solid #f1f5f9;
        margin-top: 0.25rem;
        padding-top: 0.25rem;
    }
    #wizardForm .custom-select-group-label,
    body > .custom-select-menu .custom-select-group-label {
        color: #C81424;
        font-size: 0.68rem;
        font-weight: 800;
        letter-spacing: 0.02em;
        padding: 0.45rem 0.65rem 0.3rem;
        text-transform: uppercase;
    }
    #wizardForm .custom-select-option,
    body > .custom-select-menu .custom-select-option {
        background: transparent;
        border-radius: 0.6rem;
        color: #111827;
        display: block;
        font-size: 0.86rem;
        font-weight: 600;
        min-height: 2.35rem;
        padding: 0.55rem 0.7rem;
        text-align: left;
        transition: background-color 0.16s ease, color 0.16s ease;
        width: 100%;
    }
    #wizardForm .custom-select-option:hover,
    #wizardForm .custom-select-option:focus-visible,
    body > .custom-select-menu .custom-select-option:hover,
    body > .custom-select-menu .custom-select-option:focus-visible {
        background: #fff1f2;
        color: #94111B;
        outline: none;
    }
    #wizardForm .custom-select-option.is-selected,
    body > .custom-select-menu .custom-select-option.is-selected {
        background: #fff1f2;
        box-shadow: inset 0 0 0 1px #fecdd3;
        color: #94111B;
        font-weight: 800;
    }
    #wizardForm .custom-select-option.is-selected:hover,
    #wizardForm .custom-select-option.is-selected:focus-visible,
    body > .custom-select-menu .custom-select-option.is-selected:hover,
    body > .custom-select-menu .custom-select-option.is-selected:focus-visible {
        background: #ffe4e6;
        color: #7f1d1d;
    }
    #wizardForm .custom-select-option.is-placeholder:not(.is-selected),
    body > .custom-select-menu .custom-select-option.is-placeholder:not(.is-selected) {
        color: #64748b;
    }
    #wizardForm .custom-select-option:disabled,
    body > .custom-select-menu .custom-select-option:disabled {
        color: #94a3b8;
        cursor: not-allowed;
    }
    .dark #wizardForm .custom-select-trigger {
        background: #1a1a1a;
        border-color: #444;
        color: #ffffff;
    }
    .dark #wizardForm .custom-select-trigger:hover {
        background: #202020;
        border-color: #555;
    }
    .dark #wizardForm .custom-select.is-open .custom-select-trigger {
        background: #1a1a1a;
        border-color: #C81424;
        box-shadow: 0 0 0 3px rgba(200, 20, 36, 0.18);
    }
    .dark #wizardForm .custom-select.is-disabled .custom-select-trigger {
        background: #222;
        color: #71717a;
    }
    .dark #wizardForm .custom-select-menu,
    .dark body > .custom-select-menu {
        background: #1a1a1a;
        border-color: #333;
        box-shadow: 0 18px 38px rgba(0, 0, 0, 0.45);
    }
    .dark #wizardForm .custom-select-group + .custom-select-group,
    .dark body > .custom-select-menu .custom-select-group + .custom-select-group {
        border-top-color: #2a2a2a;
    }
    .dark #wizardForm .custom-select-option,
    .dark body > .custom-select-menu .custom-select-option {
        color: #f8fafc;
    }
    .dark #wizardForm .custom-select-option:hover,
    .dark #wizardForm .custom-select-option:focus-visible,
    .dark body > .custom-select-menu .custom-select-option:hover,
    .dark body > .custom-select-menu .custom-select-option:focus-visible {
        background: rgba(200, 20, 36, 0.18);
        color: #fecdd3;
    }
    .dark #wizardForm .custom-select-option.is-selected,
    .dark body > .custom-select-menu .custom-select-option.is-selected {
        background: rgba(200, 20, 36, 0.20);
        box-shadow: inset 0 0 0 1px rgba(254, 205, 211, 0.20);
        color: #fecdd3;
    }
    .dark #wizardForm .custom-select-option.is-selected:hover,
    .dark #wizardForm .custom-select-option.is-selected:focus-visible,
    .dark body > .custom-select-menu .custom-select-option.is-selected:hover,
    .dark body > .custom-select-menu .custom-select-option.is-selected:focus-visible {
        background: rgba(200, 20, 36, 0.28);
        color: #fff1f2;
    }
    .color-finish-row {
        align-items: center;
        display: grid;
        gap: 0.5rem;
        grid-template-columns: minmax(0, 1fr) repeat(3, max-content);
        width: 100%;
    }
    .color-select-compact {
        min-height: 2.75rem;
        width: 100%;
    }
    .color-option-pill {
        align-items: center;
        display: inline-flex;
        justify-content: center;
        min-height: 2.5rem;
        min-width: max-content;
        text-align: center;
        white-space: nowrap;
    }
    .pill-btn.selected {
        background-color: #fff1f2 !important;
        border-color: #fecdd3 !important;
        box-shadow: 0 8px 18px rgba(200, 20, 36, 0.10);
        color: #94111B !important;
    }
    .pill-btn.selected:hover {
        background-color: #ffe4e6 !important;
        border-color: #fda4af !important;
        color: #7f1d1d !important;
    }
    .color-option-pill.selected {
        background-color: #fff1f2 !important;
        border-color: #fecdd3 !important;
        box-shadow: 0 8px 18px rgba(200, 20, 36, 0.10);
        color: #94111B !important;
    }
    .color-option-pill.selected:hover {
        background-color: #ffe4e6 !important;
        border-color: #fda4af !important;
        color: #7f1d1d !important;
    }
    button[onclick*="inputPriceType"].selected,
    button[onclick*="inputCurrency"].selected {
        background-color: #fff1f2 !important;
        border-color: #fecdd3 !important;
        color: #94111B !important;
        box-shadow: 0 8px 18px rgba(200, 20, 36, 0.10);
    }
    button[onclick*="inputPriceType"].selected:hover,
    button[onclick*="inputCurrency"].selected:hover {
        background-color: #ffe4e6 !important;
        border-color: #fda4af !important;
        color: #7f1d1d !important;
    }
    .dark #wizardForm .pill-btn.selected,
    .dark #wizardForm .color-option-pill.selected,
    .dark #wizardForm button[onclick*="inputPriceType"].selected,
    .dark #wizardForm button[onclick*="inputCurrency"].selected {
        background-color: rgba(200, 20, 36, 0.20) !important;
        border-color: rgba(254, 205, 211, 0.22) !important;
        color: #fecdd3 !important;
        box-shadow: 0 8px 18px rgba(200, 20, 36, 0.14);
    }
    .dark #wizardForm .pill-btn.selected:hover,
    .dark #wizardForm .color-option-pill.selected:hover,
    .dark #wizardForm button[onclick*="inputPriceType"].selected:hover,
    .dark #wizardForm button[onclick*="inputCurrency"].selected:hover {
        background-color: rgba(200, 20, 36, 0.28) !important;
        border-color: rgba(254, 205, 211, 0.30) !important;
        color: #fff1f2 !important;
    }
    #wizardForm input[type="checkbox"].peer:checked + span {
        background: #fff1f2 !important;
        border-color: #fecdd3 !important;
        box-shadow: 0 8px 18px rgba(200, 20, 36, 0.08);
    }
    #wizardForm input[type="checkbox"].peer:checked + span + span {
        color: #94111B !important;
        font-weight: 800;
    }
    .dark #wizardForm input[type="checkbox"].peer:checked + span {
        background: rgba(200, 20, 36, 0.20) !important;
        border-color: rgba(254, 205, 211, 0.22) !important;
    }
    .dark #wizardForm input[type="checkbox"].peer:checked + span + span {
        color: #fecdd3 !important;
    }

    /* Dark mode polish pentru formularul de publicare: culori mai calme, fara schimbari de layout/comportament. */
    @media (prefers-color-scheme: dark) {
        .publish-form-shell .wizard-card {
            background: #181b20;
            border-color: rgba(148, 163, 184, 0.16);
            box-shadow: 0 24px 70px rgba(0, 0, 0, 0.36);
        }
        .publish-form-shell > .flex:first-child p,
        #wizardForm label {
            color: #cbd5e1 !important;
        }
        #wizardForm .step-content > h2,
        .publish-form-shell h1 {
            color: #f8fafc !important;
        }
        .publish-form-shell > .flex:first-child > div:last-child {
            background: #181b20 !important;
            border-color: rgba(148, 163, 184, 0.16) !important;
            box-shadow: 0 14px 34px rgba(0, 0, 0, 0.26);
        }
        .publish-form-shell .step-dot {
            background: #252a31 !important;
            color: #94a3b8 !important;
        }
        .publish-form-shell .step-dot.scale-110 {
            background: #C81424 !important;
            color: #ffffff !important;
            box-shadow: 0 10px 22px rgba(200, 20, 36, 0.28);
        }
        #wizardForm input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
        #wizardForm textarea,
        #wizardForm select {
            background-color: #20242b !important;
            border-color: #3a414b !important;
            color: #f8fafc !important;
            caret-color: #fb7185;
        }
        #wizardForm select {
            background-image: url("data:image/svg+xml,%3Csvg width='20' height='20' viewBox='0 0 20 20' fill='none' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M5.5 7.75L10 12.25L14.5 7.75' stroke='%23cbd5e1' stroke-width='1.8' stroke-linecap='round' stroke-linejoin='round'/%3E%3C/svg%3E");
        }
        #wizardForm input::placeholder,
        #wizardForm textarea::placeholder {
            color: #94a3b8;
            opacity: 1;
        }
        #wizardForm input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):hover,
        #wizardForm textarea:hover,
        #wizardForm select:hover {
            background-color: #252a32 !important;
            border-color: #4b5563 !important;
        }
        #wizardForm input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]):focus,
        #wizardForm textarea:focus,
        #wizardForm select:focus {
            background-color: #252a32 !important;
            border-color: #fb7185 !important;
            box-shadow: 0 0 0 3px rgba(251, 113, 133, 0.18) !important;
        }
        #wizardForm input:disabled,
        #wizardForm select:disabled,
        #wizardForm textarea:disabled {
            background-color: #171a1f !important;
            border-color: #2b3139 !important;
            color: #717b89 !important;
            opacity: 1;
        }
        #wizardForm option {
            background-color: #1b1f25;
            color: #f8fafc;
        }
        #wizardForm .custom-select-trigger {
            background: #20242b;
            border-color: #3a414b;
            color: #f8fafc;
        }
        #wizardForm .custom-select-chevron {
            color: #94a3b8;
        }
        #wizardForm .custom-select-trigger:hover {
            background: #252a32;
            border-color: #4b5563;
        }
        #wizardForm .custom-select.is-open .custom-select-trigger {
            background: #252a32;
            border-color: #fb7185;
            box-shadow: 0 0 0 3px rgba(251, 113, 133, 0.18);
        }
        #wizardForm .custom-select.is-disabled .custom-select-trigger {
            background: #171a1f;
            border-color: #2b3139;
            color: #717b89;
        }
        #wizardForm .custom-select-menu,
        body > .custom-select-menu {
            background: #171a1f;
            border-color: rgba(148, 163, 184, 0.22);
            box-shadow: 0 22px 48px rgba(0, 0, 0, 0.52);
        }
        #wizardForm .custom-select-group + .custom-select-group,
        body > .custom-select-menu .custom-select-group + .custom-select-group {
            border-top-color: rgba(148, 163, 184, 0.12);
        }
        #wizardForm .custom-select-group-label,
        body > .custom-select-menu .custom-select-group-label {
            color: #fb7185;
        }
        #wizardForm .custom-select-option,
        body > .custom-select-menu .custom-select-option {
            color: #e5e7eb;
        }
        #wizardForm .custom-select-option:hover,
        #wizardForm .custom-select-option:focus-visible,
        body > .custom-select-menu .custom-select-option:hover,
        body > .custom-select-menu .custom-select-option:focus-visible {
            background: rgba(200, 20, 36, 0.16);
            color: #fff1f2;
        }
        #wizardForm .custom-select-option.is-selected,
        body > .custom-select-menu .custom-select-option.is-selected {
            background: rgba(200, 20, 36, 0.24);
            box-shadow: inset 0 0 0 1px rgba(251, 113, 133, 0.36);
            color: #ffe4e6;
        }
        #wizardForm .pill-btn:not(.selected),
        #wizardForm .color-option-pill:not(.selected),
        #wizardForm button[onclick*="inputPriceType"]:not(.selected),
        #wizardForm button[onclick*="inputCurrency"]:not(.selected) {
            background-color: #20242b !important;
            border-color: #3a414b !important;
            color: #cbd5e1 !important;
        }
        #wizardForm .pill-btn:not(.selected):hover,
        #wizardForm .color-option-pill:not(.selected):hover,
        #wizardForm button[onclick*="inputPriceType"]:not(.selected):hover,
        #wizardForm button[onclick*="inputCurrency"]:not(.selected):hover {
            background-color: #2a2024 !important;
            border-color: rgba(251, 113, 133, 0.45) !important;
            color: #ffe4e6 !important;
        }
        #wizardForm .pill-btn.selected,
        #wizardForm .color-option-pill.selected,
        #wizardForm button[onclick*="inputPriceType"].selected,
        #wizardForm button[onclick*="inputCurrency"].selected {
            background-color: rgba(200, 20, 36, 0.24) !important;
            border-color: rgba(251, 113, 133, 0.40) !important;
            color: #ffe4e6 !important;
            box-shadow: 0 10px 24px rgba(200, 20, 36, 0.20);
        }
        #wizardForm input[type="checkbox"].peer + span {
            background: #20242b !important;
            border-color: #3a414b !important;
        }
        #wizardForm input[type="checkbox"].peer + span + span {
            color: #cbd5e1 !important;
        }
        #wizardForm input[type="checkbox"].peer:checked + span {
            background: rgba(200, 20, 36, 0.24) !important;
            border-color: rgba(251, 113, 133, 0.40) !important;
            box-shadow: 0 10px 24px rgba(200, 20, 36, 0.18);
        }
        #wizardForm input[type="checkbox"].peer:checked + span + span {
            color: #ffe4e6 !important;
        }
        #wizardForm #imageInput ~ div {
            background: #20242b !important;
            border-color: #3a414b !important;
        }
        #wizardForm #imageInput ~ div:hover,
        #wizardForm .group:hover #imageInput ~ div {
            background: #252a32 !important;
            border-color: rgba(251, 113, 133, 0.45) !important;
        }
        #wizardForm #imageInput ~ div > div {
            background: #252a31 !important;
            box-shadow: 0 10px 24px rgba(0, 0, 0, 0.22);
        }
        #wizardForm input[name="price_value"] {
            box-shadow: 0 0 0 1px #3a414b !important;
        }
        #wizardForm input[name="price_value"]:focus {
            box-shadow: 0 0 0 3px rgba(251, 113, 133, 0.18) !important;
        }
        #wizardForm input[name="price_value"] + div {
            background: #171a1f !important;
            border-color: rgba(148, 163, 184, 0.18) !important;
        }
        #wizardForm .step-content[data-step="3"] .mb-5.flex.items-start {
            background: #20242b !important;
            border-color: #3a414b !important;
        }
        #wizardForm .wizard-footer {
            background: #171a1f !important;
            border-color: rgba(148, 163, 184, 0.14) !important;
        }
        #imageError {
            background: rgba(127, 29, 29, 0.24);
            border-color: rgba(248, 113, 113, 0.34);
            color: #fecaca;
        }
    }
    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }

    /* Ajustări vizuale doar pentru pasul 1: câmpuri mai mari și fără senzația de formular miniaturizat. */
    #wizardForm .step-content[data-step="1"] .custom-select-trigger {
        border-radius: 1rem;
        font-size: 0.95rem;
        font-weight: 750;
        height: 3.15rem;
        padding-left: 1rem;
        padding-right: 0.85rem;
    }
    #wizardForm .step-content[data-step="1"] .custom-select-label {
        line-height: 1.15;
    }
    #wizardForm .step-content[data-step="1"] label {
        line-height: 1.2;
    }

    @media (max-width: 640px) {
        /*
         * Mobile-only spacing fix pentru toți cei 3 pași.
         * Reducem spațiul dintre ecran-card și dintre câmpuri-marginea cardului,
         * fără să schimbăm logica formularului.
         */
        .publish-form-shell {
            padding-left: 0.45rem !important;
            padding-right: 0.45rem !important;
        }
        #wizardForm .wizard-card {
            border-radius: 1rem;
        }
        #wizardForm .step-content {
            padding: 0.95rem !important;
        }
        #wizardForm .step-content > h2 {
            font-size: 1.35rem;
            line-height: 1.2;
            margin-bottom: 1.15rem;
        }
        #wizardForm .step-content[data-step="1"] > .space-y-8 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 1.45rem;
        }
        #wizardForm .step-content[data-step="1"] .space-y-6 > :not([hidden]) ~ :not([hidden]),
        #wizardForm .step-content[data-step="2"] .space-y-4 > :not([hidden]) ~ :not([hidden]) {
            margin-top: 0.85rem;
        }
        #wizardForm .wizard-footer {
            padding: 0.85rem !important;
        }
        #wizardForm .wizard-footer #nextBtn,
        #wizardForm .wizard-footer #submitBtn {
            padding-left: 1.35rem;
            padding-right: 1.35rem;
        }
        #wizardForm label {
            letter-spacing: 0.08em;
        }
        #wizardForm input,
        #wizardForm textarea {
            font-size: 0.9rem;
        }
        #wizardForm input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]) {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }
        #wizardForm textarea {
            padding-left: 0.85rem;
            padding-right: 0.85rem;
        }

        #wizardForm select {
            background-position: right 0.55rem center;
            padding-right: 1.75rem;
        }
        .color-finish-row {
            gap: 0.25rem;
            grid-template-columns: minmax(0, 1fr) repeat(3, max-content);
        }
        .color-select-compact {
            font-size: 0.9rem;
        }
        .color-option-pill {
            font-size: 0.78rem;
            min-height: 2.55rem;
            padding-left: 0.55rem;
            padding-right: 0.55rem;
        }
        #wizardForm .custom-select-trigger {
            font-size: 0.75rem;
            gap: 0.35rem;
            height: 2.6rem;
            padding-left: 0.65rem;
            padding-right: 0.55rem;
        }
        #wizardForm .step-content[data-step="1"] .custom-select-trigger {
            font-size: 0.9rem;
            gap: 0.4rem;
            height: 3rem;
            padding-left: 0.85rem;
            padding-right: 0.7rem;
        }
        #wizardForm .custom-select.has-leading-icon .custom-select-trigger {
            padding-left: 2.2rem;
        }
        #wizardForm .custom-select-menu,
        body > .custom-select-menu {
            border-radius: 0.8rem;
            max-height: min(17rem, 52vh);
        }
        #wizardForm .custom-select-option,
        body > .custom-select-menu .custom-select-option {
            font-size: 0.78rem;
            min-height: 2.2rem;
            padding: 0.5rem 0.6rem;
        }
    }
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
    const wizardForm = document.getElementById('wizardForm');
    const countySelect = document.getElementById('county-select');
    const localitySelect = document.getElementById('locality-select');
    const localityBaseUrl = "{{ url('/api/localities') }}";
    const presetLocalityId = "{{ old('locality_id') }}";
    const serverValidationErrors = @json($errors->messages());
    const customSelects = new Map();

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
            if(parseInt(s.dataset.step) === currentStep) {
                activeStep = s;
                s.classList.remove('hidden');
                setTimeout(() => s.classList.remove('opacity-0'), 50);
            } else {
                s.classList.add('hidden', 'opacity-0');
            }
        });
        dots.forEach((dot, idx) => {
            if (idx + 1 === currentStep) {
                dot.className = 'step-dot w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold bg-slate-900 text-white transition-all scale-110 shadow-md';
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

        if (scrollToStart && activeStep) {
            setTimeout(() => scrollToStepStart(activeStep), 80);
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

    function restoreCustomSelectMenu(state) {
        const { root, menu } = state;
        menu.classList.remove('is-portal', 'is-open');
        menu.removeAttribute('style');

        if (menu.parentElement !== root) {
            root.appendChild(menu);
        }
    }

    function closeCustomSelect(state) {
        const { root, trigger } = state;
        if (state.pendingOpenTimer) {
            clearTimeout(state.pendingOpenTimer);
            state.pendingOpenTimer = null;
        }
        root.classList.remove('is-open');
        trigger.setAttribute('aria-expanded', 'false');
        restoreCustomSelectMenu(state);
    }

    function closeCustomSelects(except = null) {
        customSelects.forEach((state) => {
            const { root } = state;
            if (root === except) return;
            closeCustomSelect(state);
        });
    }

    function getSelectLabel(select) {
        const selected = select.selectedOptions?.[0] || select.options?.[0];
        return selected ? selected.textContent.trim() : '';
    }

    function setCustomSelectInvalid(select, isInvalid) {
        const state = customSelects.get(select);
        if (!state) return;

        state.root.classList.toggle('is-invalid', isInvalid);
        if (isInvalid) {
            state.trigger.setAttribute('aria-invalid', 'true');
        } else {
            state.trigger.removeAttribute('aria-invalid');
        }
    }

    function createCustomSelectOption(select, option) {
        const item = document.createElement('button');
        item.type = 'button';
        item.className = 'custom-select-option';
        item.textContent = option.textContent.trim();
        item.dataset.value = option.value;
        item.setAttribute('role', 'option');
        item.setAttribute('aria-selected', option.selected ? 'true' : 'false');

        if (option.value === '') item.classList.add('is-placeholder');
        if (option.selected && option.value !== '') item.classList.add('is-selected');
        if (option.disabled || option.parentElement?.disabled) item.disabled = true;

        item.addEventListener('click', () => {
            if (select.disabled || item.disabled) return;

            select.value = option.value;
            select.dispatchEvent(new Event('input', { bubbles: true }));
            select.dispatchEvent(new Event('change', { bubbles: true }));
            syncCustomSelect(select);
            setCustomSelectInvalid(select, !select.checkValidity());
            closeCustomSelects();
            customSelects.get(select)?.trigger.focus();
        });

        item.addEventListener('keydown', (event) => {
            const options = Array.from(customSelects.get(select)?.menu.querySelectorAll('.custom-select-option:not(:disabled)') || []);
            const index = options.indexOf(item);

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                options[Math.min(index + 1, options.length - 1)]?.focus();
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                options[Math.max(index - 1, 0)]?.focus();
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeCustomSelects();
                customSelects.get(select)?.trigger.focus();
            } else if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                item.click();
            }
        });

        return item;
    }

    function syncCustomSelect(select) {
        const state = customSelects.get(select);
        if (!state) return;

        const { root, trigger, label, menu } = state;
        label.textContent = getSelectLabel(select);
        trigger.disabled = select.disabled;
        trigger.setAttribute('aria-disabled', select.disabled ? 'true' : 'false');
        root.classList.toggle('is-disabled', select.disabled);
        setCustomSelectInvalid(select, select.getAttribute('aria-invalid') === 'true');

        menu.innerHTML = '';
        Array.from(select.children).forEach(child => {
            if (child.tagName === 'OPTGROUP') {
                const group = document.createElement('div');
                group.className = 'custom-select-group';

                const groupLabel = document.createElement('div');
                groupLabel.className = 'custom-select-group-label';
                groupLabel.textContent = child.label;
                group.appendChild(groupLabel);

                Array.from(child.children).forEach(option => {
                    group.appendChild(createCustomSelectOption(select, option));
                });

                menu.appendChild(group);
                return;
            }

            if (child.tagName === 'OPTION') {
                menu.appendChild(createCustomSelectOption(select, child));
            }
        });
    }

    function enhanceWizardSelect(select) {
        if (!select || customSelects.has(select)) return;

        const root = document.createElement('div');
        root.className = 'custom-select';
        if (select.parentElement?.querySelector(':scope > span')) {
            root.classList.add('has-leading-icon');
        }

        const trigger = document.createElement('button');
        trigger.type = 'button';
        trigger.className = 'custom-select-trigger';
        trigger.setAttribute('aria-haspopup', 'listbox');
        trigger.setAttribute('aria-expanded', 'false');

        const label = document.createElement('span');
        label.className = 'custom-select-label';

        const icon = document.createElement('span');
        icon.className = 'custom-select-chevron';
        icon.innerHTML = '<svg viewBox="0 0 20 20" aria-hidden="true"><path d="M6 8l4 4 4-4"/></svg>';

        const menu = document.createElement('div');
        menu.className = 'custom-select-menu';
        menu.setAttribute('role', 'listbox');

        trigger.append(label, icon);
        root.append(trigger, menu);

        select.classList.add('native-select-hidden');
        select.setAttribute('tabindex', '-1');
        select.insertAdjacentElement('afterend', root);

        const state = { root, trigger, label, menu, pendingOpenTimer: null };
        customSelects.set(select, state);
        syncCustomSelect(select);

        function estimateMenuHeight() {
            const optionCount = menu.querySelectorAll('.custom-select-option').length;
            const groupCount = menu.querySelectorAll('.custom-select-group-label').length;
            const estimatedHeight = 18 + optionCount * 38 + groupCount * 24;

            return Math.min(280, Math.max(180, estimatedHeight));
        }

        function needsScrollBeforeOpen() {
            const rect = trigger.getBoundingClientRect();
            const spaceBelow = window.innerHeight - rect.bottom;

            return spaceBelow < estimateMenuHeight() + 24;
        }

        function positionPortalMenu() {
            const rect = trigger.getBoundingClientRect();
            const viewportPadding = 16;
            const gap = 6;
            const availableBelow = Math.max(120, window.innerHeight - rect.bottom - gap - viewportPadding);
            const maxHeight = Math.min(304, estimateMenuHeight(), availableBelow);

            menu.style.left = `${rect.left}px`;
            menu.style.top = `${rect.bottom + gap}px`;
            menu.style.width = `${rect.width}px`;
            menu.style.maxHeight = `${maxHeight}px`;
            menu.style.zIndex = '9999';
        }

        function openCustomSelect() {
            closeCustomSelects(root);
            document.body.appendChild(menu);
            menu.classList.add('is-portal', 'is-open');
            root.classList.add('is-open');
            trigger.setAttribute('aria-expanded', 'true');
            positionPortalMenu();

            const selected = menu.querySelector('.custom-select-option.is-selected:not(:disabled)');
            const first = menu.querySelector('.custom-select-option:not(:disabled)');
            setTimeout(() => (selected || first)?.focus(), 0);
        }

        function openCustomSelectWhenReady() {
            if (state.pendingOpenTimer) {
                clearTimeout(state.pendingOpenTimer);
                state.pendingOpenTimer = null;
            }

            closeCustomSelects(root);

            if (!needsScrollBeforeOpen()) {
                openCustomSelect();
                return;
            }

            trigger.scrollIntoView({ behavior: 'smooth', block: 'center' });
            state.pendingOpenTimer = setTimeout(() => {
                state.pendingOpenTimer = null;
                if (!select.disabled) {
                    openCustomSelect();
                }
            }, 280);
        }

        trigger.addEventListener('click', () => {
            if (select.disabled) return;

            const willOpen = !root.classList.contains('is-open');
            if (willOpen) {
                openCustomSelectWhenReady();
            } else {
                closeCustomSelect(state);
            }
        });

        trigger.addEventListener('keydown', (event) => {
            if (event.key !== 'ArrowDown' && event.key !== 'Enter' && event.key !== ' ') return;
            event.preventDefault();
            trigger.click();
        });

        select.addEventListener('change', () => {
            syncCustomSelect(select);
            setCustomSelectInvalid(select, !select.checkValidity());
        });

        const observer = new MutationObserver(() => syncCustomSelect(select));
        observer.observe(select, {
            attributes: true,
            attributeFilter: ['disabled', 'class', 'aria-invalid'],
            childList: true,
            subtree: true,
        });

        window.addEventListener('scroll', () => {
            if (root.classList.contains('is-open')) positionPortalMenu();
        }, true);
        window.addEventListener('resize', () => {
            if (root.classList.contains('is-open')) positionPortalMenu();
        });
    }

    wizardForm?.querySelectorAll('select').forEach(enhanceWizardSelect);

    document.addEventListener('click', (event) => {
        if (!event.target.closest('#wizardForm .custom-select') && !event.target.closest('body > .custom-select-menu')) {
            closeCustomSelects();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeCustomSelects();
        }
    });

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
        setCustomSelectInvalid(input, true);

        const clear = () => {
            if (input.checkValidity()) {
                input.classList.remove('ring-2', 'ring-red-500', 'border-red-500');
                input.removeAttribute('aria-invalid');
                setCustomSelectInvalid(input, false);
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
        setCustomSelectInvalid(input, false);
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
            setCustomSelectInvalid(input, true);
        }

        return {
            target: wrapper?.querySelector('.pill-btn') || customSelects.get(input)?.root || input || error,
            message,
        };
    }

    function scrollToValidationTarget(target) {
        if (!target) return;

        const customState = target.tagName === 'SELECT' ? customSelects.get(target) : null;
        const visibleTarget = customState?.root || target;

        visibleTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        setTimeout(() => {
            const focusTarget = customState?.trigger || visibleTarget;
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

    // === 4. CASCADING SELECTS (Brand -> Model -> Generation -> Year) ===
    // IMPORTANT: $carData trebuie sa fie pe ID-uri:
    // carData[brand_id] = [{id, name, generations:[{id,name,start,end}]}]
    const carData = @json($carData ?? []);
    const brandSel = document.getElementById('brandSelect');
    const modelSel = document.getElementById('modelSelect');
    const genSel = document.getElementById('generationSelect');
    const yearSel = document.getElementById('yearSelect');

    function populateYears(start, end) {
        yearSel.innerHTML = '<option value="">An fabricație</option>';
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
        resetSelect(genSel, 'Generație');
        resetSelect(yearSel, 'An fabricație');

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

        resetSelect(genSel, 'Generație');
        resetSelect(yearSel, 'An fabricație');

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
            div.className = 'aspect-square overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm relative dark:border-[#333] dark:bg-[#252525]';
            div.innerHTML = `
                <img src="" class="w-full h-full object-cover" alt="">
                ${primaryIndex === index ? '<span class="absolute left-2 top-2 rounded-full bg-white/90 px-2.5 py-1 text-[10px] font-bold text-slate-700 shadow-sm backdrop-blur">Principală</span>' : '<button type="button" data-primary-index="' + index + '" title="Principală" aria-label="Principală" class="absolute left-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-slate-700 shadow-sm backdrop-blur transition hover:bg-white hover:text-slate-950">★</button>'}
                <button type="button" data-remove-index="${index}" title="Șterge poza" aria-label="Șterge poza" class="absolute right-2 top-2 flex h-8 w-8 items-center justify-center rounded-full bg-white/90 text-lg font-bold leading-none text-slate-700 shadow-sm backdrop-blur transition hover:bg-white hover:text-red-600">×</button>
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
