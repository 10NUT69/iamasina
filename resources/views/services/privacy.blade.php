@extends('layouts.app')

@section('title', 'Politica de confidențialitate')
@section('meta_title', 'Politica de confidențialitate - iaAuto.ro')
@section('meta_description', 'Află cum sunt colectate și utilizate datele tale personale atunci când folosești iaAuto.ro.')

@section('content')
    <div class="max-w-5xl mx-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Politica de confidențialitate
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl">
                Respectăm confidențialitatea utilizatorilor și păstrăm datele la minimumul necesar pentru funcționarea
                platformei, publicarea anunțurilor și protejarea conturilor.
            </p>
        </header>

        <section class="space-y-4 md:space-y-5 mb-8">
            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    1. Ce date colectăm
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Atunci când îți creezi un cont sau publici un anunț, pot fi colectate date precum numele afișat,
                    adresa de e-mail, telefonul, localizarea anunțului, detaliile mașinii și imaginile încărcate.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    2. Cum folosim datele
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Datele sunt folosite pentru administrarea contului, afișarea anunțurilor auto, trimiterea mesajelor
                    de sistem și protejarea platformei împotriva abuzurilor. Nu vindem date personale în scopuri de marketing.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    3. Stocare și securitate
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Datele sunt stocate pe serverele folosite pentru găzduirea iaAuto.ro și pe serviciile necesare
                    funcționării platformei, precum e-mailul tranzacțional sau procesarea imaginilor.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    4. Drepturile tale
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Poți solicita acces la datele tale, corectarea informațiilor greșite, ștergerea anunțurilor sau a
                    contului, în limitele legislației aplicabile.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    5. Contact pentru date personale
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Pentru întrebări legate de datele tale sau pentru exercitarea drepturilor menționate mai sus, scrie la
                    <a href="mailto:contact@iaauto.ro" class="font-semibold text-[#C81424] hover:underline">contact@iaauto.ro</a>.
                </p>
            </article>
        </section>

        <section class="bg-[#fff4f5] dark:bg-[#2a1013] border border-red-100 dark:border-red-900/40 rounded-xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-[#8f111a] dark:text-red-100 mb-2">
                Confidențialitatea ta contează
            </h2>
            <p class="text-xs md:text-sm text-[#7f1d1d] dark:text-red-100/90 leading-relaxed">
                iaAuto.ro nu își propune să colecteze mai multe date decât are nevoie pentru a funcționa normal.
                Dacă ai o nelămurire, trimite-ne un mesaj și verificăm împreună.
            </p>
        </section>
    </div>
@endsection
