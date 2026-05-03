@extends('layouts.app')

@section('title', 'Termeni și condiții')
@section('meta_title', 'Termeni și condiții - iaAuto.ro')
@section('meta_description', 'Află care sunt regulile de utilizare a platformei iaAuto.ro, drepturile și responsabilitățile utilizatorilor.')

@section('content')
    <div class="max-w-5xl mx-auto">
        <header class="mb-6 md:mb-8">
            <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
                Termeni și condiții
            </h1>
            <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl">
                Prin folosirea iaAuto.ro accepți regulile de mai jos. Textul este scris cât mai clar, pentru utilizatori
                care publică sau consultă anunțuri auto.
            </p>
            <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500">
                Acest text are rol informativ și nu înlocuiește consultanța juridică.
            </p>
        </header>

        <section class="space-y-4 md:space-y-5 mb-8">
            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    1. Rolul platformei
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    iaAuto.ro este o platformă de anunțuri auto. Scopul ei este să pună în legătură persoane care vor
                    să cumpere o mașină cu proprietari sau dealeri care publică anunțuri.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2">
                    Platforma nu este parte în tranzacția dintre cumpărător și vânzător și nu garantează starea tehnică,
                    istoricul, prețul sau comportamentul părților implicate.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    2. Contul și datele introduse
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Ești responsabil de corectitudinea datelor introduse în cont și în anunțuri. Nu folosi identitatea
                    altei persoane și nu publica informații false sau înșelătoare.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    3. Publicarea anunțurilor
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Anunțurile trebuie să descrie mașini reale și să includă informații cât mai corecte despre marcă,
                    model, an, kilometraj, stare, dotări, preț și localizare. Imaginile nu trebuie să inducă în eroare.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2">
                    Ne rezervăm dreptul de a șterge sau dezactiva anunțuri care încalcă legea, bunul-simț sau regulile
                    platformei.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    4. Răspundere și limitări
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    iaAuto.ro nu poate fi tras la răspundere pentru probleme tehnice ale vehiculului, neînțelegeri între
                    cumpărător și vânzător, plăți, documente incomplete sau pierderi rezultate în urma tranzacțiilor.
                </p>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mt-2">
                    Recomandăm verificarea mașinii, a documentelor, a istoricului și a vânzătorului înainte de orice plată.
                </p>
            </article>

            <article class="bg-white dark:bg-[#18181B] rounded-xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
                <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                    5. Modificarea termenilor
                </h2>
                <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                    Termenii pot fi actualizați periodic, în funcție de evoluția platformei sau de schimbările legislative.
                    Continuarea utilizării iaAuto.ro după actualizare înseamnă acceptarea versiunii noi.
                </p>
            </article>
        </section>

        <section class="bg-[#fff4f5] dark:bg-[#2a1013] border border-red-100 dark:border-red-900/40 rounded-xl p-4 md:p-5">
            <h2 class="text-sm md:text-base font-bold text-[#8f111a] dark:text-red-100 mb-2">
                Folosește platforma responsabil
            </h2>
            <p class="text-xs md:text-sm text-[#7f1d1d] dark:text-red-100/90 leading-relaxed">
                Un anunț bun, date corecte și o verificare atentă înainte de cumpărare fac experiența mai sigură pentru
                toți utilizatorii iaAuto.ro.
            </p>
        </section>
    </div>
@endsection
