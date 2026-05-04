@extends('layouts.app')

@section('title', 'Contul meu - iaAuto.ro')

@section('content')

<div class="max-w-[1536px] mx-auto mt-10 mb-20 px-4 sm:px-6 lg:px-8">

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
           class="px-5 py-3 bg-[#C81424] hover:bg-[#94111B] text-white font-bold rounded-xl shadow-lg
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
                       ? 'text-[#C81424] border-b-2 border-[#C81424]'
                       : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                   Anunțurile mele
                </a>
            </li>
            <li>
                <a href="?tab=favorite"
                   class="pb-3 inline-block transition-colors whitespace-nowrap
                   {{ request('tab') === 'favorite'
                       ? 'text-[#C81424] border-b-2 border-[#C81424]'
                       : 'text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200' }}">
                   Favorite
                </a>
            </li>
            <li>
                <a href="?tab=profil"
                   class="pb-3 inline-block transition-colors whitespace-nowrap
                   {{ request('tab') === 'profil'
                       ? 'text-[#C81424] border-b-2 border-[#C81424]'
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

        <div id="myServicesList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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

                    <div class="grid grid-cols-3 gap-2">
                        <button type="button"
                                data-id="{{ $service->id }}"
                                data-url="{{ route('services.renew', $service->id) }}"
                                onclick="refreshService(this)"
                                title="Reactualizeaza anuntul"
                                class="px-2 py-2 text-xs font-semibold bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/40 transition flex items-center justify-center gap-1">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                            </svg>
                            <span class="hidden sm:inline">Reactualizare</span>
                        </button>
                        <a href="{{ route('services.edit', $service->id) }}"
                           class="px-3 py-2 text-sm font-medium text-center bg-[#fff4f5] dark:bg-[#2a1013] text-[#C81424] dark:text-red-300 rounded-lg hover:bg-[#ffe7ea] dark:hover:bg-[#3a171c] transition">
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

                    <div class="mt-2 grid grid-cols-3 gap-2">
                        <button type="button"
                                data-url="{{ $service->public_url }}"
                                onclick="setShareUrl(this); shareFacebook()"
                                class="w-full px-2 py-2 text-xs font-medium bg-blue-50 dark:bg-blue-900/20 text-blue-700 dark:text-blue-300 rounded-lg hover:bg-blue-100 dark:hover:bg-blue-900/35 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-blue-600 dark:text-blue-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M22 12a10 10 0 10-11.5 9.9v-7H8v-3h2.5V9.8c0-2.5 1.5-3.9 3.8-3.9 1.1 0 2.2.2 2.2.2v2.4h-1.2c-1.2 0-1.6.8-1.6 1.6V12H16.9l-.4 3h-2.2v7A10 10 0 0022 12z"/>
                            </svg>
                            <span class="hidden sm:inline">Facebook</span>
                        </button>

                        <button type="button"
                                data-url="{{ $service->public_url }}"
                                data-title="{{ e($service->title) }}"
                                onclick="setShareUrl(this); shareWhatsapp()"
                                class="w-full px-2 py-2 text-xs font-medium bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300 rounded-lg hover:bg-green-100 dark:hover:bg-green-900/35 transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 text-green-600 dark:text-green-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M20.5 3.5A11 11 0 003.9 18.6L3 22l3.5-.9A11 11 0 1020.5 3.5zm-9.5 18a8.9 8.9 0 01-4.5-1.2l-.3-.2-2.1.5.6-2-.2-.3A9 9 0 1111 21.5zm5.2-6.6c-.3-.2-1.7-.8-2-.9s-.5-.2-.7.2-.8.9-1 1.1-.4.2-.7 0a7.4 7.4 0 01-2.2-1.4 8.2 8.2 0 01-1.5-1.9c-.2-.3 0-.5.1-.6l.5-.6c.2-.2.2-.4.3-.6s0-.4 0-.6-.7-1.7-1-2.3c-.3-.7-.6-.6-.7-.6h-.6c-.2 0-.6.1-.9.4s-1.2 1.1-1.2 2.8 1.2 3.2 1.4 3.4a13 13 0 005 4.6c.7.3 1.2.5 1.6.6.7.2 1.3.2 1.8.1.6-.1 1.7-.7 1.9-1.4s.2-1.2.1-1.4-.3-.2-.6-.4z"/>
                            </svg>
                            <span class="hidden sm:inline">WhatsApp</span>
                        </button>

                        <button type="button"
                                data-url="{{ $service->public_url }}"
                                onclick="setShareUrl(this); copyLink(this)"
                                class="w-full px-2 py-2 text-xs font-medium bg-gray-50 dark:bg-[#252525] text-gray-700 dark:text-gray-200 rounded-lg hover:bg-gray-100 dark:hover:bg-[#2f2f2f] transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-2 10h2a2 2 0 002-2V10a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                            </svg>
                            <span class="hidden sm:inline">Copiaza</span>
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

        <div id="favoriteList" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
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

   <div class="w-full">

       <div class="bg-white dark:bg-[#1E1E1E] border border-gray-200 dark:border-[#333333] shadow-xl rounded-2xl overflow-hidden flex flex-col md:flex-row transition-colors">

           <div class="w-full md:w-1/4 bg-gray-50 dark:bg-[#181818] p-6 border-b md:border-b-0 md:border-r border-gray-200 dark:border-[#333333] flex flex-col items-center text-center justify-center">

                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-gradient-to-br from-[#C81424] to-[#5f0f14] text-white flex items-center justify-center text-2xl font-bold shadow-lg mb-3 select-none">
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
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#C81424] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                </div>
                                <input id="editName" type="text" value="{{ auth()->user()->name }}"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm">
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
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#C81424] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <input id="editEmail" type="email" value="{{ auth()->user()->email }}"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm">
                            </div>
                        </div>
                    </div>
{{-- TIP CONT + DATE PARC AUTO --}}
<div class="bg-gray-50 dark:bg-[#252525] rounded-2xl border border-gray-200 dark:border-[#333333] p-5">
    <h3 class="text-sm font-bold text-gray-900 dark:text-white mb-4 flex items-center gap-2">
        <span class="text-base">🏷️</span> Tip cont
    </h3>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <div>
            <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                Selectează tipul
            </label>

            @php $ut = auth()->user()->user_type ?? 'individual'; @endphp

            <select id="editUserType"
                class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                       bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                       focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm">
                <option value="individual" {{ $ut === 'individual' ? 'selected' : '' }}>Persoană fizică</option>
                <option value="dealer" {{ $ut === 'dealer' ? 'selected' : '' }}>Parc auto</option>
            </select>

            <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">
                Dacă alegi „Parc auto”, îți apar câmpuri suplimentare.
            </p>
        </div>
    </div>

    <div id="dealerFieldsProfile" class="mt-5 space-y-4 hidden">
	<p class="text-xs text-gray-500 dark:text-gray-400 mb-2">
        🔒 Datele firmei sunt afișate public pe anunțuri și cresc încrederea cumpărătorilor.
    </p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div class="md:col-span-2">
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                    Nume parc auto / firmă
                </label>
                <input id="editCompanyName" type="text" value="{{ auth()->user()->company_name }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Ex: AutoBest SRL">
            </div>
			<div class="mt-2 min-h-[20px] text-sm font-medium" id="companyCheckMsg"></div>
<div class="mt-1 text-sm" id="companySuggestions"></div>


            <div>
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">CUI</label>
                <input id="editCui" type="text" value="{{ auth()->user()->cui }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="RO12345678">
            </div>

            <div>
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telefon parc auto</label>
                <input id="editDealerPhone" type="text" value="{{ auth()->user()->phone }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="07xx xxx xxx">
            </div>

            <div>
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telefon 2</label>
                <input id="editDealerPhone2" type="text" value="{{ auth()->user()->phone_2 }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Opțional">
            </div>

            <div>
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Telefon 3</label>
                <input id="editDealerPhone3" type="text" value="{{ auth()->user()->phone_3 }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Opțional">
            </div>

            <div>
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Județ</label>
                <input id="editCounty" type="text" value="{{ auth()->user()->county }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Ex: Cluj">
            </div>

            <div>
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Oraș</label>
                <input id="editCity" type="text" value="{{ auth()->user()->city }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Ex: Cluj-Napoca">
            </div>

            <div class="md:col-span-2">
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Adresă</label>
                <input id="editAddress" type="text" value="{{ auth()->user()->address }}"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Stradă, număr">
            </div>

            <div class="md:col-span-2">
                <label class="block mb-2 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-wider">Descriere parc auto</label>
                <textarea id="editDealerDescription" rows="5"
                    class="w-full px-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                           bg-white dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm"
                    placeholder="Scrie câteva detalii despre parc, servicii, program sau avantajele pentru cumpărători.">{{ auth()->user()->dealer_description }}</textarea>
            </div>
        </div>

        <div class="mt-5 rounded-2xl border border-gray-200 bg-white p-4 dark:border-[#333333] dark:bg-[#1E1E1E]">
            <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                <div>
                    <h4 class="text-sm font-bold text-gray-900 dark:text-white">Galerie profil parc auto</h4>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Pozele apar pe pagina publică a parcului auto. Poți adăuga maximum 12 imagini.</p>
                    @if(auth()->user()->dealer_public_url)
                        <a href="{{ auth()->user()->dealer_public_url }}" target="_blank" class="mt-2 inline-flex text-xs font-bold text-[#C81424] hover:text-[#94111B]">
                            Vezi pagina publică
                        </a>
                    @endif
                </div>

                <div class="flex flex-col gap-2 sm:min-w-[240px]">
                    <input id="dealerGalleryInput" type="file" accept="image/jpeg,image/png,image/webp" multiple
                        class="block w-full text-xs text-gray-600 file:mr-3 file:rounded-lg file:border-0 file:bg-[#C81424] file:px-3 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-[#94111B] dark:text-gray-300">
                    <button type="button" onclick="uploadDealerGallery()"
                        class="inline-flex h-10 items-center justify-center rounded-lg bg-gray-900 px-4 text-xs font-bold uppercase text-white transition hover:bg-black dark:bg-white dark:text-gray-900">
                        Încarcă poze
                    </button>
                </div>
            </div>

            <div id="dealerGalleryMsg" class="mt-3 hidden rounded-lg px-3 py-2 text-sm font-semibold"></div>

            <div id="dealerGalleryPreview" class="mt-4 grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-4">
                @foreach((auth()->user()->dealer_gallery_urls ?? []) as $index => $imageUrl)
                    <div class="relative overflow-hidden rounded-xl border border-gray-100 bg-gray-100 aspect-square dark:border-[#333] dark:bg-[#252525]" data-gallery-item="{{ $index }}">
                        <img src="{{ $imageUrl }}" alt="Imagine parc auto {{ $index + 1 }}" class="h-full w-full object-cover">
                        <button type="button" onclick="deleteDealerGalleryImage({{ $index }})"
                            class="absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-white transition hover:bg-[#C81424]"
                            title="Șterge imaginea">
                            ×
                        </button>
                    </div>
                @endforeach
            </div>
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
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400 group-focus-within:text-[#C81424] transition-colors">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z" />
                                    </svg>
                                </div>
                                <input id="editPassword" type="password" placeholder="Lasă gol dacă nu schimbi"
                                    class="w-full pl-10 pr-4 py-3 rounded-xl border border-gray-300 dark:border-[#404040]
                                           bg-gray-50 dark:bg-[#2C2C2C] text-gray-900 dark:text-white text-sm font-medium
                                           focus:ring-2 focus:ring-[#C81424]/20 focus:border-[#C81424] outline-none transition shadow-sm">
                            </div>
                        </div>
                    </div>

                    <div class="pt-4 flex justify-start">
                        <button onclick="updateProfile()"
                            class="px-6 py-3 rounded-xl text-white font-bold text-sm tracking-wide
                                   bg-[#C81424] hover:bg-[#94111B]
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
let currentShareUrl = '';
let currentShareTitle = '';

function refreshService(btn) {
    const url = btn.getAttribute('data-url');
    const originalContent = btn.innerHTML;

    btn.disabled = true;
    btn.innerHTML = `<svg class="animate-spin h-4 w-4 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>`;

    fetch(url, {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json",
            "Accept": "application/json"
        }
    })
    .then(async res => {
        const data = await res.json().catch(() => ({}));

        if (res.ok && (data.status === "success" || data.success)) {
            window.location.reload();
            return;
        }

        alert(data.message || "Nu am putut reactualiza anuntul.");
        btn.innerHTML = originalContent;
        btn.disabled = false;
    })
    .catch(() => {
        alert("Eroare de conexiune.");
        btn.innerHTML = originalContent;
        btn.disabled = false;
    });
}

function setShareUrl(btn) {
    currentShareUrl = btn.dataset.url || '';
    currentShareTitle = btn.dataset.title || '';
}

function shareFacebook() {
    if (!currentShareUrl) return;
    window.open(
        'https://www.facebook.com/sharer/sharer.php?u=' + encodeURIComponent(currentShareUrl),
        '_blank'
    );
}

function shareWhatsapp() {
    if (!currentShareUrl) return;
    window.open(
        'https://api.whatsapp.com/send?text=' + encodeURIComponent((currentShareTitle ? currentShareTitle + ' - ' : '') + currentShareUrl),
        '_blank'
    );
}

function copyLink(btn) {
    if (!currentShareUrl) return;

    const done = () => {
        const original = btn.innerHTML;
        btn.innerHTML = `<svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>`;
        setTimeout(() => { btn.innerHTML = original; }, 900);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(currentShareUrl).then(done).catch(done);
        return;
    }

    const textarea = document.createElement('textarea');
    textarea.value = currentShareUrl;
    document.body.appendChild(textarea);
    textarea.select();
    document.execCommand('copy');
    document.body.removeChild(textarea);
    done();
}

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

function setDealerGalleryMessage(message, success = true) {
    const msg = document.getElementById("dealerGalleryMsg");
    if (!msg) return;

    msg.classList.remove("hidden", "bg-green-100", "text-green-700", "bg-red-100", "text-red-700");
    msg.classList.add(success ? "bg-green-100" : "bg-red-100", success ? "text-green-700" : "text-red-700");
    msg.textContent = message;
}

function renderDealerGallery(gallery = []) {
    const preview = document.getElementById("dealerGalleryPreview");
    if (!preview) return;

    preview.innerHTML = "";

    gallery.forEach((item) => {
        const wrapper = document.createElement("div");
        wrapper.className = "relative overflow-hidden rounded-xl border border-gray-100 bg-gray-100 aspect-square dark:border-[#333] dark:bg-[#252525]";
        wrapper.dataset.galleryItem = item.index;

        const image = document.createElement("img");
        image.src = item.url;
        image.alt = `Imagine parc auto ${Number(item.index) + 1}`;
        image.className = "h-full w-full object-cover";

        const button = document.createElement("button");
        button.type = "button";
        button.title = "Șterge imaginea";
        button.className = "absolute right-2 top-2 inline-flex h-8 w-8 items-center justify-center rounded-full bg-black/70 text-white transition hover:bg-[#C81424]";
        button.textContent = "×";
        button.addEventListener("click", () => deleteDealerGalleryImage(item.index));

        wrapper.appendChild(image);
        wrapper.appendChild(button);
        preview.appendChild(wrapper);
    });
}

function uploadDealerGallery() {
    const input = document.getElementById("dealerGalleryInput");
    if (!input || !input.files.length) {
        setDealerGalleryMessage("Alege cel puțin o imagine.", false);
        return;
    }

    const formData = new FormData();
    Array.from(input.files).forEach((file) => formData.append("dealer_images[]", file));

    fetch("{{ route('profile.dealerGallery.upload') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: formData
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.success) {
            setDealerGalleryMessage(data.message || "Nu am putut încărca imaginile.", false);
            return;
        }

        input.value = "";
        renderDealerGallery(data.gallery || []);
        setDealerGalleryMessage("Galeria a fost actualizată.");
    })
    .catch(() => setDealerGalleryMessage("Eroare de conexiune la încărcarea imaginilor.", false));
}

function deleteDealerGalleryImage(index) {
    if (!confirm("Sigur ștergi această imagine?")) return;

    fetch("{{ url('/profile/dealer-gallery') }}/" + index, {
        method: "DELETE",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        }
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({}));

        if (!res.ok || !data.success) {
            setDealerGalleryMessage(data.message || "Nu am putut șterge imaginea.", false);
            return;
        }

        renderDealerGallery(data.gallery || []);
        setDealerGalleryMessage("Imaginea a fost ștearsă.");
    })
    .catch(() => setDealerGalleryMessage("Eroare de conexiune la ștergerea imaginii.", false));
}

function updateProfile() {
    let name     = document.getElementById("editName")?.value || "";
    let email    = document.getElementById("editEmail")?.value || "";
    let password = document.getElementById("editPassword")?.value || "";

    const userType = document.getElementById("editUserType")?.value || "individual";

    const payload = {
        name,
        email,
        password,
        user_type: userType,

        company_name: document.getElementById("editCompanyName")?.value || null,
        cui:          document.getElementById("editCui")?.value || null,
        phone:        document.getElementById("editDealerPhone")?.value || null,
        phone_2:      document.getElementById("editDealerPhone2")?.value || null,
        phone_3:      document.getElementById("editDealerPhone3")?.value || null,
        county:       document.getElementById("editCounty")?.value || null,
        city:         document.getElementById("editCity")?.value || null,
        address:      document.getElementById("editAddress")?.value || null,
        dealer_description: document.getElementById("editDealerDescription")?.value || null,
    };

    // dacă nu e dealer, trimitem null (controller oricum golește)
    if (userType !== "dealer") {
        payload.company_name = null;
        payload.cui = null;
        payload.phone = null;
        payload.phone_2 = null;
        payload.phone_3 = null;
        payload.county = null;
        payload.city = null;
        payload.address = null;
        payload.dealer_description = null;
    }

    fetch("{{ route('profile.ajaxUpdate') }}", {
        method: "POST",
        headers: {
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Content-Type": "application/json",
            "Accept": "application/json"
        },
        body: JSON.stringify(payload)
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
            } else if (data.errors.user_type) {
                msg.innerText = "✖ Tip cont invalid.";
            } else if (data.errors.company_name) {
                msg.innerText = "✖ Completează numele parcului auto.";
            } else if (data.errors.phone) {
                msg.innerText = "✖ Completează telefonul parcului auto.";
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

            const passEl = document.getElementById("editPassword");
            if (passEl) passEl.value = "";
        }

        setTimeout(() => {
            msg.style.transition = "0.4s";
            msg.style.opacity = 0;
            setTimeout(() => msg.classList.add("hidden"), 400);
        }, 3000);
    })
    .catch(err => console.error(err));
}

// Live Check + Dealer toggle
document.addEventListener("DOMContentLoaded", function () {

    // ===== Dealer fields toggle =====
    const userTypeSel = document.getElementById("editUserType");
    const dealerBox   = document.getElementById("dealerFieldsProfile");

    function refreshDealerBox() {
        if (!userTypeSel || !dealerBox) return;
        dealerBox.classList.toggle("hidden", userTypeSel.value !== "dealer");
    }

    if (userTypeSel && dealerBox) {
        userTypeSel.addEventListener("change", refreshDealerBox);
        refreshDealerBox(); // init
    }

    // ===== Live check name =====
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

                    (data.suggestions || []).forEach(function (s) {
                        html += `<button type="button"
                                         class="px-2 py-1 text-xs font-medium bg-white dark:bg-[#2C2C2C] border border-gray-200 dark:border-[#404040] rounded hover:border-[#C81424] dark:hover:border-[#C81424] transition text-gray-700 dark:text-gray-300"
                                         onclick="useSuggestion('${String(s).replace(/'/g, "\\'")}')">
                                    ${s}
                                 </button>`;
                    });

                    html += `</div></div>`;
                    sugEl.innerHTML = html;
                }
            });
        }, 300);
    });

    // ===== ADAUGAT: Live check pentru Nume Parc Auto (company_name) =====
    // (rulează doar dacă există inputul + e selectat dealer)
    const companyInput = document.getElementById("editCompanyName");
    const companyMsgEl = document.getElementById("companyCheckMsg");
    const companySugEl = document.getElementById("companySuggestions");

    let timerCompany = null;

    if (companyInput) {
        companyInput.addEventListener("input", function () {
            clearTimeout(timerCompany);

            // doar când e dealer
            if (userTypeSel && userTypeSel.value !== "dealer") {
                if (companyMsgEl) companyMsgEl.innerHTML = "";
                if (companySugEl) companySugEl.innerHTML = "";
                return;
            }

            const company_name = this.value.trim();
            if (company_name.length < 3) {
                if (companyMsgEl) companyMsgEl.innerHTML = "";
                if (companySugEl) companySugEl.innerHTML = "";
                return;
            }

            timerCompany = setTimeout(() => {
                fetch("{{ route('profile.checkCompanyName') }}", {
                    method: "POST",
                    headers: {
                        "X-CSRF-TOKEN": "{{ csrf_token() }}",
                        "Content-Type": "application/json",
                    },
                    body: JSON.stringify({ company_name })
                })
                .then(res => res.json())
                .then(data => {
                    if (!companyMsgEl || !companySugEl) return;

                    if (data.available) {
                        companyMsgEl.innerHTML =
                            `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                Numele parcului este disponibil
                            </span>`;
                        companySugEl.innerHTML = "";
                    } else {
                        companyMsgEl.innerHTML =
                            `<span class='text-red-600 dark:text-red-400 flex items-center gap-1'>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                Numele parcului este deja folosit
                            </span>`;

                        let html = `<div class="mt-2 p-3 bg-gray-50 dark:bg-[#252525] rounded-lg border border-gray-100 dark:border-[#333333]">
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mb-2">Încearcă una din sugestii:</p>
                                        <div class="flex flex-wrap gap-2">`;

                        (data.suggestions || []).forEach(function (s) {
                            html += `<button type="button"
                                             class="px-2 py-1 text-xs font-medium bg-white dark:bg-[#2C2C2C] border border-gray-200 dark:border-[#404040] rounded hover:border-[#C81424] dark:hover:border-[#C81424] transition text-gray-700 dark:text-gray-300"
                                             onclick="useCompanySuggestion('${String(s).replace(/'/g, "\\'")}')">
                                        ${s}
                                     </button>`;
                        });

                        html += `</div></div>`;
                        companySugEl.innerHTML = html;
                    }
                });
            }, 300);
        });
    }

});

// (păstrată exact cum ai avut-o)
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

// ===== ADAUGAT: sugestie pentru nume parc auto =====
function useCompanySuggestion(val) {
    const input = document.getElementById("editCompanyName");
    const msgEl = document.getElementById("companyCheckMsg");
    const sugEl = document.getElementById("companySuggestions");

    if (input) input.value = val;

    if (msgEl) {
        msgEl.innerHTML =
            `<span class='text-green-600 dark:text-green-400 flex items-center gap-1'>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                Numele parcului este disponibil
            </span>`;
    }
    if (sugEl) sugEl.innerHTML = "";
}
</script>


@endsection
