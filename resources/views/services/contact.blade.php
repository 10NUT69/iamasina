@extends('layouts.app')

@section('title', 'Contact MeseriasBun.ro')
@section('meta_title', 'Contact MeseriasBun.ro - Ai întrebări sau sugestii?')
@section('meta_description', 'Intră în legătură cu MeseriasBun.ro pentru întrebări, sugestii sau probleme legate de utilizarea platformei.')

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- TITLU + INTRO --}}
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Contact MeseriasBun.ro
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl text-justify">
                Dacă ai o întrebare, o problemă tehnică sau o idee care crezi că poate îmbunătăți platforma,
                poți lua legătura cu mine folosind datele de mai jos. Răspund de fiecare dată când timpul îmi permite.
            </p>
        </header>

        {{-- GRID 2 CARDURI --}}
        <section class="grid gap-4 md:gap-6 md:grid-cols-2 mb-8">
            {{-- CARD 1: Date de contact --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Cum mă poți contacta
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3 text-justify">
                    Momentan, MeseriasBun.ro este un proiect dezvoltat și administrat în principal de o singură persoană.
                    Nu există call-center sau suport telefonic dedicat, însă încerc să răspund cât mai repede la mesaje.
                </p>

                <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <li>
                        <span class="font-semibold">E-mail suport:</span>
                        <a href="mailto:contact@meseriasbun.ro" class="text-[#CC2E2E] hover:underline">
                            contact@meseriasbun.ro
                        </a>
                    </li>
                    <li class="text-xs text-gray-500 dark:text-gray-400 pt-1 text-justify">
                        Te rog să descrii cât mai clar problema sau sugestia ta, eventual cu link către anunț sau
                        capturi de ecran, dacă este cazul.
                    </li>
                </ul>
            </article>

            {{-- CARD 2: Sugestii / Feedback --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Sugestii și feedback
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3 text-justify">
                    MeseriasBun.ro este o platformă în continuă dezvoltare. Dacă vezi ceva ce poate fi îmbunătățit,
                    o idee de funcționalitate nouă sau un bug, spune-mi.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Orice feedback sincer ajută: de la lucruri tehnice până la texte, mesaje sau modul în care
                    sunt afișate anunțurile. Scopul este să fie cât mai utilă pentru oamenii care chiar au nevoie
                    de meseriași buni.
                </p>
            </article>
        </section>

        {{-- BANDĂ INFORMATIVĂ --}}
        <section class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-2xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-blue-900 dark:text-blue-100 mb-2">
                Important de știut
            </h2>
            <p class="text-xs md:text-sm text-blue-800/80 dark:text-blue-100/90 leading-relaxed text-justify">
                MeseriasBun.ro nu intermediază plăți și nu garantează calitatea lucrărilor. Platforma doar pune în
                legătură oamenii cu meseriașii. Înainte de a colabora, verifică atent datele persoanei, discută
                detaliile și păstrează o formă de confirmare a lucrării (mesaje, ofertă, factură etc.).
            </p>
        </section>

    </div>
@endsection
