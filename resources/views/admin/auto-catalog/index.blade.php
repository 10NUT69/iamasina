@extends('admin.layout')

@section('content')
<div class="max-w-[1536px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="mb-6 flex flex-col gap-2 md:flex-row md:items-end md:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-800">Date auto</h1>
            <p class="mt-1 text-sm text-slate-500">Administrează mărcile, modelele, mărcile populare și normele de poluare folosite în formulare și filtre.</p>
        </div>

        <div class="flex flex-wrap gap-2 text-xs font-bold text-slate-500">
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Mărci: {{ $brands->count() }}</span>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Modele: {{ $brands->sum('models_count') }}</span>
            <span class="rounded-full border border-slate-200 bg-white px-3 py-1">Norme: {{ $normePoluare->count() }}</span>
        </div>
    </div>

    @if($errors->any())
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ $errors->first() }}
        </div>
    @endif

    @if(session('success'))
        <div class="mb-6 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1fr)_380px]">
        <section class="space-y-5">
            <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800">Adaugă marcă</h2>
                <form method="POST" action="{{ route('admin.auto-catalog.brands.store') }}" class="mt-4 grid gap-3 md:grid-cols-[1fr_1fr_110px_130px_auto] md:items-end">
                    @csrf
                    <label class="block text-xs font-bold text-slate-500">
                        Nume
                        <input type="text" name="name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800" required>
                    </label>
                    <label class="block text-xs font-bold text-slate-500">
                        Țara
                        <input type="text" name="country" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                    </label>
                    <label class="block text-xs font-bold text-slate-500">
                        Ordine
                        <input type="number" name="sort_order" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                    </label>
                    <label class="flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-600">
                        <input type="checkbox" name="is_popular" value="1" class="rounded border-slate-300">
                        Populară
                    </label>
                    <button type="submit" class="h-10 rounded-lg bg-slate-800 px-4 text-sm font-bold text-white transition hover:bg-slate-900">
                        Adaugă
                    </button>
                </form>
            </div>

            <div class="space-y-4">
                @foreach($brands as $brand)
                    <article class="rounded-xl border border-slate-100 bg-white shadow-sm">
                        <div class="border-b border-slate-100 p-5">
                            <div class="mb-4 flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                                <div>
                                    <h2 class="text-lg font-bold text-slate-800">
                                        {{ $brand->name }}
                                        @if($brand->is_popular)
                                            <span class="ml-2 rounded-full bg-red-50 px-2 py-0.5 text-[10px] font-black uppercase text-[#C81424]">populară</span>
                                        @endif
                                    </h2>
                                    <p class="mt-1 text-xs text-slate-400">
                                        {{ $brand->models_count }} modele · {{ $brand->services_count }} anunțuri directe
                                    </p>
                                </div>
                                <a href="#brand-{{ $brand->id }}-models" class="text-xs font-bold text-blue-600">Vezi modelele</a>
                            </div>

                            <form method="POST" action="{{ route('admin.auto-catalog.brands.update', $brand) }}" class="grid gap-3 md:grid-cols-[1fr_1fr_1fr_100px_130px_auto] md:items-end">
                                @csrf
                                @method('PUT')
                                <label class="block text-xs font-bold text-slate-500">
                                    Nume
                                    <input type="text" name="name" value="{{ $brand->name }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800" required>
                                </label>
                                <label class="block text-xs font-bold text-slate-500">
                                    Slug
                                    <input type="text" name="slug" value="{{ $brand->slug }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                </label>
                                <label class="block text-xs font-bold text-slate-500">
                                    Țara
                                    <input type="text" name="country" value="{{ $brand->country }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                </label>
                                <label class="block text-xs font-bold text-slate-500">
                                    Ordine
                                    <input type="number" name="sort_order" value="{{ $brand->sort_order }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                </label>
                                <label class="flex h-10 items-center gap-2 rounded-lg border border-slate-200 px-3 text-xs font-bold text-slate-600">
                                    <input type="checkbox" name="is_popular" value="1" class="rounded border-slate-300" @checked($brand->is_popular)>
                                    Populară
                                </label>
                                <button type="submit" class="h-10 rounded-lg bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">
                                    Salvează
                                </button>
                            </form>

                            <form method="POST" action="{{ route('admin.auto-catalog.brands.destroy', $brand) }}" class="mt-3" onsubmit="return confirm('Ștergi marca {{ $brand->name }}? Dacă nu are anunțuri, modelele ei vor fi șterse împreună cu marca.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="rounded-lg border border-red-200 px-3 py-2 text-xs font-bold text-red-600 transition hover:bg-red-50">
                                    Șterge marca
                                </button>
                            </form>
                        </div>

                        <details id="brand-{{ $brand->id }}-models" class="p-5">
                            <summary class="cursor-pointer text-sm font-bold text-slate-700">Modele {{ $brand->name }}</summary>

                            <form method="POST" action="{{ route('admin.auto-catalog.models.store', $brand) }}" class="mt-4 grid gap-3 rounded-lg border border-slate-100 bg-slate-50 p-3 md:grid-cols-[1fr_1fr_100px_auto] md:items-end">
                                @csrf
                                <label class="block text-xs font-bold text-slate-500">
                                    Model nou
                                    <input type="text" name="name" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800" required>
                                </label>
                                <label class="block text-xs font-bold text-slate-500">
                                    Slug opțional
                                    <input type="text" name="slug" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                </label>
                                <label class="block text-xs font-bold text-slate-500">
                                    Ordine
                                    <input type="number" name="sort_order" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                </label>
                                <button type="submit" class="h-10 rounded-lg bg-slate-800 px-4 text-sm font-bold text-white transition hover:bg-slate-900">
                                    Adaugă model
                                </button>
                            </form>

                            <div class="mt-4 space-y-2">
                                @forelse($brand->models as $model)
                                    <div class="rounded-lg border border-slate-100 p-3">
                                        <form method="POST" action="{{ route('admin.auto-catalog.models.update', $model) }}" class="grid gap-3 md:grid-cols-[1fr_1fr_90px_auto] md:items-end">
                                            @csrf
                                            @method('PUT')
                                            <label class="block text-xs font-bold text-slate-500">
                                                Nume
                                                <input type="text" name="name" value="{{ $model->name }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800" required>
                                            </label>
                                            <label class="block text-xs font-bold text-slate-500">
                                                Slug
                                                <input type="text" name="slug" value="{{ $model->slug }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                            </label>
                                            <label class="block text-xs font-bold text-slate-500">
                                                Ordine
                                                <input type="number" name="sort_order" value="{{ $model->sort_order }}" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                                            </label>
                                            <button type="submit" class="h-10 rounded-lg bg-blue-600 px-4 text-sm font-bold text-white transition hover:bg-blue-700">
                                                Salvează
                                            </button>
                                        </form>

                                        <div class="mt-2 flex items-center justify-between gap-3">
                                            <span class="text-xs text-slate-400">{{ $model->services_count }} anunțuri directe</span>
                                            <form method="POST" action="{{ route('admin.auto-catalog.models.destroy', $model) }}" onsubmit="return confirm('Ștergi modelul {{ $model->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-xs font-bold text-red-600 hover:underline">Șterge model</button>
                                            </form>
                                        </div>
                                    </div>
                                @empty
                                    <p class="rounded-lg border border-dashed border-slate-200 p-4 text-sm text-slate-400">Marca nu are modele încă.</p>
                                @endforelse
                            </div>
                        </details>
                    </article>
                @endforeach
            </div>
        </section>

        <aside class="space-y-5">
            <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800">Adaugă normă poluare</h2>
                <form method="POST" action="{{ route('admin.auto-catalog.norme.store') }}" class="mt-4 space-y-3">
                    @csrf
                    <label class="block text-xs font-bold text-slate-500">
                        Nume
                        <input type="text" name="nume" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800" required>
                    </label>
                    <label class="block text-xs font-bold text-slate-500">
                        Slug opțional
                        <input type="text" name="slug" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                    </label>
                    <label class="block text-xs font-bold text-slate-500">
                        Ordine
                        <input type="number" name="sort_order" value="0" class="mt-1 w-full rounded-lg border border-slate-200 px-3 py-2 text-sm text-slate-800">
                    </label>
                    <button type="submit" class="h-10 w-full rounded-lg bg-slate-800 px-4 text-sm font-bold text-white transition hover:bg-slate-900">
                        Adaugă normă
                    </button>
                </form>
            </div>

            <div class="rounded-xl border border-slate-100 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold text-slate-800">Norme poluare</h2>
                <div class="mt-4 space-y-3">
                    @foreach($normePoluare as $norma)
                        <div class="rounded-lg border border-slate-100 p-3">
                            <form method="POST" action="{{ route('admin.auto-catalog.norme.update', $norma) }}" class="space-y-2">
                                @csrf
                                @method('PUT')
                                <input type="text" name="nume" value="{{ $norma->nume }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-sm font-bold text-slate-800" required>
                                <div class="grid grid-cols-[1fr_88px] gap-2">
                                    <input type="text" name="slug" value="{{ $norma->slug }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600">
                                    <input type="number" name="sort_order" value="{{ $norma->sort_order }}" class="w-full rounded-lg border border-slate-200 px-3 py-2 text-xs text-slate-600">
                                </div>
                                <button type="submit" class="h-9 w-full rounded-lg bg-blue-600 text-xs font-bold text-white transition hover:bg-blue-700">Salvează</button>
                            </form>

                            <div class="mt-2 flex items-center justify-between gap-3">
                                <span class="text-xs text-slate-400">{{ $norma->services_count }} anunțuri</span>
                                <form method="POST" action="{{ route('admin.auto-catalog.norme.destroy', $norma) }}" onsubmit="return confirm('Ștergi norma {{ $norma->nume }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-xs font-bold text-red-600 hover:underline">Șterge</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </aside>
    </div>
</div>
@endsection
