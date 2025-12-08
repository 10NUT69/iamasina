@extends('layouts.app')

@section('title', 'Termeni și condiții')
@section('meta_title', 'Termeni și condiții - MeseriasBun.ro')
@section('meta_description', 'Află care sunt regulile de utilizare a platformei MeseriasBun.ro, drepturile și responsabilitățile utilizatorilor.')

@section('content')
    <div class="max-w-5xl mx-auto">

        {{-- TITLU + INTRO --}}
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Termeni și condiții
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl text-justify">
                Prin folosirea MeseriasBun.ro, accepți regulile de mai jos. Am încercat să le formulăm cât mai clar,
                pe înțelesul tuturor. Dacă nu ești de acord cu acești termeni, te rugăm să nu folosești platforma.
            </p>
            <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500 text-justify">
                Acest text are rol informativ și nu înlocuiește consultanța juridică. Pentru o interpretare oficială,
                recomandăm consultarea unui specialist în domeniu.
            </p>
        </header>

        {{-- CARDURI TERMENI --}}
        <section class="space-y-4 md:space-y-5 mb-8">
            {{-- CARD 1 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    1. Rolul platformei
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    MeseriasBun.ro este o platformă de anunțuri pentru servicii. Scopul ei este să pună în legătură
                    persoane care au nevoie de un serviciu (ex: reparații, instalații, lucrări) cu meseriași sau firme
                    care pot oferi acele servicii.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Platforma nu este parte în contractul dintre client și meseriaș și nu garantează rezultatul lucrării,
                    prețul sau comportamentul părților implicate.
                </p>
            </article>

            {{-- CARD 2 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    2. Crearea contului și datele introduse
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    La crearea unui cont, ești responsabil de corectitudinea datelor introduse (nume, e-mail, număr de
                    telefon etc.). Nu folosi identitatea altei persoane și nu crea conturi false.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Ești responsabil de activitatea care se desfășoară în contul tău. Păstrează-ți parola în siguranță și
                    nu o divulga altor persoane.
                </p>
            </article>

            {{-- CARD 3 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    3. Publicarea anunțurilor
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Anunțurile publicate trebuie să descrie servicii reale, pe care chiar le poți oferi. Textele și
                    imaginile nu trebuie să fie înșelătoare, vulgare sau să încalce drepturile altor persoane.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Ne rezervăm dreptul de a șterge sau dezactiva anunțuri care încalcă bunul-simț, legislația în vigoare
                    sau regulile platformei, fără o notificare prealabilă.
                </p>
            </article>

            {{-- CARD 4 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    4. Răspundere și limitări
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    MeseriasBun.ro nu poate fi tras la răspundere pentru:
                    întârzieri, lucrări executate defectuos, neînțelegeri între client și meseriaș,
                    pierderi financiare sau orice alte consecințe rezultate în urma colaborărilor dintre utilizatori.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Recomandăm să discuți toate detaliile în prealabil (preț, termen, materiale, garanție)
                    și să păstrezi dovezi ale discuțiilor sau ale plăților efectuate.
                </p>
            </article>

            {{-- CARD 5 --}}
            <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    5. Modificarea termenilor
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                    Termenii și condițiile pot fi actualizați periodic, în funcție de evoluția platformei sau de schimbările
                    legislative. Vom încerca să anunțăm modificările importante într-un mod vizibil pe site.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2 text-justify">
                    Continuarea utilizării MeseriasBun.ro după actualizarea acestor termeni înseamnă acceptarea versiunii
                    noi.
                </p>
            </article>
        </section>

        {{-- BANDĂ CONCLUZIE --}}
        <section class="bg-blue-50 dark:bg-blue-900/10 border border-blue-100 dark:border-blue-800 rounded-2xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-blue-900 dark:text-blue-100 mb-2">
                Folosește platforma cu bun-simț
            </h2>
            <p class="text-xs md:text-sm text-blue-800/80 dark:text-blue-100/90 leading-relaxed text-justify">
                MeseriasBun.ro își propune să fie un loc curat și util pentru toată lumea. Dacă folosești platforma cu
                bun-simț, respect față de ceilalți și un minim de atenție la detalii, experiența va fi mai bună pentru
                toți cei implicați.
            </p>
        </section>

    </div>
@endsection
