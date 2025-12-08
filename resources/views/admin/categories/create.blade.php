@extends('admin.layout')

@section('content')
<div class="p-6 max-w-xl mx-auto">

    <h1 class="text-2xl font-bold mb-6">Adaugă categorie</h1>

    @if ($errors->any())
        <div class="bg-red-100 text-red-700 p-3 rounded mb-4">
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.categories.store') }}" method="POST">
        @csrf

        <label class="block mb-3">
            <span class="font-semibold">Nume categorie</span>
            <input type="text" name="name"
                   value="{{ old('name') }}"
                   class="w-full border rounded px-3 py-2 mt-1" required>
        </label>

        <label class="block mb-3">
            <span class="font-semibold">Ordine afișare</span>
            <input type="number" name="sort_order"
                   value="{{ old('sort_order', 0) }}"
                   class="w-full border rounded px-3 py-2 mt-1">
        </label>

        <button class="px-4 py-2 bg-green-600 text-white rounded hover:bg-green-700">
            Salvează
        </button>
    </form>

</div>
@endsection
