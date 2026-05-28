@extends('layouts.app')

@section('title', 'Editează profilul')

@section('content')
<div class="max-w-[1536px] mx-auto mt-12 mb-20 px-4 sm:px-6 lg:px-8">

    <h1 class="text-3xl font-bold mb-6 text-gray-900 dark:text-white">Editează profilul</h1>

    <!-- FORM PROFIL -->
    <form method="POST" action="{{ route('profile.update') }}" class="bg-white dark:bg-[#1E1E1E] p-6 rounded-xl shadow border border-gray-200 dark:border-[#333333]">
        @csrf
        @method('PATCH')

        <div class="mb-4">
            <label class="font-semibold text-gray-700 dark:text-gray-300">Nume</label>
            <input type="text" name="name" value="{{ old('name', auth()->user()->name) }}"
                   class="w-full mt-1 px-4 py-3 border border-gray-200 dark:border-[#404040] rounded-xl bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-end">
            @error('name')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700 dark:text-gray-300">Email</label>
            <input type="email" name="email" value="{{ old('email', auth()->user()->email) }}"
                   class="w-full mt-1 px-4 py-3 border border-gray-200 dark:border-[#404040] rounded-xl bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-end">
            @error('email')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <button class="px-6 py-3 bg-red-600 text-white rounded-xl font-semibold hover:bg-red-700 transition">
            Salvează modificările
        </button>
    </form>


    <!-- SCHIMBA PAROLA -->
    <h2 class="text-2xl font-bold mt-10 mb-4 text-gray-900 dark:text-white">Schimbă parola</h2>

    <form method="POST" action="{{ route('password.update') }}" class="bg-white dark:bg-[#1E1E1E] p-6 rounded-xl shadow border border-gray-200 dark:border-[#333333]">
        @csrf
        @method('PUT')

        <div class="mb-4">
            <label class="font-semibold text-gray-700 dark:text-gray-300">Parola actuală</label>
            <input type="password" name="current_password"
                   class="w-full mt-1 px-4 py-3 border border-gray-200 dark:border-[#404040] rounded-xl bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-end">
            @error('current_password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700 dark:text-gray-300">Parola nouă</label>
            <input type="password" name="password" minlength="6"
                   class="w-full mt-1 px-4 py-3 border border-gray-200 dark:border-[#404040] rounded-xl bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-end">
            @error('password')<p class="text-red-600 text-sm mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="mb-4">
            <label class="font-semibold text-gray-700 dark:text-gray-300">Confirmă parola nouă</label>
            <input type="password" name="password_confirmation" minlength="6"
                   class="w-full mt-1 px-4 py-3 border border-gray-200 dark:border-[#404040] rounded-xl bg-gray-50 dark:bg-[#252525] text-gray-900 dark:text-gray-100 focus:ring-2 focus:ring-primary-end">
        </div>

        <button class="px-6 py-3 bg-[#C81424] text-white rounded-xl font-semibold hover:bg-[#94111B] transition">
            Actualizează parola
        </button>
    </form>

</div>
@endsection
