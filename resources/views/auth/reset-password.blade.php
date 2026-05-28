@extends('layouts.app')

@section('title', 'Setează parola nouă')

@section('content')
<div class="min-h-[80vh] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Setează parola nouă
            </h2>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                Introdu adresa de email și noua parolă pentru contul tău iaAuto.ro.
            </p>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] py-8 px-6 shadow-xl rounded-2xl border border-gray-200 dark:border-[#333333] transition-colors">
            <form method="POST" action="{{ route('password.store') }}" class="space-y-6">
                @csrf

                <input type="hidden" name="token" value="{{ $request->route('token') }}">

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#C81424] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="username" required autofocus
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#C81424] focus:border-transparent
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm"
                            placeholder="adresa@email.com" value="{{ old('email', $request->email) }}">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Parolă nouă
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#C81424] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="new-password" minlength="6" required
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#C81424] focus:border-transparent
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm"
                            placeholder="Parola nouă, minim 6 caractere">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Confirmă parola
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#C81424] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25V19.5H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.5c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
                            </svg>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" minlength="6" required
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#C81424] focus:border-transparent
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm"
                            placeholder="Repetă parola">
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-red-500" />
                </div>

                <button type="submit"
                    class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white
                           bg-gradient-to-r from-[#C81424] to-[#94111B] hover:shadow-lg hover:from-[#94111B] hover:to-[#991b1b]
                           focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#C81424] active:scale-[0.98] transition-all duration-200">
                    <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-red-200 group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </span>
                    Salvează parola nouă
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
