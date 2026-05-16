@extends('layouts.app')

@section('meta_title', 'De ce dealerii auto aleg iaAuto.ro – anunțuri gratuite pentru parcuri auto')
@section('meta_description', 'Dealer auto sau parc auto? Pe iaAuto.ro poți publica gratuit mașinile, primești pagină dedicată pentru parc și îți administrezi simplu anunțurile.')
@section('meta_image', asset('images/social-share.webp'))

@section('content')
@php
    $registerUrl = route('register');
    $createUrl = route('services.create');
    $assetBase = 'images/landing/dealeri/';
    $heroMockup = asset($assetBase.'dealer-hero-mockup.png');
    $previewPage = asset($assetBase.'dealer-preview-page.png');
    $bmwCard = asset($assetBase.'dealer-bmw-card.png');
    $audiCard = asset($assetBase.'dealer-audi-card.png');
    $stockLink = asset($assetBase.'dealer-stock-link.png');
@endphp

<div class="dealer-landing-page mx-auto space-y-12 pb-10 pt-2 md:space-y-14 md:pb-14">
    <section class="dealer-landing-hero grid min-w-0 grid-cols-1 items-center gap-8 lg:grid-cols-2">
        <div class="dealer-landing-hero-copy min-w-0">
            <p class="mb-4 inline-flex rounded-full border border-red-100 bg-white px-4 py-2 text-xs font-black uppercase text-[#C81424] shadow-sm dark:border-red-900/40 dark:bg-[#18181b] dark:text-red-200">
                Pentru dealeri și parcuri auto
            </p>

            <h1 class="dealer-landing-page__title break-words font-black text-gray-950 dark:text-white">
                Ai parc auto? Publică gratuit toate mașinile pe iaAuto.ro
            </h1>

            <p class="mt-5 max-w-xl text-base leading-8 text-gray-600 dark:text-gray-300 sm:text-lg">
                Primești o pagină dedicată pentru parcul tău, anunțuri gratuite și instrumente simple pentru administrare.
            </p>

            <div class="dealer-landing-badges mt-6 grid grid-cols-1 gap-3 sm:grid-cols-3">
                @foreach(['Anunțuri gratuite', 'Pagină dedicată', 'Administrare simplă'] as $badge)
                    <div class="dealer-landing-badge inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm font-bold text-gray-800 shadow-sm dark:border-[#333] dark:bg-[#18181b] dark:text-gray-100">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-lg bg-red-50 text-[#C81424] dark:bg-red-950/40 dark:text-red-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        <span>{{ $badge }}</span>
                    </div>
                @endforeach
            </div>

            <div class="mt-7 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $registerUrl }}" class="inline-flex items-center justify-center rounded-xl bg-[#C81424] px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98]">
                    Creează cont gratuit
                </a>

                <a href="{{ $createUrl }}" class="inline-flex items-center justify-center rounded-xl border border-gray-300 bg-white px-6 py-3.5 text-sm font-black text-gray-900 shadow-sm transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#18181b] dark:text-gray-100 dark:hover:border-red-500 dark:hover:text-red-200">
                    Publică primul anunț
                </a>
            </div>
        </div>

        <div class="min-w-0">
            <img
                src="{{ $heroMockup }}"
                alt="Mockup pagină dedicată AUTO PARK DEMO"
                class="dealer-landing-asset dealer-landing-mobile-full dealer-landing-hero-asset h-auto w-full object-contain"
                width="1472"
                height="1056"
                decoding="async"
                fetchpriority="high"
            >
        </div>
    </section>

    <section>
        <div class="mb-6 text-center">
            <h2 class="text-3xl font-black text-gray-950 dark:text-white">
                Ce primești gratuit?
            </h2>
        </div>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-3">
            <article class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-[#333] dark:bg-[#18181b] md:p-7">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-[#C81424] dark:bg-red-950/30 dark:text-red-200">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20 12l-8 8-8-8V4h8l8 8z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 8h.01" />
                    </svg>
                </div>

                <h3 class="text-lg font-black text-gray-950 dark:text-white">
                    Anunțuri auto gratuite
                </h3>

                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                    Publici mașinile din parc fără taxă pe anunț și fără cost de intrare.
                </p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-[#333] dark:bg-[#18181b] md:p-7">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-[#C81424] dark:bg-red-950/30 dark:text-red-200">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 5h16v14H4z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 9h16" />
                    </svg>
                </div>

                <h3 class="text-lg font-black text-gray-950 dark:text-white">
                    Pagină dedicată pentru parc
                </h3>

                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                    Primești o pagină publică unde clienții văd datele parcului și toate mașinile active.
                </p>
            </article>

            <article class="rounded-2xl border border-gray-200 bg-white p-6 text-center shadow-sm dark:border-[#333] dark:bg-[#18181b] md:p-7">
                <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-2xl bg-red-50 text-[#C81424] dark:bg-red-950/30 dark:text-red-200">
                    <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.9" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.4-6.4l-2.1 2.1M7.7 16.3l-2.1 2.1m12.8 0l-2.1-2.1M7.7 7.7L5.6 5.6" />
                        <circle cx="12" cy="12" r="4" />
                    </svg>
                </div>

                <h3 class="text-lg font-black text-gray-950 dark:text-white">
                    Administrare simplă
                </h3>

                <p class="mt-2 text-sm leading-6 text-gray-600 dark:text-gray-300">
                    Adaugi, editezi, reactualizezi și distribui anunțurile direct din contul tău.
                </p>
            </article>
        </div>
    </section>

    <section class="grid min-w-0 grid-cols-1 items-center gap-6 md:gap-8 lg:grid-cols-2">
        <div class="min-w-0">
            <h2 class="text-2xl font-black leading-tight text-gray-950 dark:text-white">
                Anunțurile arată clar și ușor de parcurs
            </h2>

            <p class="mt-3 text-sm leading-7 text-gray-600 dark:text-gray-300">
                Fiecare mașină poate fi prezentată cu imagine, preț, detalii importante și buton de acces rapid.
            </p>
        </div>

        <div class="dealer-landing-car-grid grid min-w-0 grid-cols-1 gap-4 sm:grid-cols-2">
            <img
                src="{{ $bmwCard }}"
                alt="Card anunț demo BMW Seria 3 320d"
                class="dealer-landing-asset dealer-landing-mobile-full dealer-landing-car-asset h-auto w-full object-contain"
                width="1472"
                height="1056"
                loading="lazy"
                decoding="async"
            >

            <img
                src="{{ $audiCard }}"
                alt="Card anunț demo Audi A4 2.0 TDI"
                class="dealer-landing-asset dealer-landing-mobile-full dealer-landing-car-asset h-auto w-full object-contain"
                width="1472"
                height="1056"
                loading="lazy"
                decoding="async"
            >
        </div>
    </section>

    <section class="dealer-landing-preview grid min-w-0 grid-cols-1 items-center gap-8 lg:grid-cols-2">
        <div class="min-w-0">
            <h2 class="text-3xl font-black leading-tight text-gray-950 dark:text-white sm:text-4xl">
                Așa poate arăta pagina parcului tău
            </h2>

            <p class="mt-4 max-w-lg text-base leading-8 text-gray-600 dark:text-gray-300">
                Clienții pot vedea rapid informațiile esențiale despre parc și toate mașinile disponibile.
            </p>

            <ul class="mt-6 space-y-4 text-sm font-bold text-gray-700 dark:text-gray-200">
                @foreach(['imagine de prezentare și galerie foto', 'anunțurile tale evidențiate clar', 'informații de contact și localizare'] as $item)
                    <li class="flex items-center gap-3">
                        <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-red-50 text-[#C81424] dark:bg-red-950/30 dark:text-red-200">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.4" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                            </svg>
                        </span>
                        {{ $item }}
                    </li>
                @endforeach
            </ul>
        </div>

        <div class="min-w-0">
            <img
                src="{{ $previewPage }}"
                alt="Preview pagină parc auto AUTO PARK DEMO cu anunțuri"
                class="dealer-landing-asset dealer-landing-mobile-full h-auto w-full object-contain"
                width="1472"
                height="1056"
                loading="lazy"
                decoding="async"
            >
        </div>
    </section>

    <section>
        <h2 class="mb-6 text-center text-3xl font-black text-gray-950 dark:text-white">
            Cum începi?
        </h2>

        <div class="rounded-3xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#18181b] md:p-7">
            <div class="grid gap-8 md:grid-cols-[1fr_auto_1fr_auto_1fr] md:items-start">
                @foreach([
                    ['1', 'Creezi cont gratuit', 'Îți faci cont în câteva minute, fără costuri.'],
                    ['2', 'Completezi datele parcului', 'Adaugi informațiile despre parc și datele de contact.'],
                    ['3', 'Publici mașinile', 'Adaugi mașinile din parc și le publici gratuit.'],
                ] as $index => $step)
                    <div class="text-center">
                        <div class="mx-auto mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-[#C81424] text-lg font-black text-white shadow-lg shadow-red-700/20">
                            {{ $step[0] }}
                        </div>

                        <h3 class="font-black text-gray-950 dark:text-white">
                            {{ $step[1] }}
                        </h3>

                        <p class="mx-auto mt-2 max-w-xs text-sm leading-6 text-gray-600 dark:text-gray-300">
                            {{ $step[2] }}
                        </p>
                    </div>

                    @if($index < 2)
                        <div class="hidden pt-4 text-3xl font-light text-gray-300 md:block" aria-hidden="true">
                            →
                        </div>
                    @endif
                @endforeach
            </div>
        </div>
    </section>

<section class="dealer-landing-final-cta overflow-hidden rounded-3xl border border-red-100 bg-[#fff4f5] p-5 shadow-sm dark:border-red-900/30 dark:bg-[#220d10] md:p-6">
    <div class="dealer-landing-cta-grid grid grid-cols-1 items-center gap-5 lg:grid-cols-2 lg:gap-6">
        <div>
            <h2 class="text-3xl font-black leading-tight text-gray-950 dark:text-white">
                Testează iaAuto.ro cu câteva mașini din parc
            </h2>

            <p class="mt-3 max-w-2xl text-base leading-8 text-gray-700 dark:text-red-100">
                Nu trebuie să muți tot stocul din prima. Adaugă 3–5 mașini și vezi cum arată pagina ta.
            </p>

            <p class="mt-4 max-w-2xl rounded-2xl border border-red-100 bg-white/70 px-4 py-3 text-sm font-bold leading-6 text-gray-800 shadow-sm dark:border-red-900/40 dark:bg-[#18181b]/70 dark:text-red-100">
                După ce prinzi gustul, contactează-ne și automatizăm noi totul:
                <a href="mailto:contact@iaauto.ro" class="font-black text-[#C81424] underline decoration-red-300 underline-offset-4 hover:text-[#94111B] dark:text-red-200">
                    contact@iaauto.ro
                </a>
            </p>

            <div class="mt-6 flex flex-col gap-3 sm:flex-row">
                <a href="{{ $registerUrl }}" class="inline-flex items-center justify-center rounded-xl bg-[#C81424] px-6 py-3.5 text-sm font-black text-white shadow-lg shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98]">
                    Creează cont gratuit
                </a>

                <a href="{{ $createUrl }}" class="inline-flex items-center justify-center rounded-xl border border-red-200 bg-white px-6 py-3.5 text-sm font-black text-gray-900 shadow-sm transition hover:border-[#C81424] hover:text-[#C81424] dark:border-red-900/50 dark:bg-[#18181b] dark:text-gray-100">
                    Publică primul anunț
                </a>
            </div>
        </div>

        <div class="dealer-landing-stock-wrap">
            <img
                src="{{ $stockLink }}"
                alt="Un singur link pentru tot stocul tău"
                class="dealer-landing-stock-image"
                width="1472"
                height="1056"
                loading="lazy"
                decoding="async"
            >
        </div>
    </div>
</section>
</div>

<style>
    .dealer-landing-page {
        width: 100%;
        max-width: min(80rem, calc(100vw - 2rem));
        overflow-x: clip;
    }

    .dealer-landing-page__title {
        font-size: 1.6rem;
        line-height: 1.12;
        letter-spacing: 0;
        max-width: calc(100vw - 2rem);
        overflow-wrap: break-word;
        white-space: normal;
    }

    .dealer-landing-hero-copy {
        max-width: min(39rem, calc(100vw - 2rem));
        width: min(39rem, calc(100vw - 2rem));
    }

    .dealer-landing-asset {
        display: block;
        height: auto;
        max-width: 100%;
        width: 100%;
    }

    .dealer-landing-stock-wrap {
        aspect-ratio: 16 / 9;
        justify-self: center;
        max-width: 34rem;
        overflow: hidden;
        position: relative;
        width: 100%;
    }

    .dealer-landing-stock-image {
        display: block;
        height: 100%;
        inset: 0;
        max-width: 100%;
        object-fit: contain;
        object-position: center;
        position: absolute;
        width: 100%;
    }

    @media (max-width: 640px) {
        .dealer-landing-page {
            width: 100% !important;
            max-width: 100% !important;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            margin-left: auto !important;
            margin-right: auto !important;
        }

        .dealer-landing-hero-copy {
            width: 100%;
            max-width: 100%;
        }

        .dealer-landing-page__title {
            font-size: 1.7rem;
            line-height: 1.08;
            max-width: 100%;
        }

        .dealer-landing-badges {
            max-width: 100%;
        }

        .dealer-landing-badge {
            width: 100%;
        }

        .dealer-landing-mobile-full {
            width: calc(100vw - 1.5rem);
            max-width: none;
            margin-left: 50%;
            transform: translateX(-50%);
        }

        .dealer-landing-car-grid {
            gap: 1.5rem;
        }

        .dealer-landing-car-asset {
            width: calc(100vw - 1.5rem);
            max-width: none;
            margin-left: 50%;
            transform: translateX(-50%);
        }

        .dealer-landing-final-cta {
            padding: 1rem;
            border-radius: 1.5rem;
        }

        .dealer-landing-cta-grid {
            gap: 1.25rem;
        }

        .dealer-landing-stock-wrap {
            aspect-ratio: 16 / 9;
            width: 100%;
            max-width: 100%;
            margin-left: 0;
            transform: none;
            overflow: hidden;
            justify-self: center;
        }

        .dealer-landing-stock-image {
            position: absolute;
            inset: 0;
            display: block;
            width: 100%;
            height: 100%;
            max-width: 100%;
            object-fit: contain;
            object-position: center;
        }
    }

    @media (min-width: 480px) {
        .dealer-landing-page__title {
            font-size: 2rem;
        }
    }

    @media (min-width: 640px) {
        .dealer-landing-page__title {
            font-size: 2.25rem;
        }
    }

    @media (min-width: 1280px) {
        .dealer-landing-page__title {
            font-size: 3rem;
        }
    }

    @media (min-width: 1024px) {
        .dealer-landing-hero {
            grid-template-columns: minmax(0, 0.86fr) minmax(0, 1.14fr);
        }

        .dealer-landing-hero-asset {
            margin-left: -5%;
            max-width: none;
            width: 110%;
        }

        .dealer-landing-preview {
            grid-template-columns: minmax(0, 0.72fr) minmax(0, 1.28fr);
        }

        .dealer-landing-cta-grid {
            grid-template-columns: minmax(0, 1fr) minmax(0, 0.86fr);
        }

        .dealer-landing-stock-wrap {
            justify-self: end;
            max-width: 36rem;
            width: 100%;
        }

        .dealer-landing-stock-image {
            width: 100%;
            max-width: 100%;
        }
    }
</style>
@endsection