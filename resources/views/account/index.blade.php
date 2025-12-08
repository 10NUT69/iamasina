@extends('layouts.app')

@section('title', 'Contul meu - Servicii România')

@section('content')

<div class="max-w-7xl mx-auto mt-10 mb-20 px-4 md:px-0">

    <div class="flex flex-col md:flex-row items-start md:items-center justify-between mb-10 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-900 dark:text-white tracking-tight">
                Salut, {{ auth()->user()->name }} 👋
            </h1>
            <p class="text-gray-500 dark:text-gray-400 mt-1">
                Gestionează anunțurile și setările contului tău.
            </p>
        </div>
        
        <a href="{{ route('services.create') }}" 
           class="px-5 py-3 bg-[#CC2E2E] hover:bg-[#B72626] text-white font-bold rounded-xl shadow-lg 
                  transition transform active:scale-95 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
            </svg>
            Publică Anunț Nou
        </a>
    </div>

    <div class="border-b border-gray-200 dark:border-[#333333] mb-8">
        <ul class="flex gap-8 text-lg font-medium overflow-x-auto no-scrollbar">
            <li>
                <a href="?tab=anunturi"
                   class="pb-3 inline-block transition-colors whitespace-nowrap
                   {{ request('tab') === 'anunturi' || !request('tab')
                       ? 'text-[#CC2E2E] border-b-2 border-[#CC2E2E]'
                       : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                   Anunțurile mele
                </a>
            </li>
            <li>
                <a href="?tab=favorite"
                   class="pb-3 inline-block transition-colors whitespace-nowrap
                   {{ request('tab') === 'favorite'
                       ? 'text-[#CC2E2E] border-b-2 border-[#CC2E2E]'
                       : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                   Favorite
                </a>
            </li>
            <li>
                <a href="?tab=profil"
                   class="pb-3 inline-block transition-colors whitespace-nowrap
                   {{ request('tab') === 'profil'
                       ? 'text-[#CC2E2E] border-b-2 border-[#CC2E2E]'
                       : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                   Setări Profil
                </a>
            </li>
        </ul>
    </div>

    {{-- TAB 1: ANUNȚURILE MELE --}}
    @if(request('tab') === 'anunturi' || !request('tab'))

        @php
            $myServices = \App\Models\Service::where('user_id', auth()->id())
                            ->orderBy('created_at', 'desc')
                            ->get();
        @endphp

        @if($myServices->isEmpty())
            <div class="text-center py-16 bg-gray-50 dark:bg-[#1E1E1E] rounded-2xl border border-dashed border-gray-300 dark:border-[#333333]">
                <p class="text-gray-600 dark:text-gray-400 text-lg">Nu ai publicat niciun anunț încă.</p>
            </div>
        @else

        <div id="myServicesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($myServices as $service)
            <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-sm border border-gray-200 dark:border-[#333333] 
                        overflow-hidden hover:shadow-lg transition-all duration-300 group"
                 id="service-{{ $service->id }}">

                {{-- 🔥 MODIFICAT AICI: Link SEO Friendly --}}
                <a href="{{ $service->public_url }}" class="block relative overflow-hidden">
                    <img src="{{ $service->main_image_url }}"
                         class="w-full h-48 object-cover transition-transform duration-500 group-hover:scale-105"
                         alt="{{ $service->title }}">
                    
                    <span class="absolute top-2 right-2 px-2 py-1 text-xs font-bold rounded-md shadow-sm
                        {{ $service->status === 'active' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        {{ ucfirst($service->status ?? 'Activ') }}
                    </span>
                </a>

                <div class="p-5">
                    <h3 class="font-bold text-lg text-gray-900 dark:text-white truncate mb-1">
                        {{ $service->title }}
                    </h3>

                    <p class="text-gray-700 dark:text-gray-300 font-semibold text-sm mb-3">
                        @if($service->price_value)
                            {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                            @if($service->price_type == 'negotiable')
                                <span class="text-gray-400 font-normal text-xs ml-1">(Negociabil)</span>
                            @endif
                        @else
                            <span class="text-orange-500">Cere ofertă</span>
                        @endif
                    </p>

                    <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-400 border-t border-gray-100 dark:border-[#333333] pt-3 mb-4">
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.477 0 8.268 2.943 9.542 7-1.274 4.057-5.065 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                            {{ $service->views }}
                        </span>
                        <span>{{ $service->created_at->format('d.m.Y') }}</span>
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <a href="{{ route('services.edit', $service->id) }}"
                           class="px-3 py-2 text-sm font-medium text-center bg-blue-50 dark:bg-blue-900/20 text-blue-600 dark:text-blue-400 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/40 transition">
                            Editare
                        </a>

                        <button type="button"
                                data-id="{{ $service->id }}"
                                data-url="{{ route('services.destroy', $service->id) }}"
                                onclick="deleteService(this)"
                                class="px-3 py-2 text-sm font-medium text-center bg-red-50 dark:bg-red-900/20 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-100 dark:hover:bg-red-900/40 transition">
                            Șterge
                        </button>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    @endif


    {{-- TAB 2: FAVORITE --}}
    @if(request('tab') === 'favorite')

        @php
            $favorites = auth()->user()
                ->favorites()
                ->with('service')
                ->get()
                ->pluck('service')
                ->filter();
        @endphp

        @if($favorites->isEmpty())
            <div id="favoriteEmptyMsg" class="text-center py-16 bg-gray-50 dark:bg-[#1E1E1E] rounded-2xl border border-dashed border-gray-300 dark:border-[#333333]">
                <p class="text-gray-500 dark:text-gray-400 text-lg">Nu ai niciun anunț salvat la favorite.</p>
            </div>
        @else

        <div id="favoriteEmptyMsg" class="hidden text-center py-16 bg-gray-50 dark:bg-[#1E1E1E] rounded-2xl border border-dashed border-gray-300 dark:border-[#333333]">
            <p class="text-gray-500 dark:text-gray-400 text-lg">Nu ai niciun anunț salvat la favorite.</p>
        </div>

        <div id="favoriteList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($favorites as $service)
            <div class="bg-white dark:bg-[#1E1E1E] rounded-2xl shadow-sm border border-gray-200 dark:border-[#333333] p-4 favorite-card transition-colors group" 
                 id="favorite-{{ $service->id }}">

                {{-- 🔥 MODIFICAT AICI: Link SEO Friendly --}}
                <a href="{{ $service->public_url }}">
                    <img src="{{ $service->main_image_url }}"
                         class="w-full h-40 object-cover rounded-xl mb-3 bg-gray-100 dark:bg-[#2C2C2C] group-hover:scale-[1.02] transition-transform duration-300">
                </a>

                <h3 class="font-bold text-lg text-gray-900 dark:text-white truncate">{{ $service->title }}</h3>

                <p class="text-gray-700 dark:text-gray-300 font-semibold text-sm mt-1">
                    @if($service->price_value)
                        {{ number_format($service->price_value, 0, ',', '.') }} {{ $service->currency }}
                    @else
                        Cere ofertă
                    @endif
                </p>

                <button onclick="toggleFavorite({{ $service->id }}, this)"
                        class="mt-4 w-full px-3 py-2.5 text-sm font-medium bg-red-100 dark:bg-red-900/30 text-red-600 dark:text-red-400 rounded-lg hover:bg-red-200 dark:hover:bg-red-900/50 transition flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                    Scoate din favorite
                </button>
            </div>
            @endforeach
        </div>
        @endif
    @endif


   {{-- TAB 3: PROFIL --}}
   @if(request('tab') === 'profil')

   <div class="max-w-5xl mr-auto">
       
       <div class="bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#333333] shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row transition-colors">
           
           <div class="w-full md:w-1/4 bg-gray-50 dark:bg-[#181818] p-6 border-b md:border-b-0 md:border-r border-gray-200 dark:border-[#333333] flex flex-col items-center text-center justify-center">
               
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#CC2E2E] to-[#801010] text-white flex items-center justify-center text-2xl font-bold shadow-lg mb-3 select-none">
                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                    </div>
                    <div class="absolute bottom-3 right-0 w-4 h-4 bg-green-500 border-2 border-white dark:border-[#181818] rounded-full"></div>
                </div>

                <h2 class="text-lg font-bold text-gray-900 dark:text-white truncate w-full px-2">
                    {{ auth()->user()->name }}
                </h2>
                
                <div class="mt-1 px-3 py-1 bg-white dark:bg-[#252525] border border-gray-200 dark:border-[#333333] rounded-full text-xs text-gray-500 dark:text-gray-400 shadow-sm">
                    Membru din {{ auth()->user()->created_at->format('Y') }}
                </div>
           </div>

           <div class="w-full md:w-3/4 p-8">
               
                <div class="flex items-center justify-between mb-8">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Setări Cont</h3>
                        <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Actualizează datele tale de identificare.</p>
                    </div>
                </div>

                <div id="profileSavedMsg" class="hidden mb-6 p-3 rounded-lg text-sm font-medium text-center transition-all"></div>

                <div class="space-y-6">
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        
                        <div>
                            <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Numele tău</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input id="editName" type="text" value="{{ auth()->user()->name }}"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040] 
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                                           focus:ring-2 focus:ring-[#CC2E2E]/20 focus:border-[#CC2E2E] outline-none transition shadow-sm">
                            </div>
                            
                            {{-- FEEDBACK VALIDARE --}}
                            <div class="mt-2 min-h-[20px] space-y-2">
                                <div id="nameCheckMsg" class="text-sm font-medium"></div>
                                <div id="nameSuggestions" class="text-sm"></div>
                            </div>
                        </div>

                        <div>
                            <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Email</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input id="editEmail" type="email" value="{{ auth()->user()->email }}"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040] 
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                                           focus:ring-2 focus:ring-[#CC2E2E]/20 focus:border-[#CC2E2E] outline-none transition shadow-sm">
                            </div>
                        </div>
                    </div>

                    <hr class="border-gray-100 dark:border-[#333333]">

                    <div>
                        <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                            Securitate
                        </h3>
                        
                        <div class="md:w-1/2 pr-0 md:pr-3">
                            <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Parolă Nouă</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#CC2E2E] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <input id="editPassword" type="password" placeholder="Lasă gol dacă nu schimbi"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040] 
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                                           focus:ring-2 focus:ring-[#CC2E2E]/20 focus:border-[#CC2E2E] outline-none transition shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-start">
                        <button onclick="updateProfile()"
                            class="px-6 py-3 rounded-xl text-white font-bold text-sm tracking-wide
                                   bg-[#CC2E2E] hover:bg-[#B72626] 
                                   shadow-lg hover:shadow-red-500/20 active:scale-95 transition-all duration-200 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            Salvează Modificările
                        </button>
                    </div>
                </div>

           </div>
       </div>

   </div>

   @endif

</div>


<script>
function toggleFavorite(serviceId, btn) {
    fetch("{{ route('favorite.toggle') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ service_id: serviceId })
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "removed") {
            let card = document.getElementById("favorite-" + serviceId);
            card.style.transition = "0.4s";
            card.style.opacity = "0";
            card.style.transform = "scale(0.95)";
            setTimeout(() => card.remove(), 400);
            setTimeout(() => {
                if (document.querySelectorAll('.favorite-card').length === 0) {
                    document.getElementById('favoriteEmptyMsg').classList.remove('hidden');
                }
            }, 450);
        }
    });
}

function deleteService(btn) {
    if (!confirm("Sigur vrei să ștergi acest anunț? Această acțiune este ireversibilă.")) return;

    const url = btn.getAttribute('data-url');
    const id = btn.getAttribute('data-id');
    const card = document.getElementById("service-" + id);

    if (card) card.style.opacity = "0.5";

    fetch(url, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.status === "deleted") {
            card.style.transition = "0.3s";
            card.style.opacity = "0";
            card.style.transform = "scale(0.9)";
            setTimeout(() => card.remove(), 300);
        } else {
            alert("Eroare la ștergere.");
            if (card) card.style.opacity = "1";
        }
    })
    .catch(err => {
        console.error(err);
        alert("Eroare la ștergere.");
        if (card) card.style.opacity = "1";
    });
}

function updateProfile() {
    let name     = document.getElementById("editName").value;
    let email    = document.getElementById("editEmail").value;
    let password = document.getElementById("editPassword").value;

    fetch("{{ route('profile.ajaxUpdate') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify({ name, email, password })
    })
    .then(async res => {
        let data;
        try {
            data = await res.json();
        } catch (e) {
            throw new Error("Eroare server.");
        }

        let msg = document.getElementById("profileSavedMsg");

        if (data.errors) {
            msg.classList.remove("hidden");
            msg.classList.remove("bg-green-100", "text-green-700");
            msg.classList.add("bg-red-100", "text-red-700");

            if (data.errors.email) {
                msg.innerText = "✖ Emailul este utilizat de altcineva.";
            } else if (data.errors.name) {
                msg.innerText = "✖ Numele este utilizat de altcineva.";
            } else {
                msg.innerText = "✖ Date invalide.";
            }

            msg.style.opacity = 1;

            setTimeout(() => {
                msg.style.transition = "0.4s";
                msg.style.opacity = 0;
                setTimeout(() => msg.classList.add("hidden"), 400);
            }, 3000);

            return; 
        }

        if (data.success) {
            msg.classList.remove("hidden");
            msg.classList.remove("bg-red-100", "text-red-700");
            msg.classList.add("bg-green-100", "text-green-700");
            msg.innerText = "Modificările au fost salvate cu succes!";
            msg.style.opacity = 1;

            document.getElementById("editPassword").value = "";
        }

        setTimeout(() => {
            msg.style.transition = "0.4s";
            msg.style.opacity = 0;
            setTimeout(() => msg.classList.add("hidden"), 400);
        }, 3000);
    })
    .catch(err => console.error(err));
}

// Live Check
document.addEventListener("DOMContentLoaded", function () {
    const nameInput = document.getElementById("editName");
    const msgEl = document.getElementById("nameCheckMsg");
    const sugEl = document.getElementById("nameSuggestions");

    if (!nameInput) return;

    let timer = null;

    nameInput.addEventListener("input", function () {
        clearTimeout(timer);

        const name = this.value.trim();
        if (name.length < 3) {
            msgEl.innerHTML = "";
            sugEl.innerHTML = "";
            return;
        }

        timer = setTimeout(() => {
            fetch("{{ route('profile.checkName') }}", {
                method: "POST",
                headers: {
                    "X-CSRF-TOKEN": "{{ csrf_token() }}",
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ name })
            })
            .then(res => res.json())
            .then(data => {
                if (data.available) {
                    msgEl.innerHTML = 
                        `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Numele este disponibil
                        </span>`;
                    sugEl.innerHTML = "";
                } else {
                    msgEl.innerHTML =
                        `<span class='text-red-600 dark:text-red-400 flex items-center gap-1'>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            Numele este deja folosit
                        </span>`;

                    let html = `<div class="mt-2 p-3 bg-gray-50 dark:bg-[#252525] rounded-lg border border-gray-100 dark:border-[#333333]">
                                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Încearcă una din sugestii:</p>
                                    <div class="flex flex-wrap gap-2">`;

                    data.suggestions.forEach(function (s) {
                        html += `<button type="button" 
                                         class="px-2 py-1 text-xs font-medium bg-white dark:bg-[#2C2C2C] border border-gray-200 dark:border-[#404040] rounded hover:border-[#CC2E2E] dark:hover:border-[#CC2E2E] transition text-gray-700 dark:text-gray-300"
                                         onclick="useSuggestion('${s.replace(/'/g, "\\'")}')">
                                    ${s}
                                 </button>`;
                    });

                    html += `</div></div>`;
                    sugEl.innerHTML = html;
                }
            });
        }, 300);
    });
});

function useSuggestion(name) {
    const input = document.getElementById("editName");
    const msgEl = document.getElementById("nameCheckMsg");
    const sugEl = document.getElementById("nameSuggestions");

    if (input) input.value = name;
    
    if (msgEl) {
        msgEl.innerHTML = 
            `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Numele este disponibil
            </span>`;
    }
    if (sugEl) sugEl.innerHTML = "";
}
</script>

@endsection