@extends('layouts.app')

@section('title', 'Contact iaAuto.ro')
@section('meta_title', 'Contact iaAuto.ro - Întrebări și sugestii')
@section('meta_description', 'Intră în legătură cu iaAuto.ro pentru întrebări, sugestii sau probleme legate de anunțuri auto și utilizarea platformei.')

@section('content')
    <div class="max-w-5xl mx-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Contact iaAuto.ro
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl">
                Dacă ai o întrebare, o problemă tehnică sau o sugestie pentru platformă, ne poți scrie folosind
                adresa de mai jos. Pentru sesizări legate de un anunț, trimite și linkul anunțului.
            </p>
        </header>

        <section class="grid gap-4 md:gap-6 md:grid-cols-2 mb-8">
            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Suport și sesizări
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                    Răspundem în funcție de disponibilitate, iar mesajele clare ajută mult: descrierea problemei,
                    browserul folosit și, unde este cazul, o captură de ecran.
                </p>

                <ul class="text-sm text-gray-700 dark:text-gray-300 space-y-1">
                    <li>
                        <span class="font-semibold">E-mail suport:</span>
                        <a href="mailto:contact@iaauto.ro" class="text-[#C81424] hover:underline">
                            contact@iaauto.ro
                        </a>
                    </li>
                </ul>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-3">
                    Sugestii pentru platformă
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-3">
                    iaAuto.ro este în dezvoltare, iar feedbackul concret ne ajută să prioritizăm ce contează:
                    filtre, pagini de anunț, listare, cont de utilizator sau optimizare pentru mobil.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Dacă observi o problemă la creare, editare sau afișarea unui anunț auto, trimite detaliile și
                    vom verifica.
                </p>
            </article>
        </section>

        <section class="bg-[#fff4f5] dark:bg-[#2a1013] border border-red-100 dark:border-red-900/40 rounded-xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-[#8f111a] dark:text-red-100 mb-2">
                Important de știut
            </h2>
            <p class="text-xs md:text-sm text-[#7f1d1d] dark:text-red-100/90 leading-relaxed">
                iaAuto.ro nu intermediază plăți și nu garantează starea tehnică a mașinilor. Înainte de cumpărare,
                verifică actele, istoricul, vânzătorul și mașina prin metode independente.
            </p>
        </section>
    </div>
@endsection
