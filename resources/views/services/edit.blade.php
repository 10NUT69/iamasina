@extends('layouts.app')

@section('title', 'Editează anunț - ' . $service->title)

@section('content')

<div class="max-w-3xl mx-auto mt-8 mb-16 px-4 md:px-0">

    <div class="text-center mb-10">
        <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight transition-colors">
            Modifică anunțul
        </h1>
        <p class="text-gray-500 dark:text-gray-400 mt-2 text-lg transition-colors">
            Actualizează detaliile pentru a atrage mai mulți clienți.
        </p>
    </div>

    <form action="{{ route('services.update', $service->id) }}" 
          method="POST" 
          enctype="multipart/form-data"
          class="space-y-8"
          id="editForm">

        @csrf
        @method('PUT')

        {{-- SECȚIUNEA 1: DETALII DE BAZĂ --}}
        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-blue-100 dark:bg-blue-900/30 text-blue-600 p-1.5 rounded-lg">📝</span>
                Detalii de bază
            </h2>
            
            <div class="space-y-6">
                {{-- TITLU --}}
                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Titlul anunțului</label>
                    <input type="text" name="title" value="{{ old('title', $service->title) }}"
                        class="w-full pl-4 pr-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                               bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                               focus:ring-2 focus:ring-primary-end outline-none transition placeholder-gray-400"
                        required>
                    @error('title') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                    {{-- CATEGORIE --}}
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Categoria</label>
                        <div class="relative">
                            <select name="category_id"
                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                           focus:ring-2 focus:ring-primary-end outline-none appearance-none form-select cursor-pointer"
                                    required>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}" {{ old('category_id', $service->category_id) == $category->id ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">
                                        {{ $category->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    {{-- JUDEȚ --}}
                    <div>
                        <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Județ</label>
                        <div class="relative">
                            <select name="county_id"
                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                                           focus:ring-2 focus:ring-primary-end outline-none appearance-none form-select cursor-pointer"
                                    required>
                                @foreach ($counties as $county)
                                    <option value="{{ $county->id }}" {{ old('county_id', $service->county_id) == $county->id ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">
                                        {{ $county->name }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                </svg>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- DETALII MAȘINĂ --}}
                <div class="pt-4 mt-2 border-t border-dashed border-gray-200 dark:border-[#333333]">
                    <h3 class="text-lg font-semibold mb-4 text-gray-900 dark:text-white flex items-center gap-2">
                        <span class="bg-orange-100 dark:bg-orange-900/30 text-orange-600 p-1.5 rounded-lg">🚗</span>
                        Detalii mașină
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-4">
                        {{-- MARCĂ (din DB) --}}
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Marcă</label>
                            <div class="relative">
                                @php
                                    $currentBrand = old('brand', $service->brand);
                                @endphp
                                <select name="brand" id="brandSelect"
                                        class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                               bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                               focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer form-select">
                                    <option value="" class="dark:bg-[#1E1E1E]">Alege marca</option>
                                    @foreach($brands as $brand)
                                        <option value="{{ $brand->name }}"
                                            {{ $currentBrand === $brand->name ? 'selected' : '' }}
                                            class="dark:bg-[#1E1E1E]">
                                            {{ $brand->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- MODEL (din DB, dependent de marcă) --}}
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Model</label>
                            <div class="relative">
                                <select name="model" id="modelSelect"
                                        class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                               bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                               focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer form-select"
                                        disabled>
                                    <option value="" class="dark:bg-[#1E1E1E]">Alege modelul</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-gray-500">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        {{-- AN FABRICAȚIE --}}
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">An fabricație</label>
                            <input type="number" name="year" value="{{ old('year', $service->year) }}"
                                   placeholder="Ex: 2016"
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                          bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                          focus:ring-2 focus:ring-primary-end outline-none transition placeholder-gray-400 no-spinner">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-5 mb-4">
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Kilometraj (km)</label>
                            <input type="number" name="mileage" value="{{ old('mileage', $service->mileage) }}"
                                   placeholder="Ex: 180000"
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                          bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                          focus:ring-2 focus:ring-primary-end outline-none transition placeholder-gray-400 no-spinner">
                        </div>

                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Combustibil</label>
                            <select name="fuel_type"
                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer form-select">
                                <option value="" class="dark:bg-[#1E1E1E]">Alege</option>
                                <option value="benzina"   {{ old('fuel_type', $service->fuel_type) == 'benzina' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Benzină</option>
                                <option value="motorina"  {{ old('fuel_type', $service->fuel_type) == 'motorina' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Motorină</option>
                                <option value="hibrid"    {{ old('fuel_type', $service->fuel_type) == 'hibrid' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Hibrid</option>
                                <option value="electric"  {{ old('fuel_type', $service->fuel_type) == 'electric' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Electric</option>
                                <option value="gaz"       {{ old('fuel_type', $service->fuel_type) == 'gaz' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Gaz</option>
                            </select>
                        </div>

                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Transmisie</label>
                            <select name="transmission"
                                    class="w-full pl-4 pr-10 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                           focus:ring-2 focus:ring-primary-end outline-none transition cursor-pointer form-select">
                                <option value="" class="dark:bg-[#1E1E1E]">Alege</option>
                                <option value="manuala"       {{ old('transmission', $service->transmission) == 'manuala' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Manuală</option>
                                <option value="automata"      {{ old('transmission', $service->transmission) == 'automata' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Automată</option>
                                <option value="semi-automata" {{ old('transmission', $service->transmission) == 'semi-automata' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">Semi-automată</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Caroserie</label>
                            <input type="text" name="body_type" value="{{ old('body_type', $service->body_type) }}"
                                   placeholder="Ex: Hatchback, Sedan, SUV"
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                          bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                          focus:ring-2 focus:ring-primary-end outline-none transition placeholder-gray-400">
                        </div>

                        <div>
                            <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Putere (CP)</label>
                            <input type="number" name="power" value="{{ old('power', $service->power) }}"
                                   placeholder="Ex: 150"
                                   class="w-full px-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                                          bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                          focus:ring-2 focus:ring-primary-end outline-none transition placeholder-gray-400 no-spinner">
                        </div>
                    </div>
                </div>

                {{-- DESCRIERE --}}
                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Descriere</label>
                    <textarea name="description" rows="6"
                              class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                                     bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white
                                     focus:ring-2 focus:ring-primary-end outline-none transition resize-y placeholder-gray-400"
                              required>{{ old('description', $service->description) }}</textarea>
                     @error('description') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
            </div>
        </div>

        {{-- SECȚIUNEA 2: PREȚ ȘI IMAGINI --}}
        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-green-100 dark:bg-green-900/30 text-green-600 p-1.5 rounded-lg">💰</span>
                Preț și Imagini
            </h2>

            <div class="mb-8">
                <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Preț</label>
                <div class="flex flex-col sm:flex-row gap-4">
                    <div class="flex-1 relative">
                        <input type="number" name="price_value" step="0.01" value="{{ old('price_value', $service->price_value) }}"
                               class="w-full pl-4 pr-20 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040] 
                                      bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white font-bold text-lg
                                      focus:ring-2 focus:ring-primary-end outline-none no-spinner">
                        <div class="absolute inset-y-0 right-0 flex items-center">
                            <select name="currency" class="h-full py-0 pl-2 pr-7 bg-transparent text-gray-500 dark:text-gray-300 font-bold border-none cursor-pointer focus:ring-0">
                                <option value="RON" {{ old('currency', $service->currency) == 'RON' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">RON</option>
                                <option value="EUR" {{ old('currency', $service->currency) == 'EUR' ? 'selected' : '' }} class="dark:bg-[#1E1E1E]">EUR</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="flex bg-gray-100 dark:bg-[#2C2C2C] p-1 rounded-xl w-full sm:w-auto shrink-0">
                        <label class="flex-1 sm:flex-none cursor-pointer">
                            <input type="radio" name="price_type" value="fixed" {{ old('price_type', $service->price_type) == 'fixed' ? 'checked' : '' }} class="peer sr-only">
                            <span class="flex items-center justify-center w-full sm:w-32 py-2.5 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 peer-checked:bg-white dark:peer-checked:bg-[#404040] peer-checked:text-gray-900 dark:peer-checked:text-white peer-checked:shadow-sm transition-all">Preț Fix</span>
                        </label>
                        <label class="flex-1 sm:flex-none cursor-pointer">
                            <input type="radio" name="price_type" value="negotiable" {{ old('price_type', $service->price_type) == 'negotiable' ? 'checked' : '' }} class="peer sr-only">
                            <span class="flex items-center justify-center w-full sm:w-32 py-2.5 rounded-lg text-sm font-semibold text-gray-500 dark:text-gray-400 peer-checked:bg-white dark:peer-checked:bg-[#404040] peer-checked:text-gray-900 dark:peer-checked:text-white peer-checked:shadow-sm transition-all">Negociabil</span>
                        </label>
                    </div>
                </div>
            </div>

            {{-- ZONA UNIFICATĂ PENTRU IMAGINI (EXISTENTE + NOI) --}}
            <div class="space-y-4">
                <label class="block font-semibold text-gray-700 dark:text-gray-300 flex justify-between items-center">
                    Galerie Foto
                    <span class="text-xs font-normal text-gray-400 bg-gray-100 dark:bg-[#2C2C2C] px-2 py-1 rounded-lg">
                        Adaugă imagini noi sau șterge-le pe cele vechi
                    </span>
                </label>

                {{-- 1. Input Upload --}}
                <div class="relative w-full group mb-6">
                    <input type="file" id="imageInput" name="images[]" multiple accept="image/*"
                           class="absolute inset-0 w-full h-full opacity-0 cursor-pointer z-10">
                    
                    <div class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-gray-300 dark:border-[#404040] rounded-2xl bg-gray-50 dark:bg-[#2C2C2C] group-hover:bg-gray-100 dark:group-hover:bg-[#333333] transition-colors">
                        <div class="flex items-center gap-3">
                            <div class="p-2 bg-white dark:bg-[#404040] rounded-full shadow-sm">
                                <svg class="h-6 w-6 text-[#CC2E2E]" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div class="text-left">
                                <p class="text-sm text-gray-600 dark:text-gray-300 font-medium">Click sau Drag & Drop</p>
                                <p class="text-[10px] text-gray-400">Max 15MB / imagine</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 2. Grid Imagini (Mixat: Vechi + Noi) --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    
                    {{-- A. IMAGINI DEJA SALVATE PE SERVER --}}
                    @php
                        $gallery = $service->images;
                        if (is_null($gallery)) $gallery = [];
                        if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
                        if (!is_array($gallery)) $gallery = [];
                        $gallery = array_values(array_filter($gallery));
                    @endphp

                    @foreach($gallery as $img)
                        <div class="relative group aspect-square rounded-xl overflow-hidden border border-gray-200 dark:border-[#404040] bg-white dark:bg-[#2C2C2C]" 
                             id="server-img-{{ $loop->index }}">
                            
                            <img src="{{ asset('storage/services/' . $img) }}" class="w-full h-full object-cover">
                            
                            <div class="absolute top-2 left-2 bg-gray-900/60 backdrop-blur-sm text-white text-[10px] px-2 py-0.5 rounded pointer-events-none">
                                Existent
                            </div>

                            <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center">
                                <button type="button" 
                                        onclick="deleteServerImage('{{ $img }}', {{ $service->id }}, 'server-img-{{ $loop->index }}')"
                                        class="bg-red-600 hover:bg-red-700 text-white px-3 py-1.5 rounded-lg shadow-lg transform translate-y-2 group-hover:translate-y-0 transition-all flex items-center gap-1 text-xs font-bold">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                    Șterge
                                </button>
                            </div>
                        </div>
                    @endforeach

                    {{-- B. PREVIEW IMAGINI NOI --}}
                    <div id="previewContainer" class="contents"></div>

                </div>
            </div>
        </div>

        {{-- SECȚIUNEA 3: CONTACT --}}
        <div class="bg-white dark:bg-[#1E1E1E] p-6 md:p-8 rounded-2xl shadow-lg border border-gray-100 dark:border-[#333333] transition-colors">
            <h2 class="text-xl font-bold mb-6 text-gray-900 dark:text-white flex items-center gap-2">
                <span class="bg-purple-100 dark:bg-purple-900/30 text-purple-600 p-1.5 rounded-lg">📞</span>
                Date de contact
            </h2>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Telefon</label>
                    <input type="text" name="phone" value="{{ old('phone', $service->phone) }}"
                        class="w-full pl-4 pr-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                               bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                               focus:ring-2 focus:ring-primary-end outline-none transition"
                        required>
                </div>
                <div>
                    <label class="block mb-2 font-semibold text-gray-700 dark:text-gray-300">Email (Opțional)</label>
                    <input type="email" name="email" value="{{ old('email', $service->email) }}"
                        class="w-full pl-4 pr-4 py-3.5 rounded-xl border border-gray-300 dark:border-[#404040]
                               bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white 
                               focus:ring-2 focus:ring-primary-end outline-none transition">
                </div>
            </div>
        </div>

        <div class="pt-4 pb-12">
            <button type="submit"
                class="w-full py-4 rounded-xl text-white text-lg font-bold
                       bg-gradient-to-r from-[#CC2E2E] to-red-600 
                       hover:from-[#B72626] hover:to-red-700 
                       active:scale-[0.99] shadow-xl shadow-red-500/20 
                       transition-all duration-200 flex items-center justify-center gap-2">
                <span>Salvează Modificările</span>
                <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                </svg>
            </button>
            
            <div class="text-center mt-6">
                <a href="{{ $service->public_url }}" class="text-gray-500 dark:text-gray-400 hover:underline text-sm">
                    Renunță și întoarce-te la anunț
                </a>
            </div>
        </div>
    </form>
</div>

{{-- CSS --}}
<style>
.no-spinner::-webkit-inner-spin-button,
.no-spinner::-webkit-outer-spin-button { -webkit-appearance: none; margin: 0; }
.no-spinner { -moz-appearance: textfield; }
select.form-select {
    -webkit-appearance: none; -moz-appearance: none; appearance: none;
    background-image: none; background-color: transparent;
}
.contents { display: contents; } /* Hack pentru Grid */
</style>

{{-- JAVASCRIPT --}}
<script>
// === 1. LOGICĂ IMAGINI NOI (UPLOAD) ===
let uploadedFiles = [];
const imageInput = document.getElementById('imageInput');
const previewContainer = document.getElementById('previewContainer');
const LIMITA_MB = 15;

imageInput.addEventListener('change', function (e) {
    const newFiles = Array.from(e.target.files);
    let hasError = false;

    newFiles.forEach(file => {
        if (file.size > LIMITA_MB * 1024 * 1024) {
            alert(`Imaginea "${file.name}" este prea mare!`);
            hasError = true;
        }
    });

    if (hasError) { updateInputFiles(); return; }

    uploadedFiles = uploadedFiles.concat(newFiles);
    if (uploadedFiles.length > 10) uploadedFiles = uploadedFiles.slice(0, 10);

    renderPreviews();
    updateInputFiles();
});

function renderPreviews() {
    previewContainer.innerHTML = "";

    uploadedFiles.forEach((file, index) => {
        const reader = new FileReader();
        reader.onload = function (e) {
            const div = document.createElement('div');
            div.className = `relative group aspect-square rounded-xl overflow-hidden shadow-sm border-2 border-dashed border-green-500/50 bg-green-50 dark:bg-[#1E2A20]`;
            
            div.innerHTML = `
                <img src="${e.target.result}" class="w-full h-full object-cover opacity-90">
                
                <div class="absolute top-2 left-2 bg-green-600 text-white text-[10px] px-2 py-0.5 rounded shadow z-10">
                    Nou
                </div>

                <div class="absolute bottom-1 left-1 bg-black/60 text-white text-[10px] px-2 py-0.5 rounded backdrop-blur-sm font-mono z-10">
                    ${(file.size / 1024 / 1024).toFixed(2)} MB
                </div>

                <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex flex-col items-center justify-center gap-2 p-4">
                    <button type="button" onclick="removePhoto(${index})" 
                            class="px-3 py-1.5 bg-red-600 text-white text-xs font-bold rounded-lg hover:bg-red-700 transition shadow-lg w-full flex items-center justify-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Anulează
                    </button>
                </div>
            `;
            previewContainer.appendChild(div);
        };
        reader.readAsDataURL(file);
    });
}

window.removePhoto = function(index) {
    uploadedFiles.splice(index, 1);
    renderPreviews();
    updateInputFiles();
}

function updateInputFiles() {
    const dataTransfer = new DataTransfer();
    uploadedFiles.forEach(file => { dataTransfer.items.add(file); });
    imageInput.files = dataTransfer.files;
}

// === 2. LOGICĂ ȘTERGERE IMAGINI VECHI (AJAX) ===
window.deleteServerImage = function(imageName, serviceId, containerId) {
    if (!confirm('Ești sigur că vrei să ștergi această imagine? Nu se poate anula.')) return;

    fetch(`/services/${serviceId}/image`, {
        method: 'DELETE',
        headers: { 
            'Content-Type': 'application/json', 
            'X-CSRF-TOKEN': '{{ csrf_token() }}' 
        },
        body: JSON.stringify({ image: imageName })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            const el = document.getElementById(containerId);
            el.style.transform = 'scale(0.9)';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 300);
        } else {
            alert('Eroare la ștergere: ' + (data.message || 'Necunoscută'));
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert('Eroare de rețea.');
    });
}

// === 3. MARCĂ → MODEL (DATE DIN BAZA DE DATE) ===
// $carModelsByBrand vine din controller (map brand_name => [model1, model2, ...])
const modelsByBrand = @json($carModelsByBrand ?? []);

const brandSelect = document.getElementById('brandSelect');
const modelSelect = document.getElementById('modelSelect');

function populateModelsForBrand(brand, preselectedModel = null) {
    modelSelect.innerHTML = '';

    const placeholder = document.createElement('option');
    placeholder.value = '';
    placeholder.textContent = 'Alege modelul';
    placeholder.className = 'dark:bg-[#1E1E1E]';
    modelSelect.appendChild(placeholder);

    const models = modelsByBrand[brand] || [];

    if (!brand || models.length === 0) {
        modelSelect.disabled = true;
        return;
    }

    models.forEach(model => {
        const opt = document.createElement('option');
        opt.value = model;
        opt.textContent = model;
        opt.className = 'dark:bg-[#1E1E1E]';
        if (preselectedModel && preselectedModel === model) {
            opt.selected = true;
        }
        modelSelect.appendChild(opt);
    });

    modelSelect.disabled = false;
}

if (brandSelect && modelSelect) {
    brandSelect.addEventListener('change', function () {
        populateModelsForBrand(this.value, null);
    });

    // Valorile existente (serviciu + old după validare)
    const oldBrand  = @json(old('brand', $service->brand));
    const oldModel  = @json(old('model', $service->model));

    if (oldBrand) {
        brandSelect.value = oldBrand;
        populateModelsForBrand(oldBrand, oldModel);
    }
}
</script>

@endsection
