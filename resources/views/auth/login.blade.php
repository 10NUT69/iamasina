@extends('layouts.app')

@section('title', 'Autentificare')

@section('content')

<div class="min-h-[80vh] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-md w-full space-y-8">
        
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Bine ai revenit! 👋
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Nu ai cont încă? 
                <a href="{{ route('register') }}" class="font-medium text-[#CC2E2E] hover:text-[#B72626] transition">
                    Înregistrează-te gratuit
                </a>
            </p>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] py-8 px-6 shadow-xl rounded-2xl border border-gray-200 dark:border-[#333333] transition-colors">
            
            <x-auth-session-status class="mb-4" :status="session('status')" />

            <form class="space-y-6" method="POST" action="{{ route('login') }}">
                @csrf

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
                            </svg>
                        </div>
                        <input id="email" name="email" type="email" autocomplete="email" required autofocus
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl 
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent 
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm" 
                            placeholder="adresa@email.com" value="{{ old('email') }}">
                    </div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Parolă
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="current-password" required 
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl 
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent 
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm" 
                            placeholder="••••••••">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember_me" name="remember" type="checkbox" 
                            class="h-4 w-4 text-[#CC2E2E] focus:ring-[#CC2E2E] border-gray-300 dark:border-[#404040] rounded dark:bg-[#2C2C2C] cursor-pointer">
                        <label for="remember_me" class="ml-2 block text-sm text-gray-700 dark:text-gray-300 cursor-pointer select-none">
                            Ține-mă minte
                        </label>
                    </div>

                    @if (Route::has('password.request'))
                        <div class="text-sm">
                            <a href="{{ route('password.request') }}" class="font-medium text-[#CC2E2E] hover:text-[#B72626] transition">
                                Ai uitat parola?
                            </a>
                        </div>
                    @endif
                </div>

                <div>
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white 
                               bg-gradient-to-r from-[#CC2E2E] to-[#B72626] hover:shadow-lg hover:from-[#B72626] hover:to-[#991b1b] 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#CC2E2E] active:scale-[0.98] transition-all duration-200">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-red-200 group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9" />
                            </svg>
                        </span>
                        Intră în cont
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

@endsection