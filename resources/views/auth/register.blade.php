@extends('layouts.app')

@section('title', 'Înregistrare')

@section('content')

<div class="min-h-[80vh] flex flex-col items-center justify-center py-12 px-4 sm:px-6 lg:px-8">
    
    <div class="max-w-md w-full space-y-8">
        
        <div class="text-center">
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">
                Creează un cont nou
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                Sau 
                <a href="{{ route('login') }}" class="font-medium text-[#CC2E2E] hover:text-[#B72626] transition">
                    intră în contul existent
                </a>
            </p>
        </div>

        <div class="bg-white dark:bg-[#1E1E1E] py-8 px-6 shadow-xl rounded-2xl border border-gray-200 dark:border-[#333333] transition-colors">
            
            <form class="space-y-6" action="{{ route('register') }}" method="POST">
                @csrf

                <div>
                    <label for="regName" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Numele tău
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <input id="regName" name="name" type="text" autocomplete="name" required 
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl 
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent 
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm" 
                            placeholder="Ex: Ion Popescu" value="{{ old('name') }}">
                    </div>
                    
                    <div class="mt-2 min-h-[20px] text-xs font-medium">
                        <div id="regNameMsg"></div>
                        <div id="regNameSuggestions" class="mt-1 flex flex-wrap gap-2"></div>
                    </div>
                    <x-input-error :messages="$errors->get('name')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label for="regEmail" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Adresa de Email
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                            </svg>
                        </div>
                        <input id="regEmail" name="email" type="email" autocomplete="email" required 
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl 
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent 
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm" 
                            placeholder="ion@exemplu.ro" value="{{ old('email') }}">
                    </div>

                    <div id="regEmailMsg" class="mt-1 min-h-[20px] text-xs font-medium"></div>
                    <x-input-error :messages="$errors->get('email')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Parolă
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" name="password" type="password" autocomplete="new-password" required 
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl 
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent 
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm" 
                            placeholder="Minim 8 caractere">
                    </div>
                    <x-input-error :messages="$errors->get('password')" class="mt-1 text-xs text-red-500" />
                </div>

                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                        Confirmă Parola
                    </label>
                    <div class="relative group">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <input id="password_confirmation" name="password_confirmation" type="password" autocomplete="new-password" required 
                            class="appearance-none block w-full pl-10 pr-3 py-3 border border-gray-300 dark:border-[#404040] rounded-xl 
                                   placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-[#CC2E2E] focus:border-transparent 
                                   bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white sm:text-sm transition shadow-sm" 
                            placeholder="Repetă parola">
                    </div>
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-1 text-xs text-red-500" />
                </div>

                <div class="pt-2">
                    <button type="submit" 
                        class="group relative w-full flex justify-center py-3 px-4 border border-transparent text-sm font-bold rounded-xl text-white 
                               bg-gradient-to-r from-[#CC2E2E] to-[#B72626] hover:shadow-lg hover:from-[#B72626] hover:to-[#991b1b] 
                               focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#CC2E2E] active:scale-[0.98] transition-all duration-200">
                        <span class="absolute left-0 inset-y-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-red-200 group-hover:text-white transition-colors" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd" />
                            </svg>
                        </span>
                        Creează Cont
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

{{-- SCRIPTS: Verificare Live --}}
<script>
document.addEventListener('DOMContentLoaded', () => {

    // 1. LIVE CHECK USERNAME
    const nameInput = document.getElementById('regName');
    const msgName = document.getElementById('regNameMsg');
    const sugName = document.getElementById('regNameSuggestions');
    let timerName = null;

    nameInput.addEventListener('input', function () {
        let name = this.value.trim();
        clearTimeout(timerName);
        
        if (name.length < 3) {
            msgName.innerHTML = "";
            sugName.innerHTML = "";
            return;
        }

        timerName = setTimeout(() => {
            fetch("{{ route('profile.checkName') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ name })
            })
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    msgName.innerHTML = `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'><svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/></svg> Disponibil</span>`;
                    sugName.innerHTML = "";
                } else {
                    msgName.innerHTML = `<span class='text-red-600 dark:text-red-400 flex items-center gap-1'><svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/></svg> Indisponibil</span>`;
                    
                    let html = "";
                    data.suggestions.forEach(s => {
                        html += `<button type="button" class="px-2 py-1 text-xs bg-gray-100 dark:bg-[#2C2C2C] border border-gray-200 dark:border-[#404040] rounded hover:bg-gray-200 dark:hover:bg-[#333333] transition text-gray-600 dark:text-gray-300" onclick="useSuggestionRegister('${s}')">${s}</button>`;
                    });
                    sugName.innerHTML = html;
                }
            });
        }, 300);
    });

    // 2. LIVE CHECK EMAIL
    const emailInput = document.getElementById('regEmail');
    const msgEmail = document.getElementById('regEmailMsg');
    let timerEmail = null;

    emailInput.addEventListener('input', function () {
        let email = this.value.trim();
        clearTimeout(timerEmail);
        
        if (!email.includes('@') || email.length < 5) {
            msgEmail.innerHTML = "";
            return;
        }

        timerEmail = setTimeout(() => {
            fetch("{{ route('profile.checkEmail') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json"
                },
                body: JSON.stringify({ email })
            })
            .then(r => r.json())
            .then(data => {
                if (data.available) {
                    msgEmail.innerHTML = `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'><svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/></svg> Email valid</span>`;
                } else {
                    msgEmail.innerHTML = `<span class='text-red-600 dark:text-red-400 flex items-center gap-1'><svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M6 18L18 6M6 6l12 12'/></svg> Email deja folosit</span>`;
                }
            });
        }, 300);
    });
});

function useSuggestionRegister(name) {
    document.getElementById('regName').value = name;
    document.getElementById('regNameMsg').innerHTML = `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'><svg class='w-3 h-3' fill='none' stroke='currentColor' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M5 13l4 4L19 7'/></svg> Disponibil</span>`;
    document.getElementById('regNameSuggestions').innerHTML = "";
}
</script>

@endsection