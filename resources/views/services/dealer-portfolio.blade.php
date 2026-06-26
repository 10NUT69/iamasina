@extends('layouts.app')

@php
    $cleanDealerSeoValue = static function ($value) {
        $value = trim((string) $value);

        if ($value === '') {
            return null;
        }

        return trim(preg_replace('/\s+/', ' ', $value));
    };

    $dealerDisplayName = $cleanDealerSeoValue($dealer->company_name ?: $dealer->name) ?: 'Parc auto';
    $dealerSeoCity = $cleanDealerSeoValue($dealer->city);
    $dealerSeoCounty = $cleanDealerSeoValue($dealer->county);
    $dealerSeoLocation = trim(implode(', ', array_filter([$dealerSeoCity, $dealerSeoCounty])));
    $activeCount = isset($totalCount)
        ? (int) $totalCount
        : (isset($services) && method_exists($services, 'count') ? (int) $services->count() : 0);
    $activeCountSeoLabel = number_format($activeCount, 0, ',', '.') . ' ' . ($activeCount === 1 ? 'anunț auto' : 'anunțuri auto');

    $dealerSeoTitle = $dealerDisplayName . ' - anunțuri auto';
    if ($dealerSeoLocation !== '') {
        $dealerSeoTitle .= ' din ' . $dealerSeoLocation;
    }

    $dealerSeoDescription = 'Vezi ' . $activeCountSeoLabel . ' de la ' . $dealerDisplayName . ', parc auto';
    $dealerSeoDescription .= $dealerSeoLocation !== '' ? ' din ' . $dealerSeoLocation : '';
    $dealerSeoDescription .= '. Mașini disponibile, prețuri, fotografii și contact pe iaAuto.ro.';

    $galleryUrls = $dealer->dealer_gallery_urls;
    $firstDealerGalleryUrl = count($galleryUrls) ? $galleryUrls[0] : null;
    $firstDealerService = isset($services) && method_exists($services, 'first') ? $services->first() : null;
    $dealerSeoImage = $firstDealerGalleryUrl ?: ($firstDealerService?->main_image_url ?: asset('images/social-share.webp'));
@endphp

@section('title', $dealerSeoTitle)
@section('meta_title', e($dealerSeoTitle))
@section('meta_description', e($dealerSeoDescription))
@section('meta_image', $dealerSeoImage)

@section('content')
@php
    $firstGalleryUrl = count($galleryUrls) ? $galleryUrls[0] : null;
    $phones = collect([$dealer->phone, $dealer->phone_2, $dealer->phone_3])->filter()->values();
    $primaryPhone = $phones->first();
    $formatPhoneDisplay = function ($phone) {
        $phone = trim((string) $phone);
        $compact = preg_replace('/\s+/', '', $phone);
        $digits = preg_replace('/\D+/', '', $compact);

        if (strlen($digits) === 10 && str_starts_with($digits, '0')) {
            return substr($digits, 0, 4) . ' ' . substr($digits, 4, 3) . ' ' . substr($digits, 7, 3);
        }

        if (strlen($digits) === 12 && str_starts_with($digits, '40')) {
            return '+40 ' . substr($digits, 2, 3) . ' ' . substr($digits, 5, 3) . ' ' . substr($digits, 8, 3);
        }

        return $phone;
    };
    $phoneItems = $phones->map(fn ($phone) => [
        'href' => preg_replace('/\s+/', '', $phone),
        'label' => $formatPhoneDisplay($phone),
    ])->values();
    $primaryPhoneHref = $phoneItems->first()['href'] ?? null;
    $dealerUrl = $dealer->dealer_public_url ?: url()->current();
    $dealerSubtitle = 'Parc auto';

    if ($dealer->county && $dealer->city) {
        $dealerSubtitle = 'Parc auto în județul ' . $dealer->county . ', ' . $dealer->city;
    } elseif ($dealer->county) {
        $dealerSubtitle = 'Parc auto în județul ' . $dealer->county;
    } elseif ($dealer->city) {
        $dealerSubtitle = 'Parc auto în ' . $dealer->city;
    }

    $mapsQuery = trim(implode(', ', array_filter([$dealer->address, $dealer->city, $dealer->county])));
    $mapsUrl = $mapsQuery ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapsQuery) : null;
    $modelSelectDisabled = ! $selectedBrandId || $models->isEmpty();
    $activeCountLabel = number_format($activeCount, 0, ',', '.') . ' ' . ($activeCount === 1 ? 'anunț activ' : 'anunțuri active');
    $stockHeadingLabel = number_format($totalCount, 0, ',', '.') . ' ' . ($totalCount === 1 ? 'anunț auto oferit de ' : 'anunțuri auto oferite de ') . $dealerDisplayName;
    $galleryPhotoLabel = count($galleryUrls) === 1 ? '1 fotografie' : count($galleryUrls) . ' fotografii';
    $galleryThumbUrls = array_slice($galleryUrls, 1, 3);
    $dealerPlaceLine = trim(implode(', ', array_filter([$dealer->city, $dealer->county])));
    $dealerAddressDisplay = trim(implode(', ', array_filter([$dealer->address, $dealer->city, $dealer->county])));
    $dealerDescriptionLimit = 3000;
    $dealerDescriptionRaw = trim((string) $dealer->dealer_description);
    $dealerDescriptionDisplay = \Illuminate\Support\Str::limit($dealerDescriptionRaw, $dealerDescriptionLimit, '');
    $hasDealerDescription = $dealerDescriptionDisplay !== '';
    $dealerCountySlug = $dealer->county ? \Illuminate\Support\Str::slug($dealer->county) : null;
    $dealerBreadcrumbItems = [
        ['name' => 'Acasă', 'item' => route('services.index')],
        ['name' => 'Dealeri auto', 'item' => route('cars.index', ['seller_type' => 'dealer'])],
    ];

    if ($dealer->county) {
        $dealerBreadcrumbItems[] = [
            'name' => $dealer->county,
            'item' => $dealerCountySlug
                ? route('brand.index', ['segment1' => $dealerCountySlug, 'seller_type' => 'dealer'])
                : ($dealer->county_id ? route('cars.index', ['seller_type' => 'dealer', 'county_id' => $dealer->county_id]) : null),
        ];
    }

    $dealerBreadcrumbItems[] = ['name' => $dealerDisplayName, 'item' => $dealerUrl];
