@extends('layouts.app')

@section('title', 'Politica de cookies')
@section('meta_title', 'Politica de cookies - iaAuto.ro')
@section('meta_description', 'Află ce cookie-uri folosește iaAuto.ro, de ce sunt necesare și cum poți gestiona preferințele pentru analytics și marketing.')

@section('content')
<div class="max-w-5xl mx-auto">

    <header class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
            Politica de cookies
        </h1>

        <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl text-justify">
            Această pagină explică modul în care iaAuto.ro folosește cookie-uri și tehnologii similare pentru funcționarea site-ului,
            pentru analiză și, doar cu acordul tău, pentru activități de marketing sau măsurare a campaniilor.
        </p>

        <p class="mt-2 text-[11px] text-gray-400 dark:text-gray-500 text-justify">
            Ultima actualizare: 11.05.2026
        </p>
    </header>

    <section class="space-y-4 md:space-y-5 mb-8">

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                1. Ce sunt cookie-urile
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Cookie-urile sunt fișiere mici sau informații salvate în browserul tău atunci când vizitezi un site. Ele pot ajuta site-ul să funcționeze corect,
                să păstreze o sesiune de autentificare, să rețină preferințe sau să ofere informații statistice despre modul în care este folosită platforma.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pe iaAuto.ro folosim cookie-uri strict necesare pentru funcționare și, doar dacă îți dai acordul, cookie-uri opționale pentru analytics sau marketing.
                Refuzarea cookie-urilor opționale nu blochează folosirea site-ului.
            </p>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                2. Tipuri de cookie-uri folosite
            </h2>

            <div class="space-y-3 text-sm text-gray-700 dark:text-gray-300 leading-relaxed">
                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Cookie-uri necesare</h3>
                    <p class="mt-1 text-justify">
                        Sunt active mereu și sunt folosite pentru funcționarea site-ului, securitate, sesiune, autentificare, protecție CSRF,
                        salvarea preferinței privind cookie-urile și alte funcționalități de bază. Acestea nu pot fi dezactivate din panoul de setări.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Cookie-uri de analytics</h3>
                    <p class="mt-1 text-justify">
                        Ne pot ajuta să înțelegem cum este folosit site-ul, ce pagini sunt vizitate și unde putem îmbunătăți experiența. Acestea se încarcă doar dacă alegi
                        să permiți categoria Analytics.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Cookie-uri de marketing</h3>
                    <p class="mt-1 text-justify">
                        Pot fi folosite ulterior pentru măsurarea campaniilor sau integrarea unor instrumente precum Google Ads ori Facebook Pixel. Acestea se încarcă doar
                        dacă alegi să permiți categoria Marketing.
                    </p>
                </div>
            </div>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                3. Consimțământ și preferințe
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                La prima vizită, afișăm un banner discret prin care poți accepta toate cookie-urile, refuza cookie-urile opționale sau personaliza preferințele.
                Alegerea ta este salvată local în browser, sub cheia <strong>iaauto_cookie_consent</strong>, pentru a nu afișa bannerul la fiecare vizită.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Poți modifica oricând preferințele folosind butonul <strong>Setări cookies</strong> din footer.
            </p>

            <button type="button"
                    onclick="window.openCookieSettings && window.openCookieSettings()"
                    class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#C81424] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#a6101d]">
                Deschide setările cookies
            </button>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                4. Exemple de tehnologii care pot fi folosite
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                În prezent, site-ul este pregătit să încarce instrumente de analytics sau marketing doar după consimțământ. În funcție de configurarea activă,
                pot fi folosite servicii precum Google Analytics, Google Ads sau Facebook Pixel, dar numai pentru categoriile pe care le-ai acceptat.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Dacă aceste servicii sunt activate, ele pot primi informații tehnice precum pagina vizitată, browserul, dispozitivul, interacțiuni de bază sau identificatori
                generați de serviciile respective, conform propriilor politici.
            </p>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                5. Administrarea cookie-urilor din browser
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pe lângă setările oferite pe site, poți șterge sau bloca cookie-uri direct din browser. Reține că blocarea cookie-urilor strict necesare poate afecta
                autentificarea, publicarea anunțurilor, mesajele sau alte funcționalități importante ale platformei.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pentru detalii despre datele personale și drepturile tale, consultă și
                <a href="{{ route('page.privacy') }}" class="text-[#C81424] hover:underline">
                    Politica de confidențialitate
                </a>.
            </p>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                6. Contact
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pentru întrebări despre această politică sau despre modul în care folosim cookie-urile, ne poți contacta la
                <a href="mailto:contact@iaauto.ro" class="text-[#C81424] hover:underline">
                    contact@iaauto.ro
                </a>.
            </p>
        </article>

    </section>
</div>
@endsection
