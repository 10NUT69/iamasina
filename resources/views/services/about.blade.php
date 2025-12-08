@extends('layouts.app')

@section('title', 'Despre MeseriasBun.ro')
@section('meta_title', 'Despre MeseriasBun.ro - Platformă pentru meseriași și servicii în România')
@section('meta_description', 'MeseriasBun.ro este o platformă gândită pentru omul de rând, care are nevoie să găsească rapid un meseriaș bun, fără bătăi de cap, cu anunțuri gratuite și fără reclame invazive.')

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- TITLU + INTRO --}}
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Despre MeseriasBun.ro
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl text-justify">
                O platformă gândită ca o unealtă utilă pentru omul de rând, care are nevoie să găsească rapid un meseriaș
                bun – fără bătăi de cap, fără căutări pe zeci de site-uri.
            </p>
        </header>

        {{-- GRID CU 3 CARDURI --}}
        <section class="grid gap-4 md:gap-6 md:grid-cols-3">
            {{-- CARD 1 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    De ce am creat platforma
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3 text-justify">
                    MeseriasBun.ro a pornit de la o nevoie foarte simplă: atunci când ai o problemă în casă sau ai nevoie
                    de un serviciu, vrei să găsești rapid un specialist, nu să pierzi timp pe platforme amestecate, cu
                    anunțuri din toate domeniile.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Ideea de bază este să existe un
                    <span class="font-semibold">singur loc dedicat meseriașilor și serviciilor</span> – electricieni,
                    instalatori, constructori, servicii de curățenie, reparații și multe altele.
                </p>
            </article>

            {{-- CARD 2 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Anunțuri gratuit, oricâte ai nevoie
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3 text-justify">
                    MeseriasBun.ro a fost gândit ca o unealtă reală pentru meseriași și firme mici, nu ca o barieră în
                    calea lor. De aceea, poți publica
                    <span class="font-semibold">câte anunțuri vrei, gratuit</span>.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Planul este clar: anunțurile obișnuite vor rămâne gratuite atâta timp cât va exista acest domeniu.
                    Singura opțiune contra cost va fi rubrica de
                    <span class="font-semibold">anunțuri promovate</span>, pentru cei care își doresc mai multă
                    vizibilitate.
                </p>
            </article>

            {{-- CARD 3 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Fără reclame invazive
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3 text-justify">
                    Nu îmi doresc un site plin de bannere și pop-up-uri care acoperă conținutul. Scopul platformei este
                    să te ajute să găsești rapid ce cauți, nu să te încurce.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    De aceea, pe MeseriasBun.ro
                    <span class="font-semibold">nu vei vedea reclame agresive</span>. Accentul rămâne pe anunțuri și pe
                    meseriași, nu pe publicitate.
                </p>
            </article>
        </section>

        {{-- BANDĂ DE CONCLUZIE --}}
        <section class="mt-8 md:mt-10 bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-2xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-blue-900 dark:text-blue-100 mb-2">
                Pentru omul de rând, în primul rând
            </h2>
            <p class="text-xs md:text-sm text-blue-800/80 dark:text-blue-100/90 leading-relaxed text-justify">
                MeseriasBun.ro este făcut pentru oamenii simpli care au nevoie, din când în când, de ajutor: o reparație,
                o instalație, o renovare sau un serviciu specializat. Dacă platforma te ajută să găsești un meseriaș bun
                mai ușor, înseamnă că și-a atins scopul.
            </p>
        </section>

    </div>
@endsection
