@extends('layouts.app')

@section('title', 'Politica de cookies')
@section('meta_title', 'Politica de cookies - iaAuto.ro')
@section('meta_description', 'Afla ce cookie-uri foloseste iaAuto.ro, de ce sunt necesare si cum poti gestiona preferintele pentru analytics si marketing.')

@section('content')
<div class="max-w-5xl mx-auto">

    <header class="mb-6 md:mb-8">
        <h1 class="text-2xl md:text-3xl lg:text-4xl font-extrabold text-gray-900 dark:text-gray-100 mb-2">
            Politica de cookies
        </h1>

        <p class="text-sm md:text-base text-gray-600 dark:text-gray-300 max-w-3xl text-justify">
            Aceasta pagina explica modul in care iaAuto.ro foloseste cookie-uri si tehnologii similare pentru functionarea site-ului,
            pentru analiza si, doar cu acordul tau, pentru activitati de marketing sau masurare a campaniilor.
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
                Cookie-urile sunt fisiere mici sau informatii salvate in browserul tau atunci cand vizitezi un site. Ele pot ajuta site-ul sa functioneze corect,
                sa pastreze o sesiune de autentificare, sa retina preferinte sau sa ofere informatii statistice despre modul in care este folosita platforma.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pe iaAuto.ro folosim cookie-uri strict necesare pentru functionare si, doar daca iti dai acordul, cookie-uri optionale pentru analytics sau marketing.
                Refuzarea cookie-urilor optionale nu blocheaza folosirea site-ului.
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
                        Sunt active mereu si sunt folosite pentru functionarea site-ului, securitate, sesiune, autentificare, protectie CSRF,
                        salvarea preferintei privind cookie-urile si alte functionalitati de baza. Acestea nu pot fi dezactivate din panoul de setari.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Cookie-uri de analytics</h3>
                    <p class="mt-1 text-justify">
                        Ne pot ajuta sa intelegem cum este folosit site-ul, ce pagini sunt vizitate si unde putem imbunatati experienta. Acestea se incarca doar daca alegi
                        sa permiti categoria Analytics.
                    </p>
                </div>

                <div class="rounded-xl border border-gray-100 dark:border-gray-800 p-3">
                    <h3 class="font-bold text-gray-900 dark:text-gray-100">Cookie-uri de marketing</h3>
                    <p class="mt-1 text-justify">
                        Pot fi folosite ulterior pentru masurarea campaniilor sau integrarea unor instrumente precum Google Ads ori Facebook Pixel. Acestea se incarca doar
                        daca alegi sa permiti categoria Marketing.
                    </p>
                </div>
            </div>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                3. Consimtamant si preferinte
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                La prima vizita, afisam un banner discret prin care poti accepta toate cookie-urile, refuza cookie-urile optionale sau personaliza preferintele.
                Alegerea ta este salvata local in browser, sub cheia <strong>iaauto_cookie_consent</strong>, pentru a nu afisa bannerul la fiecare vizita.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Poti modifica oricand preferintele folosind butonul <strong>Setari cookies</strong> din footer.
            </p>

            <button type="button"
                    onclick="window.openCookieSettings && window.openCookieSettings()"
                    class="mt-4 inline-flex items-center justify-center rounded-xl bg-[#C81424] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#a6101d]">
                Deschide setarile cookies
            </button>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                4. Exemple de tehnologii care pot fi folosite
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                In prezent, site-ul este pregatit sa incarce instrumente de analytics sau marketing doar dupa consimtamant. In functie de configurarea activa,
                pot fi folosite servicii precum Google Analytics, Google Ads sau Facebook Pixel, dar numai pentru categoriile pe care le-ai acceptat.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Daca aceste servicii sunt activate, ele pot primi informatii tehnice precum pagina vizitata, browserul, dispozitivul, interactiuni de baza sau identificatori
                generati de serviciile respective, conform propriilor politici.
            </p>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                5. Administrarea cookie-urilor din browser
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pe langa setarile oferite pe site, poti sterge sau bloca cookie-uri direct din browser. Retine ca blocarea cookie-urilor strict necesare poate afecta
                autentificarea, publicarea anunturilor, mesajele sau alte functionalitati importante ale platformei.
            </p>

            <p class="mt-2 text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pentru detalii despre datele personale si drepturile tale, consulta si
                <a href="{{ route('page.privacy') }}" class="text-[#C81424] hover:underline">
                    Politica de confidentialitate
                </a>.
            </p>
        </article>

        <article class="bg-white dark:bg-[#18181B] rounded-2xl shadow-sm border border-gray-100 dark:border-gray-800 p-4 md:p-5">
            <h2 class="text-base md:text-lg font-bold text-gray-900 dark:text-gray-100 mb-2">
                6. Contact
            </h2>

            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed text-justify">
                Pentru intrebari despre aceasta politica sau despre modul in care folosim cookie-urile, ne poti contacta la
                <a href="mailto:contact@iaauto.ro" class="text-[#C81424] hover:underline">
                    contact@iaauto.ro
                </a>.
            </p>
        </article>

    </section>
</div>
@endsection
