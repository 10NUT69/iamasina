@extends('admin.layout')

@section('content')
@php
    $gallery = $service->images;
    if (is_null($gallery)) $gallery = [];
    if (is_string($gallery)) $gallery = json_decode($gallery, true) ?? [];
    if (!is_array($gallery)) $gallery = [];
    $gallery = array_values(array_filter($gallery));

    $brandName = $service->brandRel?->name
        ?: $service->generation?->model?->brand?->name
        ?: $service->brand;

    $modelName = $service->modelRel?->name
        ?: $service->generation?->model?->name
        ?: $service->model;
@endphp

<div class="max-w-[1536px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-slate-800">Editeaza anunt</h1>
            <p class="text-sm text-slate-500 mt-1">
                ID: #{{ $service->id }}
                @if($service->trashed())
                    <span class="ml-2 inline-flex px-2 py-0.5 rounded bg-red-100 text-red-700 text-xs font-bold">STERS</span>
                @endif
            </p>
        </div>

        <div class="flex flex-wrap gap-2">
            <a href="{{ $service->public_url }}" target="_blank"
               class="px-4 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm">
                Vezi public
            </a>

            <a href="{{ route('admin.services.index') }}"
               class="px-4 py-2 rounded-lg border bg-white hover:bg-slate-50 text-sm">
                Inapoi la lista
            </a>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-lg">
            <b>{{ session('success') }}</b>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <b>{{ session('error') }}</b>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg">
            <b>Verifica formularul:</b>
            <ul class="list-disc ml-5 mt-2 text-sm">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <form method="POST" action="{{ route('admin.services.update', $service->id) }}" class="space-y-5">
                @csrf
                @method('PUT')

                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Titlu</label>
                    <input type="text"
                           name="title"
                           value="{{ old('title', $service->title) }}"
                           class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none"
                           required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Categorie</label>
                        <select name="category_id"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}" @selected((int) old('category_id', $service->category_id) === (int) $category->id)>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Status</label>
                        <select name="status"
                                class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none"
                                required>
                            @foreach(['active' => 'Activ', 'pending' => 'In asteptare', 'expired' => 'Expirat', 'rejected' => 'Respins'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('status', $service->status) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block mb-2 text-sm font-semibold text-slate-700">Descriere</label>
                    <textarea name="description"
                              rows="10"
                              class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none resize-y"
                              required>{{ old('description', $service->description) }}</textarea>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Pret</label>
                        <input type="number"
                               step="0.01"
                               name="price_value"
                               value="{{ old('price_value', $service->price_value) }}"
                               class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Moneda</label>
                        <select name="currency" class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="EUR" @selected(old('currency', $service->currency) === 'EUR')>EUR</option>
                            <option value="RON" @selected(old('currency', $service->currency) === 'RON')>RON</option>
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Tip pret</label>
                        <select name="price_type" class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                            <option value="fixed" @selected(old('price_type', $service->price_type) === 'fixed')>Fix</option>
                            <option value="negotiable" @selected(old('price_type', $service->price_type) === 'negotiable')>Negociabil</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Telefon</label>
                        <input type="text"
                               name="phone"
                               value="{{ old('phone', $service->phone) }}"
                               class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-semibold text-slate-700">Email</label>
                        <input type="email"
                               name="email"
                               value="{{ old('email', $service->email) }}"
                               class="w-full px-4 py-3 rounded-lg border border-slate-300 bg-slate-50 focus:ring-2 focus:ring-blue-500 outline-none">
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit"
                            class="px-5 py-3 rounded-lg bg-slate-800 hover:bg-slate-900 text-white font-semibold transition">
                        Salveaza modificarile
                    </button>
                </div>
            </form>
        </div>

        <aside class="space-y-6">
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Detalii auto</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-slate-400 font-bold uppercase text-xs">Marca</dt><dd class="text-slate-700">{{ $brandName ?: '-' }}</dd></div>
                    <div><dt class="text-slate-400 font-bold uppercase text-xs">Model</dt><dd class="text-slate-700">{{ $modelName ?: '-' }}</dd></div>
                    <div><dt class="text-slate-400 font-bold uppercase text-xs">An</dt><dd class="text-slate-700">{{ $service->an_fabricatie ?: '-' }}</dd></div>
                    <div><dt class="text-slate-400 font-bold uppercase text-xs">KM</dt><dd class="text-slate-700">{{ $service->km ? number_format($service->km, 0, ',', '.') : '-' }}</dd></div>
                    <div><dt class="text-slate-400 font-bold uppercase text-xs">Localitate</dt><dd class="text-slate-700">{{ $service->locality?->name ?? $service->city ?? '-' }}</dd></div>
                    <div><dt class="text-slate-400 font-bold uppercase text-xs">Utilizator</dt><dd class="text-slate-700">{{ $service->user?->email ?? '-' }}</dd></div>
                </dl>
            </div>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-5">
                <h2 class="text-lg font-bold text-slate-800 mb-4">Imagini</h2>

                @if(count($gallery) === 0)
                    <div class="text-sm text-slate-500">
                        Nu exista imagini incarcate. Se va afisa imaginea default.
                    </div>
                @else
                    <div class="grid grid-cols-2 gap-3">
                        @foreach($gallery as $image)
                            <div class="relative group aspect-square rounded-xl overflow-hidden border border-slate-200 bg-white">
                                <img src="{{ asset('storage/services/' . $image) }}" class="w-full h-full object-cover" alt="">

                                <form method="POST"
                                      action="{{ route('admin.services.deleteImage', $service->id) }}"
                                      onsubmit="return confirm('Stergi aceasta imagine?')"
                                      class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition flex items-center justify-center">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="image" value="{{ $image }}">
                                    <button class="bg-red-600 hover:bg-red-700 text-white px-3 py-2 rounded-lg text-xs font-bold">
                                        Sterge
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </aside>
    </div>
</div>
@endsection
