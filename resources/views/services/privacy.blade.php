@extends('layouts.app')

@section('title', 'Politica de confidențialitate')
@section('meta_title', 'Politica de confidențialitate - MeseriasBun.ro')
@section('meta_description', 'Află cum sunt colectate și utilizate datele tale personale atunci când folosești MeseriasBun.ro.')

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- TITLU + INTRO --}}
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Politica de confidențialitate
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl text-justify">
                Îți respect confidențialitatea și încerc să păstrez datele la minimumul necesar pentru funcționarea
                platformei. Mai jos găsești, pe scurt, ce date sunt colectate, cum sunt folosite și care sunt drepturile tale.
            </p>
        </header>

        {{-- CARDURI CONFIDENȚIALITATE --}}
        <section class="space-y-4 md:space-y-5 mb-8">
            {{-- CARD 1 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    1. Ce date colectăm
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Atunci când îți creezi un cont sau publici un anunț, pot fi colectate următoarele date:
                </p>
                <ul class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed list-disc list-inside space-y-1 text-justify">
                    <li>nume afișat și adresă de e-mail;</li>
                    <li>număr de telefon (dacă îl adaugi în anunț);</li>
                    <li>conținutul anunțurilor publicate și imaginile încărcate;</li>
                    <li>date tehnice minime (ex: IP, browser) pentru securitate și statistică.</li>
                </ul>
            </article>

            {{-- CARD 2 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    2. Cum folosim aceste date
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Datele sunt folosite pentru:
                </p>
                <ul class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed list-disc list-inside space-y-1 text-justify">
                    <li>crearea și administrarea contului tău;</li>
                    <li>afișarea anunțurilor publicate de tine pe platformă;</li>
                    <li>trimiterea de e-mailuri de sistem (ex: resetare parolă);</li>
                    <li>protejarea platformei împotriva abuzurilor și a încercărilor de fraudă.</li>
                </ul>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Nu vindem și nu cedăm mai departe datele tale personale unor terți în scopuri de marketing.
                </p>
            </article>

            {{-- CARD 3 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    3. Stocarea datelor și securitate
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Datele sunt stocate pe serverele folosite pentru găzduirea MeseriasBun.ro și pe serviciile necesare
                    trimiterii de e-mailuri (ex: furnizorul de e-mail tranzacțional).
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Sunt folosite măsuri tehnice rezonabile pentru a proteja datele, însă niciun sistem nu poate fi
                    garantat 100% sigur. Te rugăm să îți păstrezi parola în siguranță și să nu o folosești și pe alte site-uri.
                </p>
            </article>

            {{-- CARD 4 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    4. Drepturile tale
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    În limitele legislației aplicabile, ai dreptul să:
                </p>
                <ul class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed list-disc list-inside space-y-1 text-justify">
                    <li>soliciți acces la datele tale din cont;</li>
                    <li>corectezi datele greșite sau incomplete;</li>
                    <li>ștergi anunțurile publicate sau contul, dacă nu mai dorești să folosești platforma;</li>
                    <li>ne contactezi pentru orice nelămurire legată de datele tale.</li>
                </ul>
            </article>

            {{-- CARD 5 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    5. Contact pentru date personale
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Pentru întrebări legate de datele tale sau pentru exercitarea drepturilor menționate mai sus,
                    mă poți contacta la:
                </p>
                <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    <span class="font-semibold">E-mail:</span>
                    <a href="mailto:contact@meseriasbun.ro" class="text-[#CC2E2E] hover:underline">
                        contact@meseriasbun.ro
                    </a>
                </p>
            </article>
        </section>

        {{-- BANDĂ CONCLUZIE --}}
        <section class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-2xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-blue-900 dark:text-blue-100 mb-2">
                Confidențialitatea ta contează
            </h2>
            <p class="text-xs md:text-sm text-blue-800/80 dark:text-blue-100/90 leading-relaxed text-justify">
                MeseriasBun.ro nu își propune să colecteze mai multe date decât este necesar pentru a funcționa normal.
                Dacă ai orice îngrijorare legată de confidențialitate, trimite un mesaj și voi încerca să găsim
                cea mai bună soluție.
            </p>
        </section>

    </div>
@endsection
