@extends('layouts.app')

@section('title', 'Adaugă anunț nou')

@section('content')

<div class="max-w-5xl mx-auto mt-6 mb-24 px-4 sm:px-6">

    {{-- HEADER --}}
    <div class="flex flex-col md:flex-row justify-between items-end mb-8 gap-6">
        <div>
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight leading-tight">
                Vinde mașina ta
            </h1>
            <p class="text-gray-500 dark:text-gray-400 text-base mt-2">Transformă mașina în bani în 3 pași simpli.</p>
        </div>
        
        {{-- STEPPER --}}
        <div class="flex items-center gap-3 bg-white dark:bg-[#1E1E1E] px-4 py-2 rounded-full shadow-sm border border-gray-100 dark:border-[#333]">
            <div id="step-dot-1" class="step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold bg-blue-600 text-white transition-all shadow-blue-500/30 shadow-lg">1</div>
            <div class="w-10 h-0.5 bg-gray-100 dark:bg-gray-700 rounded-full"></div>
            <div id="step-dot-2" class="step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all border border-transparent">2</div>
            <div class="w-10 h-0.5 bg-gray-100 dark:bg-gray-700 rounded-full"></div>
            <div id="step-dot-3" class="step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all border border-transparent">3</div>
        </div>
    </div>

    <form action="{{ route('services.store') }}" method="POST" enctype="multipart/form-data" id="wizardForm">
        @csrf

        {{-- CONTAINER PRINCIPAL --}}
        <div class="bg-white dark:bg-[#1E1E1E] rounded-3xl shadow-2xl shadow-gray-200/50 dark:shadow-none border border-gray-100 dark:border-[#2A2A2A] overflow-hidden min-h-[500px] flex flex-col relative">

            {{-- ================= PASUL 1: IDENTIFICARE & CONFIGURAȚIE ================= --}}
            <div class="step-content animate-fade-in" data-step="1">
                
                {{-- SECTIUNE: IDENTIFICARE (Marcă, Model, An) --}}
                <div class="p-6 md:p-8 border-b border-gray-100 dark:border-[#2A2A2A]">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-blue-100 text-blue-600 dark:bg-blue-900/30 dark:text-blue-400">🚙</span> 
                        Identificare Vehicul
                    </h2>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                        {{-- Brand --}}
                        <div class="relative group">
                            <input type="hidden" name="brand" id="brandText" value="">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Marca</label>
                            <select name="brand_id" id="brandSelect" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all appearance-none cursor-pointer font-medium" required>
                                <option value="">Selectează</option>
                                @foreach($brands as $brand)
                                    <option value="{{ $brand->id }}" data-name="{{ $brand->name }}">{{ $brand->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Model --}}
                        <div class="relative group">
                            <input type="hidden" name="model" id="modelText" value="">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Model</label>
                            <select name="model_id" id="modelSelect" disabled class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] outline-none transition-all appearance-none font-medium">
                                <option value="">-</option>
                            </select>
                        </div>

                        {{-- Generatie --}}
                        <div class="relative group">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Generație</label>
                            <select name="car_generation_id" id="generationSelect" disabled class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] outline-none transition-all appearance-none font-medium">
                                <option value="">-</option>
                            </select>
                        </div>
                        
                        {{-- An --}}
                        <div class="relative group">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">An Fabricație</label>
                            <select name="an_fabricatie" id="yearSelect" disabled class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-gray-900 dark:text-white disabled:opacity-50 disabled:bg-gray-100 dark:disabled:bg-[#222] outline-none transition-all appearance-none font-medium" required>
                                <option value="">-</option>
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="category_id" value="{{ $autoCategoryId }}">
                </div>

                {{-- SECTIUNE: SPECIFICAȚII (Dropdown-uri) --}}
                <div class="p-6 md:p-8 bg-gray-50/50 dark:bg-[#252525]/30">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-green-100 text-green-600 dark:bg-green-900/30 dark:text-green-400">⚙️</span> 
                        Configurație
                    </h2>

                    {{-- GRID 4 Coloane pentru specificatiile tehnice --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        {{-- Combustibil --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Combustibil</label>
                            <select name="combustibil_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white outline-none focus:border-blue-500 transition-all appearance-none cursor-pointer" required>
                                <option value="">Selectează</option>
                                @foreach($fuels as $fuel)
                                    <option value="{{ $fuel->id }}">{{ $fuel->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Caroserie --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Caroserie</label>
                            <select name="caroserie_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white outline-none focus:border-blue-500 transition-all appearance-none cursor-pointer" required>
                                <option value="">Selectează</option>
                                @foreach($bodies as $body)
                                    <option value="{{ $body->id }}">{{ $body->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Transmisie --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Transmisie</label>
                            <select name="cutie_viteze_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white outline-none focus:border-blue-500 transition-all appearance-none cursor-pointer" required>
                                <option value="">Selectează</option>
                                @foreach($transmissions as $trans)
                                    <option value="{{ $trans->id }}">{{ $trans->nume }}</option>
                                @endforeach
                            </select>
                        </div>

                        {{-- Tractiune --}}
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Tracțiune</label>
                            <select name="tractiune_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white outline-none focus:border-blue-500 transition-all appearance-none cursor-pointer" required>
                                <option value="">Selectează</option>
                                @foreach($tractiuni as $tr)
                                    <option value="{{ $tr->id }}">{{ $tr->nume }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- CULOARE + FINISAJ (Aliniate) --}}
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-4 items-end">
                        <div class="md:col-span-4">
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Culoare</label>
                            <select name="culoare_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white outline-none focus:border-blue-500" required>
                                <option value="">Alege Culoarea</option>
                                @foreach($colors as $color)
                                    <option value="{{ $color->id }}">{{ $color->nume }}</option>
                                @endforeach
                            </select>
                        </div>
                        {{-- Finisaj (Fara label, doar 3 optiuni) --}}
                        <div class="md:col-span-8">
                            <input type="hidden" name="culoare_opt_id" id="inputColorOpt" value="">
                            <div class="flex gap-2">
                                @foreach($colorOpts->take(3) as $opt) {{-- Luam doar primele 3 optiuni daca sunt mai multe --}}
                                    <button type="button" onclick="selectPill('inputColorOpt', '{{ $opt->id }}', this)" class="pill-btn flex-1 px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm font-medium text-gray-600 dark:text-gray-300 hover:border-blue-500 hover:text-blue-500 transition-all">
                                        {{ $opt->nume }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ================= PASUL 2: DETALII TEHNICE & MARKETING ================= --}}
            <div class="step-content hidden opacity-0" data-step="2">
                
                <div class="p-6 md:p-10 border-b border-gray-100 dark:border-[#2A2A2A]">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-orange-100 text-orange-600 dark:bg-orange-900/30 dark:text-orange-400">📊</span> 
                        Date Tehnice & Istoric
                    </h2>
                    
                    {{-- VIN --}}
                    <div class="mb-8">
                        <label class="text-xs font-bold text-gray-500 uppercase mb-2 block ml-1">Serie Șasiu (VIN)</label>
                        <div class="relative">
                            <input type="text" name="vin" placeholder="Ex: WBA33..." maxlength="17" class="w-full pl-5 pr-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-white uppercase font-mono tracking-wider focus:bg-white dark:focus:bg-[#1a1a1a] focus:ring-2 focus:ring-blue-500 outline-none transition-all">
                            <span class="absolute right-4 top-3.5 text-xs text-gray-400 font-medium bg-gray-100 dark:bg-[#333] px-2 py-0.5 rounded">Optional</span>
                        </div>
                    </div>

                    {{-- RANDUL 1: Importata, Avariata, KM, Putere, Capacitate --}}
                    {{-- Pe mobil: 2 coloane. Pe desktop: 5 coloane. --}}
                    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 items-center mb-6">
                        
                        {{-- Checkbox Importata --}}
                        <label class="cursor-pointer flex items-center justify-center gap-2 h-[50px] px-3 rounded-xl border border-gray-200 dark:border-[#444] bg-gray-50 dark:bg-[#252525] hover:bg-white dark:hover:bg-[#333] transition-colors select-none">
                            <input type="checkbox" name="importata" value="1" class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-xs font-bold uppercase text-gray-700 dark:text-gray-300">Importată</span>
                        </label>

                        {{-- Checkbox Avariata --}}
                        <label class="cursor-pointer flex items-center justify-center gap-2 h-[50px] px-3 rounded-xl border border-gray-200 dark:border-[#444] bg-gray-50 dark:bg-[#252525] hover:bg-white dark:hover:bg-[#333] transition-colors select-none">
                            <input type="checkbox" name="avariata" value="1" class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-xs font-bold uppercase text-gray-700 dark:text-gray-300">Avariată</span>
                        </label>

                        {{-- Rulaj --}}
                        <div class="relative col-span-2 lg:col-span-1">
                            <input type="number" name="km" placeholder="Rulaj" class="w-full pl-4 pr-8 py-3 h-[50px] rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium" required>
                            <span class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">km</span>
                        </div>

                        {{-- Putere --}}
                        <div class="relative col-span-1 lg:col-span-1">
                            <input type="number" name="putere" placeholder="Putere" class="w-full pl-4 pr-8 py-3 h-[50px] rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium" required>
                            <span class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">CP</span>
                        </div>

                        {{-- Capacitate --}}
                        <div class="relative col-span-1 lg:col-span-1">
                            <input type="number" name="capacitate_cilindrica" placeholder="Capacitate" class="w-full pl-4 pr-8 py-3 h-[50px] rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500 outline-none transition-all font-medium">
                            <span class="absolute right-3 top-3.5 text-xs font-bold text-gray-400 pointer-events-none">cm³</span>
                        </div>
                    </div>

                    {{-- RANDUL 2: Usi, Locuri --}}
                    <div class="grid grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Număr uși</label>
                            <select name="numar_usi" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm outline-none focus:border-blue-500">
                                <option value="">-</option>
                                @foreach([2,3,4,5] as $usi)
                                    <option value="{{ $usi }}">{{ $usi }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Număr locuri</label>
                            <select name="numar_locuri" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm outline-none focus:border-blue-500">
                                <option value="">-</option>
                                @foreach(range(2,9) as $locuri)
                                    <option value="{{ $locuri }}">{{ $locuri }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    {{-- RANDUL 3: Filtru Particule + Norma (am pus si norma aici ca sa echilibram) --}}
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        {{-- Filtru Particule Checkbox --}}
                        <label class="cursor-pointer flex items-center gap-3 px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-gray-50 dark:bg-[#252525] hover:bg-white dark:hover:bg-[#333] transition-colors select-none h-[50px] mt-auto">
                            <input type="checkbox" name="filtru_particule" value="1" class="w-4 h-4 rounded text-blue-600 border-gray-300 focus:ring-blue-500">
                            <span class="text-sm font-medium text-gray-700 dark:text-gray-300">Are Filtru de Particule (DPF)</span>
                        </label>

                        {{-- Norma Poluare --}}
                        <div>
                             {{-- Label ascuns vizual pe desktop pt aliniere, sau explicit --}}
                             <label class="block text-xs font-bold text-gray-500 uppercase mb-1.5 ml-1">Normă Poluare</label>
                             <select name="norma_poluare_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm text-gray-900 dark:text-white outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="">Alege Norma</option>
                                @foreach($normePoluare as $norma)
                                    <option value="{{ $norma->id }}">{{ $norma->nume }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                </div>

                {{-- MARKETING SECTION --}}
                <div class="p-6 md:p-10">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-purple-100 text-purple-600 dark:bg-purple-900/30 dark:text-purple-400">📢</span> 
                        Descriere Anunț
                    </h2>
                    
                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 ml-1">Titlu Anunț</label>
                            <input type="text" name="title" placeholder="Ex: BMW 320d M-Packet, Unic Proprietar, Fiscal pe loc" class="w-full px-5 py-4 rounded-xl bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-[#444] focus:border-blue-500 focus:ring-4 focus:ring-blue-500/10 outline-none text-gray-900 dark:text-white font-bold text-lg placeholder-gray-300 transition-all" required>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 ml-1">Descriere Detaliată</label>
                            <textarea name="description" rows="10" placeholder="Descrie dotările, istoricul de service, starea tehnică..." class="w-full px-5 py-4 rounded-xl bg-white dark:bg-[#1a1a1a] border border-gray-200 dark:border-[#444] focus:border-blue-500 outline-none text-sm leading-relaxed text-gray-900 dark:text-white resize-y" required></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ================= PASUL 3: FINALIZARE ================= --}}
            <div class="step-content hidden opacity-0" data-step="3">
                
                <div class="p-6 md:p-10">
                    <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-6 flex items-center gap-3">
                        <span class="flex items-center justify-center w-8 h-8 rounded-lg bg-pink-100 text-pink-600 dark:bg-pink-900/30 dark:text-pink-400">📸</span> 
                        Galerie Foto
                    </h2>

                    {{-- DRAG & DROP --}}
                    <div class="mb-10">
                        <div class="relative w-full group">
                            <input type="file" id="imageInput" name="images[]" multiple accept="image/*" class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-20">
                            <div class="flex flex-col items-center justify-center w-full h-52 border-2 border-dashed border-gray-300 dark:border-[#444] rounded-2xl bg-gray-50 dark:bg-[#252525] group-hover:bg-blue-50 dark:group-hover:bg-[#2a2a2a] group-hover:border-blue-400 transition-all">
                                <div class="p-4 bg-white dark:bg-[#333] rounded-full shadow-sm mb-3 group-hover:scale-110 transition-transform">
                                    <svg class="w-8 h-8 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <p class="text-base font-bold text-gray-700 dark:text-gray-300">Click sau trage poze aici</p>
                                <p class="text-xs text-gray-400 mt-1">Maxim 10 imagini (JPG, PNG)</p>
                            </div>
                        </div>
                        <div id="previewContainer" class="grid grid-cols-4 sm:grid-cols-5 md:grid-cols-6 gap-3 mt-6"></div>
                    </div>

                    <div class="grid grid-cols-1 lg:grid-cols-2 gap-10">
                        
                        {{-- PRET --}}
                        <div class="bg-gray-50 dark:bg-[#252525] p-6 rounded-2xl border border-gray-100 dark:border-[#333]">
                            <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Stabilește Prețul</h3>
                            
                            <div class="relative mb-4">
                                <input type="number" name="price_value" step="0.01" placeholder="0" class="w-full pl-5 pr-20 py-4 rounded-xl bg-white dark:bg-[#1a1a1a] border-none text-3xl font-extrabold text-gray-900 dark:text-white outline-none ring-1 ring-gray-200 dark:ring-[#444] focus:ring-2 focus:ring-green-500 placeholder-gray-200" required>
                                
                                <div class="absolute right-3 top-3 bottom-3 flex bg-gray-100 dark:bg-[#333] rounded-lg p-1 border border-gray-200 dark:border-[#444]">
                                    <input type="hidden" name="currency" id="inputCurrency" value="EUR">
                                    <button type="button" onclick="selectPill('inputCurrency', 'EUR', this)" class="pill-btn px-3 flex items-center justify-center text-xs font-bold rounded text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors selected">EUR</button>
                                    <button type="button" onclick="selectPill('inputCurrency', 'RON', this)" class="pill-btn px-3 flex items-center justify-center text-xs font-bold rounded text-gray-500 hover:text-gray-900 dark:text-gray-400 dark:hover:text-white transition-colors">RON</button>
                                </div>
                            </div>

                            <div class="flex gap-2 w-full">
                                <input type="hidden" name="price_type" id="inputPriceType" value="fixed">
                                <button type="button" onclick="selectPill('inputPriceType', 'negotiable', this)" class="pill-btn flex-1 py-2 text-xs font-bold border rounded-lg border-gray-200 dark:border-[#444] bg-white dark:bg-[#1a1a1a] text-gray-500 transition-colors">Negociabil</button>
                                <button type="button" onclick="selectPill('inputPriceType', 'fixed', this)" class="pill-btn flex-1 py-2 text-xs font-bold border rounded-lg border-green-500 bg-green-50 text-green-700 selected">Preț Fix</button>
                            </div>
                        </div>

                        {{-- CONTACT --}}
                        <div>
                            <h3 class="text-sm font-bold text-gray-500 uppercase mb-4">Locație & Contact</h3>
                            <div class="space-y-4">
                                <div>
                                    <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">Telefon</label>
                                    <input type="text" name="phone" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm font-medium" placeholder="07xx xxx xxx" required>
                                </div>
                                <div class="grid grid-cols-2 gap-4">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">Județ</label>
                                        <select id="county-select" name="county_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm font-medium" required>
                                            <option value="">Județ</option>
                                            @foreach ($counties as $county)
                                                <option value="{{ $county->id }}" @selected((string)old('county_id') === (string)$county->id)>{{ $county->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-400 mb-1 ml-1">Localitate</label>
                                        <select id="locality-select" name="locality_id" class="w-full px-4 py-3 rounded-xl border border-gray-200 dark:border-[#444] bg-white dark:bg-[#252525] text-sm font-medium" disabled>
                                            <option value="">Localitate</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                     {{-- GUEST / AUTH SECTION --}}
                    @guest
                    <div class="mt-10 p-6 bg-blue-50 dark:bg-blue-900/10 rounded-2xl border border-blue-100 dark:border-blue-800/20">
                        <div class="flex items-start gap-4 mb-6">
                            <div class="p-2 bg-blue-100 dark:bg-blue-800 rounded-lg text-blue-600 dark:text-blue-200">ℹ️</div>
                            <div>
                                <h4 class="font-bold text-blue-900 dark:text-blue-100">Cont nou automat</h4>
                                <p class="text-sm text-blue-700 dark:text-blue-300 opacity-80 mt-1">Completează datele tale și îți vom crea un cont pentru a gestiona anunțul.</p>
                            </div>
                        </div>
                        
                        <input type="hidden" name="user_type" value="individual">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div class="md:col-span-2">
                                <input type="text" name="name" placeholder="Numele tău complet" class="w-full px-4 py-3 rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-[#1a1a1a] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <input type="email" name="email" placeholder="Adresa de email" class="w-full px-4 py-3 rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-[#1a1a1a] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                            <div>
                                <input type="password" name="password" placeholder="Alege o parolă" class="w-full px-4 py-3 rounded-xl border border-blue-200 dark:border-blue-800 bg-white dark:bg-[#1a1a1a] text-sm outline-none focus:ring-2 focus:ring-blue-500">
                            </div>
                        </div>
                    </div>
                    @endguest

                    @auth
                    <div class="mt-8 flex items-center justify-center gap-2 text-sm text-gray-500">
                        <span class="w-2 h-2 rounded-full bg-green-500"></span>
                        Logat ca <span class="font-bold text-gray-900 dark:text-white">{{ auth()->user()->name }}</span>
                    </div>
                    @endauth

                </div>
            </div>

            {{-- FOOTER NAVIGARE --}}
            <div class="mt-auto px-6 py-5 md:px-10 md:py-6 bg-gray-50 dark:bg-[#1a1a1a] border-t border-gray-100 dark:border-[#2A2A2A] flex justify-between items-center z-10 sticky bottom-0 backdrop-blur-sm bg-opacity-90 dark:bg-opacity-90">
                <button type="button" id="prevBtn" class="hidden text-gray-500 font-bold text-sm px-4 py-2 hover:bg-gray-200 dark:hover:bg-[#333] rounded-xl transition-colors">
                    ← Înapoi
                </button>
                <button type="button" id="nextBtn" class="ml-auto bg-gray-900 dark:bg-white text-white dark:text-black font-bold text-sm px-10 py-3.5 rounded-xl hover:shadow-xl hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    Continuă <span>→</span>
                </button>
                <button type="submit" id="submitBtn" class="hidden ml-auto bg-blue-600 text-white font-bold text-sm px-10 py-3.5 rounded-xl hover:shadow-xl hover:shadow-blue-500/30 hover:scale-[1.02] active:scale-[0.98] transition-all flex items-center gap-2">
                    <span>🚀</span> Publică Anunțul
                </button>
            </div>

        </div>
    </form>
</div>

<style>
    .animate-fade-in { animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
    
    /* Pill logic styling */
    .pill-btn.selected { 
        background-color: #2563EB; 
        color: white !important; 
        border-color: #2563EB; 
        box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.3);
    }
    
    /* Price type logic specific colors */
    button[onclick*="inputPriceType"].selected { background-color: #10B981 !important; color: white !important; border-color: #10B981 !important; box-shadow: none; }
    button[onclick*="inputCurrency"].selected { background-color: #1F2937 !important; color: white !important; border-color: #1F2937 !important; box-shadow: none; }
    .dark button[onclick*="inputCurrency"].selected { background-color: #ffffff !important; color: black !important; border-color: #ffffff !important; }

    input[type=number]::-webkit-inner-spin-button, input[type=number]::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
</style>

<script>
// Codul JavaScript
document.addEventListener('DOMContentLoaded', function() {
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
                dot.className = 'step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold bg-blue-600 text-white transition-all shadow-blue-500/30 shadow-lg scale-110';
            } else if (idx + 1 < currentStep) {
                dot.className = 'step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold bg-green-500 text-white transition-all';
                dot.innerHTML = '✓';
            } else {
                dot.className = 'step-dot w-9 h-9 rounded-full flex items-center justify-center text-sm font-bold bg-gray-100 dark:bg-[#333] text-gray-400 transition-all border border-transparent';
                dot.innerHTML = idx + 1;
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
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }

    function resetLocalities() {
        if (!localitySelect) return;
        localitySelect.innerHTML = '<option value=\"\">Localitate</option>';
        localitySelect.disabled = true;
    }

    function populateLocalities(localities, selectedId, countyId) {
        if (!localitySelect) return;
        localitySelect.innerHTML = '<option value=\"\">Localitate</option>';
        localities.forEach(locality => {
            if (countyId && locality.county_id && String(locality.county_id) !== String(countyId)) return;
            const option = document.createElement('option');
            option.value = locality.id;
            option.textContent = locality.name;
            if (String(selectedId) === String(locality.id)) option.selected = true;
            localitySelect.appendChild(option);
        });
        localitySelect.disabled = false;
    }

    async function loadLocalities(countyId, selectedId = null) {
        if (!countyId) { resetLocalities(); return; }
        try {
            const response = await fetch(`${localityBaseUrl}/${countyId}`);
            const data = await response.json();
            populateLocalities(data, selectedId, countyId);
        } catch (error) { console.error(error); resetLocalities(); }
    }

    window.selectPill = function(inputId, value, btnElement) {
        document.getElementById(inputId).value = value;
        const parent = btnElement.parentElement;
        const siblings = parent.querySelectorAll('.pill-btn');
        siblings.forEach(el => el.classList.remove('selected'));
        btnElement.classList.add('selected');
        // Erori si alte logici daca mai exista
    }

    function validateCurrentStep() {
        let valid = true;
        const currentEl = document.querySelector(`.step-content[data-step="${currentStep}"]`);
        const inputs = currentEl.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(inp => {
            if(!inp.value) {
                valid = false;
                inp.classList.add('ring-2', 'ring-red-500', 'border-red-500');
                inp.addEventListener('change', () => inp.classList.remove('ring-2', 'ring-red-500', 'border-red-500'), {once:true});
            }
        });

        if(currentStep === 1) {
             const genSel = document.getElementById('generationSelect');
             if (!genSel.disabled && !genSel.value) {
                valid = false;
                genSel.classList.add('ring-2', 'ring-red-500', 'border-red-500');
                genSel.addEventListener('change', () => genSel.classList.remove('ring-2', 'ring-red-500', 'border-red-500'), {once:true});
             }
        }
        return valid;
    }

    nextBtn.addEventListener('click', () => { if(validateCurrentStep()) { currentStep++; updateStep(); } });
    prevBtn.addEventListener('click', () => { currentStep--; updateStep(); });

    if (countySelect) { countySelect.addEventListener('change', () => { loadLocalities(countySelect.value); }); }
    if (countySelect && countySelect.value) { loadLocalities(countySelect.value, presetLocalityId); } else { resetLocalities(); }

    const carData = @json($carData ?? []);
    const brandSel = document.getElementById('brandSelect');
    const modelSel = document.getElementById('modelSelect');
    const genSel = document.getElementById('generationSelect');
    const yearSel = document.getElementById('yearSelect');

    function populateYears(start, end) {
        yearSel.innerHTML = '<option value="">-</option>';
        yearSel.disabled = false;
        for(let i = end; i >= start; i--) { yearSel.innerHTML += `<option value="${i}">${i}</option>`; }
    }
    function resetSelect(el, defaultText) {
        el.innerHTML = `<option value="">${defaultText}</option>`;
        el.disabled = true;
        el.value = "";
    }

    brandSel.addEventListener('change', function() {
        const brandId = this.value;
        const brandName = this.options[this.selectedIndex]?.dataset?.name || '';
        document.getElementById('brandText').value = brandName;
        resetSelect(modelSel, '-'); resetSelect(genSel, '-'); resetSelect(yearSel, '-');
        if(brandId && carData[brandId]) {
            modelSel.disabled = false;
            carData[brandId].forEach(m => { modelSel.innerHTML += `<option value="${m.id}" data-name="${m.name}">${m.name}</option>`; });
        }
    });

    modelSel.addEventListener('change', function() {
        const brandId = brandSel.value;
        const modelId = this.value;
        const modelName = this.options[this.selectedIndex]?.dataset?.name || '';
        document.getElementById('modelText').value = modelName;
        resetSelect(genSel, '-'); resetSelect(yearSel, '-');
        if(brandId && modelId && carData[brandId]) {
            const modelObj = carData[brandId].find(x => String(x.id) === String(modelId));
            const generations = modelObj?.generations || [];
            if (generations.length > 0) {
                genSel.disabled = false;
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
                            div.className = 'aspect-square rounded-xl overflow-hidden border border-gray-200 shadow-sm relative group';
                            div.innerHTML = `<img src="${ev.target.result}" class="w-full h-full object-cover transition-transform group-hover:scale-105">`;
                            previewContainer.appendChild(div);
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }
        });
    }

    updateStep();
});
</script>

@endsection