@endphp

<div class="w-full min-w-0 space-y-4 overflow-hidden pb-24 pt-2.5 lg:pb-10">
    <x-breadcrumbs :items="$dealerBreadcrumbItems" />

    <section class="grid min-w-0 items-stretch gap-4 xl:grid-cols-[minmax(440px,0.8fr)_minmax(0,1.2fr)]">
        <div class="min-w-0 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#303033] dark:bg-[#18181B] sm:p-5 lg:p-5">
            <div class="flex h-full min-w-0 flex-col">
                <h1 class="break-words text-3xl font-black tracking-tight text-gray-950 dark:text-white sm:text-4xl xl:text-4xl">
                    {{ $dealerDisplayName }}
                </h1>

                <p class="mt-1.5 text-sm font-semibold text-gray-600 dark:text-gray-300 sm:text-base">
                    {{ $dealerSubtitle }}
                </p>

                <div class="mt-3">
                    <span class="inline-flex items-center rounded-lg bg-green-100 px-3.5 py-1.5 text-sm font-black text-green-800 dark:bg-green-500/15 dark:text-green-300">
                        {{ $activeCountLabel }}
                    </span>
                </div>

                <div class="mt-4 space-y-3 text-sm font-bold text-gray-800 dark:text-gray-100">
                    @foreach($phoneItems as $phone)
                        <a href="tel:{{ $phone['href'] }}" class="flex min-w-0 items-center gap-3 transition hover:text-[#C81424] dark:hover:text-red-300">
                            <svg class="h-5 w-5 shrink-0 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                            </svg>
                            <span class="truncate">{{ $phone['label'] }}</span>
                        </a>
                    @endforeach

                    @if($dealerAddressDisplay)
                        <div class="flex min-w-0 items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 21s7-4.438 7-11a7 7 0 10-14 0c0 6.562 7 11 7 11z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 10.5a2 2 0 100-4 2 2 0 000 4z"/>
                            </svg>
                            <span class="min-w-0 break-words">{{ $dealerAddressDisplay }}</span>
                        </div>
                    @endif

                    <div class="flex min-w-0 items-start gap-3">
                        <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 6v6l4 2"/>
                            <circle cx="12" cy="12" r="9" stroke-width="1.9"/>
                        </svg>
                        <span>Program: contactează dealerul</span>
                    </div>
                </div>

                <div @class([
                    'mt-5 grid gap-3 sm:flex sm:flex-row',
                    'grid-cols-2' => $primaryPhone,
                    'grid-cols-1' => ! $primaryPhone,
                    'lg:mt-auto lg:pt-4',
                ])>
                    <a href="#dealer-listings" class="inline-flex h-11 min-w-0 items-center justify-center gap-2 rounded-lg border border-[#C81424] bg-white px-4 text-sm font-black text-[#C81424] shadow-sm transition hover:bg-[#C81424] hover:text-white active:scale-[0.98] dark:bg-transparent dark:hover:bg-[#C81424] sm:flex-1">
                        <span>Vezi anunțurile</span>
                        <span aria-hidden="true">&rarr;</span>
                    </a>

                    @if($primaryPhone)
                        <a href="tel:{{ $primaryPhoneHref }}" class="inline-flex h-11 min-w-0 items-center justify-center gap-2 rounded-lg bg-[#C81424] px-4 text-sm font-black text-white shadow-sm shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98] sm:flex-1">
                            <svg class="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.97 1.293a1.125 1.125 0 01-1.21.38 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.38-1.21l1.293-.97a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                            </svg>
                            Sună acum
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <div class="relative min-w-0 overflow-hidden rounded-xl border border-gray-200 bg-white p-2 shadow-sm dark:border-[#303033] dark:bg-[#18181B] lg:h-[320px]">
            @if(count($galleryUrls))
                <div @class([
                    'grid gap-2',
                    'lg:h-full lg:min-h-0' => true,
                    'lg:grid-rows-[minmax(0,1fr)]' => true,
                    'lg:grid-cols-[minmax(0,1fr)_190px]' => count($galleryUrls) > 1,
                    'lg:grid-cols-1' => count($galleryUrls) <= 1,
                ])>
                    <div id="dealerMobileGalleryTouchArea" class="relative min-h-[260px] overflow-hidden rounded-lg bg-gray-200 sm:min-h-[340px] lg:h-full lg:min-h-0 dark:bg-[#202024]">
                        <img id="dealerMobileGalleryImage" src="{{ $firstGalleryUrl }}" alt="{{ $dealerDisplayName }} imagine 1" class="absolute inset-0 h-full w-full object-cover object-center">
                        <button type="button" onclick="openDealerMainGallery()" class="absolute inset-0 z-20 cursor-zoom-in rounded-lg focus:outline-none focus-visible:ring-2 focus-visible:ring-white/80" aria-label="Deschide galeria {{ $dealerDisplayName }}"></button>

                        <div class="absolute inset-x-0 bottom-0 z-10 h-28 bg-gradient-to-t from-black/55 via-black/15 to-transparent pointer-events-none"></div>

                        @if(count($galleryUrls) > 1)
                            <button type="button" onclick="changeDealerMobileGalleryImage(-1)" class="absolute left-3 top-1/2 z-30 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/45 text-white shadow-lg backdrop-blur transition hover:bg-[#C81424] lg:hidden" aria-label="Imaginea anterioară">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                                </svg>
                            </button>

                            <button type="button" onclick="changeDealerMobileGalleryImage(1)" class="absolute right-3 top-1/2 z-30 flex h-10 w-10 -translate-y-1/2 items-center justify-center rounded-full bg-black/45 text-white shadow-lg backdrop-blur transition hover:bg-[#C81424] lg:hidden" aria-label="Imaginea următoare">
                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                                </svg>
                            </button>

                            <div id="dealerMobileGalleryCounter" class="absolute bottom-4 left-4 z-30 rounded-lg bg-black/65 px-3 py-1.5 text-xs font-black text-white shadow-lg backdrop-blur lg:hidden">
                                1 / {{ count($galleryUrls) }}
                            </div>
                        @endif

                        <button type="button" onclick="openDealerGallery(0)" class="absolute bottom-4 left-4 z-30 hidden items-center gap-1.5 rounded-lg bg-black/65 px-3 py-2 text-xs font-black text-white shadow-lg backdrop-blur transition hover:bg-[#C81424] lg:inline-flex">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M3 9a2 2 0 012-2h.9a2 2 0 001.6-.8l.8-1.1A2 2 0 0110 4h4a2 2 0 011.7 1.1l.8 1.1a2 2 0 001.6.8h.9a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                                <circle cx="12" cy="13" r="3" stroke-width="1.9"/>
                            </svg>
                            {{ $galleryPhotoLabel }}
                        </button>

                        <button type="button" onclick="openDealerMobileGallery()" class="absolute bottom-4 right-4 z-30 inline-flex items-center rounded-lg bg-black/65 px-3 py-1.5 text-[11px] font-black uppercase text-white shadow-lg backdrop-blur transition hover:bg-[#C81424] lg:hidden">
                            Vezi galeria
                        </button>
                    </div>

                    @if(count($galleryUrls) > 1)
                        <div class="hidden min-w-0 overflow-hidden grid-rows-3 gap-2 lg:grid lg:h-full lg:min-h-0 lg:grid-rows-[repeat(3,minmax(0,1fr))]">
                            @foreach($galleryThumbUrls as $index => $imageUrl)
                                <button type="button" onclick="openDealerGallery({{ $index + 1 }})" class="relative h-full min-h-0 overflow-hidden rounded-lg bg-gray-200 text-left dark:bg-[#202024]">
                                    <img src="{{ $imageUrl }}" alt="{{ $dealerDisplayName }} imagine {{ $index + 2 }}" class="h-full w-full object-cover object-center transition duration-500 hover:scale-105">
                                </button>
                            @endforeach

                            @for($i = count($galleryThumbUrls); $i < 3; $i++)
                                <button type="button" onclick="openDealerGallery(0)" class="relative h-full min-h-0 overflow-hidden rounded-lg bg-gray-100 dark:bg-[#202024]" aria-label="Deschide galeria">
                                    <div class="absolute inset-0 flex items-center justify-center text-xs font-black uppercase text-gray-400 dark:text-gray-500">
                                        Galerie
                                    </div>
                                </button>
                            @endfor
                        </div>
                    @endif
                </div>
            @else
                <div class="flex min-h-[260px] items-center justify-center rounded-lg bg-gray-50 p-8 text-center dark:bg-[#101012] lg:h-full lg:min-h-0">
                    <div>
                        <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-white text-[#C81424] shadow-sm dark:bg-[#18181B]">
                            <svg class="h-8 w-8" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 16l4-4a2 2 0 012.828 0L13 15.172 15.172 13A2 2 0 0118 13l3 3M4 19h16a1 1 0 001-1V6a1 1 0 00-1-1H4a1 1 0 00-1 1v12a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <p class="text-sm font-semibold text-gray-500 dark:text-gray-400">Galeria parcului auto va apărea aici.</p>
                    </div>
                </div>
            @endif
        </div>
    </section>

    <section id="dealer-listings" class="scroll-mt-24 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-[#303033] dark:bg-[#18181B]">
        <div class="border-b border-gray-200 px-4 dark:border-[#303033] sm:px-6">
            <nav class="flex min-w-0 items-center gap-7 overflow-x-auto text-sm font-black text-gray-700 dark:text-gray-300" aria-label="Secțiuni dealer">
                <a href="#dealer-listings" class="inline-flex h-14 shrink-0 items-center gap-2 border-b-2 border-[#C81424] text-[#C81424] dark:text-red-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M5 17h14l-1.3-4.6A3 3 0 0014.8 10H9.2a3 3 0 00-2.9 2.4L5 17z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M7 17v2m10-2v2M8 14h8"/>
                    </svg>
                    Stoc
                </a>
                @if($hasDealerDescription)
                    <a href="#dealer-about" class="inline-flex h-14 shrink-0 items-center gap-2 border-b-2 border-transparent transition hover:text-[#C81424] dark:hover:text-red-300">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M13 16h-1v-4h-1m1-4h.01"/>
                            <circle cx="12" cy="12" r="9" stroke-width="1.9"/>
                        </svg>
                        Despre parc
                    </a>
                @endif
                <a href="#dealer-location" class="inline-flex h-14 shrink-0 items-center gap-2 border-b-2 border-transparent transition hover:text-[#C81424] dark:hover:text-red-300">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 21s7-4.438 7-11a7 7 0 10-14 0c0 6.562 7 11 7 11z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 10.5a2 2 0 100-4 2 2 0 000 4z"/>
                    </svg>
                    Locație
                </a>
            </nav>
        </div>

        <div class="grid gap-6 p-4 sm:p-6 lg:grid-cols-[290px_minmax(0,1fr)]">
            <aside>
                <form action="{{ $dealerUrl }}#dealer-listings" method="GET" class="rounded-lg border border-gray-200 bg-white p-4 dark:border-[#333] dark:bg-[#202024] lg:sticky lg:top-24">
                    <h2 class="mb-4 text-lg font-black text-gray-950 dark:text-white">Filtre anunțuri</h2>

                    <div class="space-y-4">
                        <x-combobox
                            id="brand-filter"
                            name="brand_id"
                            label="Marca"
                            placeholder="Toate mărcile"
                            :options="$brands"
                            option-label="name"
                            :selected="$selectedBrandId"
                            class="listing-filter"
                        />

                        <x-combobox
                            id="model-filter"
                            name="model_id"
                            label="Model"
                            placeholder="{{ $selectedBrandId ? 'Toate modelele' : 'Alege marca' }}"
                            :options="$models"
                            option-label="name"
                            :selected="$selectedModelId"
                            :disabled="$modelSelectDisabled"
                            class="listing-filter"
                        />

                        <div class="grid grid-cols-2 gap-3 pt-8">
                            <a href="{{ $dealerUrl }}#dealer-listings" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-200 bg-white text-xs font-black text-gray-700 transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#18181B] dark:text-gray-200">
                                Șterge filtre
                            </a>
                            <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#C81424] text-xs font-black text-white shadow-sm transition hover:bg-[#94111B]">
                                Caută
                            </button>
                        </div>
                    </div>
                </form>
            </aside>

            <div class="min-w-0">
                <div class="mb-4">
                    <h2 class="text-2xl font-black text-gray-950 dark:text-white">{{ $stockHeadingLabel }}</h2>
                </div>

                <div class="flex min-w-0 flex-col gap-0" data-dealer-stock-list>
                    @include('services.partials.service_cards_horizontal', ['services' => $services])
                </div>
            </div>
        </div>
    </section>

    @if($hasDealerDescription)
        <section id="dealer-about" class="scroll-mt-24 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#303033] dark:bg-[#18181B] sm:p-6">
            <div class="min-w-0">
                <h2 class="break-words text-xl font-black text-gray-950 dark:text-white">Despre {{ $dealerDisplayName }}</h2>
                <p class="mt-3 whitespace-pre-line break-words text-sm font-medium leading-6 text-gray-700 dark:text-gray-300">{{ $dealerDescriptionDisplay }}</p>
            </div>
        </section>
    @endif


    @if($dealer->county || $dealer->city || $dealer->address || $phoneItems->isNotEmpty())
        <section id="dealer-location" class="scroll-mt-24 rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#303033] dark:bg-[#18181B] sm:p-6">
            <div class="grid gap-6 lg:grid-cols-[minmax(280px,0.46fr)_minmax(0,1fr)]">
                <div class="min-w-0">
                    <h2 class="text-xl font-black text-gray-950 dark:text-white">Locație și program</h2>

                    <div class="mt-4 space-y-4 text-sm font-semibold text-gray-700 dark:text-gray-300">
                        @if($dealerAddressDisplay)
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 21s7-4.438 7-11a7 7 0 10-14 0c0 6.562 7 11 7 11z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 10.5a2 2 0 100-4 2 2 0 000 4z"/>
                                </svg>
                                <div class="min-w-0">
                                    <p class="break-words">{{ $dealerAddressDisplay }}</p>
                                    @if($mapsUrl)
                                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="mt-1 inline-flex text-xs font-black text-[#C81424] hover:text-[#94111B] dark:text-red-300">
                                            Vezi pe hartă
                                        </a>
                                    @endif
                                </div>
                            </div>
                        @endif

                        @if($phoneItems->isNotEmpty())
                            <div class="flex gap-3">
                                <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.970 1.293a1.125 1.125 0 01-1.210.380 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.380-1.210l1.293-.970a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                                </svg>
                                <div class="min-w-0">
                                    @foreach($phoneItems as $phone)
                                        <a href="tel:{{ $phone['href'] }}" class="block transition hover:text-[#C81424] dark:hover:text-red-300">{{ $phone['label'] }}</a>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <div class="flex gap-3">
                            <svg class="mt-0.5 h-5 w-5 shrink-0 text-gray-700 dark:text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.9" d="M12 6v6l4 2"/>
                                <circle cx="12" cy="12" r="9" stroke-width="1.9"/>
                            </svg>
                            <div>
                                <p>Program</p>
                                <p class="font-medium text-gray-500 dark:text-gray-400">Contactează dealerul pentru program.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="min-w-0">
                    <div class="relative h-44 overflow-hidden rounded-lg border border-gray-200 bg-gray-100 dark:border-[#333] dark:bg-[#202024] sm:h-52">
                        <div class="absolute left-[6%] top-[58%] h-3 w-[88%] -rotate-6 rounded-full bg-white/90 dark:bg-white/10"></div>
                        <div class="absolute left-[18%] top-[8%] h-[120%] w-3 rotate-[32deg] rounded-full bg-white/90 dark:bg-white/10"></div>
                        <div class="absolute right-[12%] top-[10%] h-10 w-16 rounded bg-green-200/80 dark:bg-green-500/15"></div>
                        <div class="absolute left-[6%] bottom-[8%] h-14 w-24 rounded bg-green-100/90 dark:bg-green-500/10"></div>

                        <div class="absolute left-1/2 top-1/2 flex -translate-x-1/2 -translate-y-1/2 items-center gap-2">
                            <span class="relative flex h-10 w-10 items-center justify-center rounded-full bg-[#C81424] text-white shadow-lg shadow-red-700/30">
                                <span class="absolute h-14 w-14 rounded-full bg-[#C81424]/15"></span>
                                <svg class="relative h-6 w-6" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                    <path d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7zm0 9.5A2.5 2.5 0 1112 6a2.5 2.5 0 010 5.5z"/>
                                </svg>
                            </span>
                            <span class="rounded bg-white/95 px-2.5 py-1 text-xs font-black uppercase text-[#C81424] shadow-sm dark:bg-[#18181B] dark:text-red-300">
                                {{ $dealerDisplayName }}
                            </span>
                        </div>

                        @if($dealerPlaceLine)
                            <span class="absolute right-4 top-4 text-xs font-black uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ $dealerPlaceLine }}</span>
                        @endif
                    </div>

                    @if($mapsUrl)
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="mt-4 inline-flex h-12 w-full items-center justify-center gap-2 rounded-lg border border-gray-200 bg-white text-sm font-black text-gray-900 transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#202024] dark:text-white dark:hover:text-red-300">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true">
                                <path fill="#1a73e8" d="M12 2a7 7 0 00-7 7c0 5.25 7 13 7 13s7-7.75 7-13a7 7 0 00-7-7z"/>
                                <circle cx="12" cy="9" r="2.5" fill="#fff"/>
                            </svg>
                            Deschide în Google Maps
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif
</div>

@if($primaryPhone)
    <div class="fixed inset-x-0 bottom-0 z-[60] border-t border-gray-200 bg-white/95 p-3 shadow-2xl backdrop-blur dark:border-[#333] dark:bg-[#18181B]/95 lg:hidden">
        <div class="grid grid-cols-2 gap-2">
            <a href="tel:{{ $primaryPhoneHref }}" class="inline-flex h-12 items-center justify-center gap-2 rounded-xl bg-[#C81424] text-sm font-black uppercase text-white">
                <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106a1.125 1.125 0 00-1.173.417l-.970 1.293a1.125 1.125 0 01-1.210.380 12.035 12.035 0 01-7.143-7.143 1.125 1.125 0 01.380-1.210l1.293-.970a1.125 1.125 0 00.417-1.173L6.963 3.102A1.125 1.125 0 005.872 2.25H4.5A2.25 2.25 0 002.25 4.5v2.25z"/>
                </svg>
                Sună
            </a>
            <a href="#dealer-listings" class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-200 bg-white text-sm font-black uppercase text-gray-900 dark:border-[#333] dark:bg-[#202024] dark:text-white">
                Anunțuri
            </a>
        </div>
    </div>
@endif

@if(count($galleryUrls))
    <div id="dealerGalleryModal" class="fixed inset-0 z-[80] hidden bg-black/90 text-white" aria-modal="true" role="dialog" aria-label="Galerie parc auto">
        <button type="button" onclick="closeDealerGallery()" class="absolute right-4 top-4 z-[90] flex h-11 w-11 items-center justify-center rounded-full bg-white/10 text-3xl leading-none text-white transition hover:bg-white/20" aria-label="Închide galeria">
            ×
        </button>

        <button type="button" onclick="changeDealerGalleryImage(-1)" class="absolute left-3 top-1/2 z-[90] hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:flex" aria-label="Imaginea anterioară">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>

        <button type="button" onclick="changeDealerGalleryImage(1)" class="absolute right-3 top-1/2 z-[90] hidden h-12 w-12 -translate-y-1/2 items-center justify-center rounded-full bg-white/10 text-white transition hover:bg-white/20 sm:flex" aria-label="Imaginea următoare">
            <svg class="h-7 w-7" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <div id="dealerGalleryTouchArea" class="flex h-full w-full touch-pan-y select-none items-center justify-center px-4 py-16 sm:px-20">
            <img id="dealerGalleryImage" src="" alt="{{ $dealer->company_name }} galerie" class="max-h-full max-w-full rounded-lg object-contain shadow-2xl">
        </div>

        <div class="absolute bottom-4 left-1/2 z-[90] flex -translate-x-1/2 items-center gap-3 rounded-full bg-white/10 px-4 py-2 text-sm font-bold text-white backdrop-blur">
            <button type="button" onclick="changeDealerGalleryImage(-1)" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20 sm:hidden" aria-label="Imaginea anterioară">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <span id="dealerGalleryCounter">1 / {{ count($galleryUrls) }}</span>
            <button type="button" onclick="changeDealerGalleryImage(1)" class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-white/10 transition hover:bg-white/20 sm:hidden" aria-label="Imaginea următoare">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
    </div>
@endif

<script>
const dealerGalleryImages = @json($galleryUrls);
const dealerGalleryAltBase = @json($dealerDisplayName);
const dealerCarData = @json($carData ?? []);
const initialDealerModelId = @json((string) $selectedModelId);
let dealerGalleryIndex = 0;
let dealerMobileGalleryIndex = 0;
let dealerMobileGalleryTouchStartX = 0;
let dealerMobileGalleryTouchStartY = 0;
let dealerMobileGallerySuppressClick = false;
let dealerGalleryTouchStartX = 0;
let dealerGalleryTouchStartY = 0;

function resetFilters() {
    window.location.href = @json($dealerUrl);
}

window.toggleHeart = function(btn, serviceId) {
    window.iaAutoFavorites?.toggle(btn, serviceId);
}

function updateDealerGalleryImage() {
    const image = document.getElementById('dealerGalleryImage');
    const counter = document.getElementById('dealerGalleryCounter');

    if (!image || !dealerGalleryImages.length) return;

    image.src = dealerGalleryImages[dealerGalleryIndex];
    if (counter) {
        counter.textContent = `${dealerGalleryIndex + 1} / ${dealerGalleryImages.length}`;
    }
}

function openDealerGallery(index = 0) {
    if (!dealerGalleryImages.length) return;

    const modal = document.getElementById('dealerGalleryModal');
    if (!modal) return;

    dealerGalleryIndex = Math.max(0, Math.min(Number(index) || 0, dealerGalleryImages.length - 1));
    updateDealerGalleryImage();
    modal.classList.remove('hidden');
    document.body.style.overflow = 'hidden';
}

function closeDealerGallery() {
    const modal = document.getElementById('dealerGalleryModal');
    if (!modal) return;

    modal.classList.add('hidden');
    document.body.style.overflow = '';
}

function changeDealerGalleryImage(direction) {
    if (!dealerGalleryImages.length) return;

    dealerGalleryIndex = (dealerGalleryIndex + direction + dealerGalleryImages.length) % dealerGalleryImages.length;
    updateDealerGalleryImage();
}

function updateDealerMobileGalleryImage() {
    const image = document.getElementById('dealerMobileGalleryImage');
    const counter = document.getElementById('dealerMobileGalleryCounter');

    if (!image || !dealerGalleryImages.length) return;

    image.src = dealerGalleryImages[dealerMobileGalleryIndex];
    image.alt = `${dealerGalleryAltBase} imagine ${dealerMobileGalleryIndex + 1}`;

    if (counter) {
        counter.textContent = `${dealerMobileGalleryIndex + 1} / ${dealerGalleryImages.length}`;
    }
}

function changeDealerMobileGalleryImage(direction) {
    if (dealerGalleryImages.length < 2) return;

    dealerMobileGalleryIndex = (dealerMobileGalleryIndex + direction + dealerGalleryImages.length) % dealerGalleryImages.length;
    updateDealerMobileGalleryImage();
}

function openDealerMobileGallery() {
    openDealerGallery(dealerMobileGalleryIndex);
}

function openDealerMainGallery() {
    if (dealerMobileGallerySuppressClick) return;

    openDealerMobileGallery();
}

document.addEventListener('keydown', function (event) {
    const modal = document.getElementById('dealerGalleryModal');
    if (!modal || modal.classList.contains('hidden')) return;

    if (event.key === 'Escape') closeDealerGallery();
    if (event.key === 'ArrowLeft') changeDealerGalleryImage(-1);
    if (event.key === 'ArrowRight') changeDealerGalleryImage(1);
});

document.addEventListener('DOMContentLoaded', function () {
    window.iaCombobox?.init(document);

    const brandFilter = document.getElementById('brand-filter');
    const modelFilter = document.getElementById('model-filter');

    function setDealerComboboxPlaceholder(el, placeholder) {
        const instance = window.iaCombobox?.get(el);
        const root = instance?.root || el?.closest?.('[data-combobox]');
        const input = root?.querySelector('[data-combobox-input]');

        if (root) {
            root.dataset.comboboxPlaceholder = placeholder;
        }

        if (instance) {
            instance.placeholder = placeholder;
        }

        if (input) {
            input.placeholder = placeholder;
        }
    }

    function setDealerModelOptions(brandId, selectedModelId = '') {
        if (!modelFilter) return;

        const models = dealerCarData[String(brandId || '')] || [];

        if (!brandId) {
            setDealerComboboxPlaceholder(modelFilter, 'Alege marca');
            window.iaCombobox?.setOptions(modelFilter, [], '', { dispatch: false });
            window.iaCombobox?.disable(modelFilter);
            return;
        }

        if (!models.length) {
            setDealerComboboxPlaceholder(modelFilter, 'Nu sunt modele');
            window.iaCombobox?.setOptions(modelFilter, [], '', { dispatch: false });
            window.iaCombobox?.disable(modelFilter);
            return;
        }

        setDealerComboboxPlaceholder(modelFilter, 'Toate modelele');
        window.iaCombobox?.setOptions(modelFilter, models.map((model) => ({
            value: model.id,
            label: model.name,
            name: model.name,
            slug: model.slug || '',
        })), selectedModelId, { dispatch: false });
        window.iaCombobox?.enable(modelFilter);
    }

    if (brandFilter && modelFilter) {
        setDealerModelOptions(brandFilter.value, initialDealerModelId);

        brandFilter.addEventListener('change', function () {
            setDealerModelOptions(this.value);
        });
    }

    const modal = document.getElementById('dealerGalleryModal');
    const touchArea = document.getElementById('dealerGalleryTouchArea');
    const mobileGalleryTouchArea = document.getElementById('dealerMobileGalleryTouchArea');

    if (modal) {
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeDealerGallery();
        });
    }

    if (mobileGalleryTouchArea) {
        mobileGalleryTouchArea.addEventListener('touchstart', function (event) {
            const touch = event.changedTouches[0];
            dealerMobileGalleryTouchStartX = touch.clientX;
            dealerMobileGalleryTouchStartY = touch.clientY;
        }, { passive: true });

        mobileGalleryTouchArea.addEventListener('touchend', function (event) {
            const touch = event.changedTouches[0];
            const deltaX = touch.clientX - dealerMobileGalleryTouchStartX;
            const deltaY = touch.clientY - dealerMobileGalleryTouchStartY;

            if (Math.abs(deltaX) < 45 || Math.abs(deltaX) < Math.abs(deltaY)) return;

            dealerMobileGallerySuppressClick = true;
            window.setTimeout(() => {
                dealerMobileGallerySuppressClick = false;
            }, 350);

            changeDealerMobileGalleryImage(deltaX < 0 ? 1 : -1);
        }, { passive: true });
    }

    if (!touchArea) return;

    touchArea.addEventListener('touchstart', function (event) {
        const touch = event.changedTouches[0];
        dealerGalleryTouchStartX = touch.clientX;
        dealerGalleryTouchStartY = touch.clientY;
    }, { passive: true });

    touchArea.addEventListener('touchend', function (event) {
        const touch = event.changedTouches[0];
        const deltaX = touch.clientX - dealerGalleryTouchStartX;
        const deltaY = touch.clientY - dealerGalleryTouchStartY;

        if (Math.abs(deltaX) < 50 || Math.abs(deltaX) < Math.abs(deltaY)) return;

        changeDealerGalleryImage(deltaX < 0 ? 1 : -1);
    }, { passive: true });
});
</script>
@endsection
