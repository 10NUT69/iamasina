@extends('layouts.app')

@section('title', 'Editează profilul')

@section('content')
<div class="max-w-3xl mx-auto mt-12 mb-20">

    <h1 class="text-3xl font-bold mb-6 text-gray-900">Editează profilul</h1>

    <!-- FORM PROFIL -->
    <form method="POST" action="{{ route('profile.update') }}" class="bg-white p-6 rounded-xl shadow border">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="font-semibold text-gray-700">Nume</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                   class="w-full mt-1 px-4 py-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-primary-end">
            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700">Email</label>
            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                   class="w-full mt-1 px-4 py-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-primary-end">
            @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <button class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition">
            Salvează modificările
        </button>
    </form>


    <!-- SCHIMBA PAROLA -->
    <h2 class="text-2xl font-bold mt-10 mb-4 text-gray-900">Schimbă parola</h2>

    <form method="POST" action="{{ route('password.update') }}" class="bg-white p-6 rounded-xl shadow border">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="font-semibold text-gray-700">Parola actuală</label>
            <input type="password" name="current_password"
                   class="w-full mt-1 px-4 py-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-primary-end">
            @error('current_password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700">Parola nouă</label>
            <input type="password" name="password"
                   class="w-full mt-1 px-4 py-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-primary-end">
            @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700">Confirmă parola nouă</label>
            <input type="password" name="password_confirmation"
                   class="w-full mt-1 px-4 py-3 border rounded-xl bg-gray-50 focus:ring-2 focus:ring-primary-end">
        </div>

        <button class="px-6 py-3 bg-blue-600 text-white rounded-xl font-semibold hover:bg-blue-700 transition">
            Actualizează parola
        </button>
    </form>

</div>
@endsection
