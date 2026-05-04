@extends('layouts.app')

@section('title', ($dealer->company_name ?: 'Parc auto') . ' - parc auto')
@section('meta_title', ($dealer->company_name ?: 'Parc auto') . ' - anunțuri auto')
@section('meta_description', $dealer->dealer_description ?: 'Vezi anunțurile publicate de acest parc auto pe iaAuto.ro.')

@section('content')
@php
    $galleryUrls = $dealer->dealer_gallery_urls;
    $phones = collect([$dealer->phone, $dealer->phone_2, $dealer->phone_3])->filter()->values();
    $primaryPhone = $phones->first();
    $primaryPhoneHref = $primaryPhone ? preg_replace('/\s+/', '', $primaryPhone) : null;
    $dealerUrl = $dealer->dealer_public_url ?: url()->current();
    $activeCount = $totalCount ?? $services->count();
    $dealerSubtitle = 'Parc auto';
    if ($dealer->county && $dealer->city) {
        $dealerSubtitle = 'Parc auto în Județul ' . $dealer->county . ', ' . $dealer->city;
    } elseif ($dealer->county) {
        $dealerSubtitle = 'Parc auto în Județul ' . $dealer->county;
    } elseif ($dealer->city) {
        $dealerSubtitle = 'Parc auto în ' . $dealer->city;
    }
    $addressLine = trim(
        ($dealer->county ? 'Județ ' . $dealer->county : '') .
        (($dealer->county && ($dealer->city || $dealer->address)) ? ', ' : '') .
        ($dealer->city ? 'Oraș ' . $dealer->city : '') .
        (($dealer->city && $dealer->address) ? ', ' : '') .
        ($dealer->address ?: '')
    );
    $mapsQuery = trim(implode(', ', array_filter([$dealer->address, $dealer->city, $dealer->county])));
    $mapsUrl = $mapsQuery ? 'https://www.google.com/maps/search/?api=1&query=' . urlencode($mapsQuery) : null;
    $selectedBrandName = $brands->first(fn ($brand) => (string) $brand->id === (string) $selectedBrandId)?->name ?? 'Toate mărcile';
    $selectedModelName = $models->first(fn ($model) => (string) $model->id === (string) $selectedModelId)?->name ?? 'Toate modelele';
@endphp

<div class="w-full min-w-0 space-y-8 overflow-hidden pb-24 lg:pb-12">
    <a href="{{ route('cars.index') }}" class="inline-flex w-fit items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2 text-sm font-black text-gray-700 shadow-sm transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#18181B] dark:text-gray-100">
        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
        </svg>
        Înapoi
    </a>

    <section class="grid min-w-0 items-stretch gap-5 lg:grid-cols-[minmax(0,0.92fr)_minmax(420px,1.08fr)]">
        <div class="min-w-0 rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#18181B] sm:p-6 lg:p-8">
            <div class="flex min-w-0 flex-col gap-5">
                <div>
                    <p class="text-xs font-black uppercase tracking-wide text-[#C81424] dark:text-red-300">
                        {{ $dealerSubtitle }}
                    </p>

                    <h1 class="mt-2 break-words text-3xl font-black tracking-tight text-gray-950 dark:text-white sm:text-4xl xl:text-5xl">
                        {{ $dealer->company_name ?: $dealer->name }}
                    </h1>
                </div>

                <div class="grid min-w-0 gap-2 sm:grid-cols-3">
                    @if($dealer->cui)
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-[#333] dark:bg-[#202024]">
                            <span class="block text-[11px] font-black uppercase tracking-wide text-gray-400">CUI</span>
                            <span class="mt-1 block break-words text-sm font-bold text-gray-900 dark:text-white">{{ $dealer->cui }}</span>
                        </div>
                    @endif

                    @if($addressLine)
                        <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-[#333] dark:bg-[#202024] sm:col-span-2">
                            <span class="block text-[11px] font-black uppercase tracking-wide text-gray-400">Adresă</span>
                            <span class="mt-1 block break-words text-sm font-bold text-gray-900 dark:text-white">{{ $addressLine }}</span>
                        </div>
                    @endif

                    <div class="rounded-xl border border-gray-100 bg-gray-50 p-3 dark:border-[#333] dark:bg-[#202024]">
                        <span class="block text-[11px] font-black uppercase tracking-wide text-gray-400">Anunțuri</span>
                        <span class="mt-1 block text-sm font-bold text-gray-900 dark:text-white">{{ number_format($activeCount, 0, ',', '.') }} active</span>
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @if($primaryPhone)
                        <a href="tel:{{ $primaryPhoneHref }}" class="inline-flex h-12 flex-1 items-center justify-center rounded-xl bg-[#C81424] px-5 text-sm font-black uppercase text-white shadow-lg shadow-red-600/20 transition hover:bg-[#94111B]">
                            Sună acum
                        </a>
                    @endif

                    <a href="#dealer-listings" class="inline-flex h-12 flex-1 items-center justify-center rounded-xl border border-gray-200 bg-white px-5 text-sm font-black uppercase text-gray-800 transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#202024] dark:text-gray-100">
                        Vezi anunțurile
                    </a>
                </div>

                @if($dealer->dealer_description)
                    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#202024] sm:p-6">
                        <h2 class="text-lg font-black text-gray-950 dark:text-white">Despre parc auto</h2>
                        <p class="mt-3 max-w-3xl whitespace-pre-line break-words text-base leading-[1.7] text-gray-600 dark:text-gray-300">
                            {{ $dealer->dealer_description }}
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <div class="relative min-w-0 overflow-hidden rounded-2xl border border-gray-100 bg-white p-2 shadow-sm dark:border-[#333] dark:bg-[#18181B]">
            @if(count($galleryUrls))
                <div class="flex min-h-[250px] snap-x gap-2 overflow-x-auto pb-1 lg:grid lg:h-full lg:min-h-[340px] lg:grid-cols-[minmax(0,2fr)_minmax(150px,0.82fr)] lg:grid-rows-2 lg:overflow-visible lg:pb-0">
                    @foreach(array_slice($galleryUrls, 0, 3) as $index => $imageUrl)
                        @php
                            $tileClass = $index === 0
                                ? (count($galleryUrls) === 1 ? 'lg:col-span-2 lg:row-span-2' : 'lg:row-span-2')
                                : '';
                        @endphp
                        <button type="button" onclick="openDealerGallery({{ $index }})" class="{{ $tileClass }} relative block h-[250px] min-w-[82%] snap-start overflow-hidden rounded-xl bg-gray-200 text-left dark:bg-[#202024] sm:min-w-[62%] lg:h-auto lg:min-w-0">
                            <img src="{{ $imageUrl }}" alt="{{ $dealer->company_name }} imagine {{ $index + 1 }}" class="h-full w-full object-cover transition duration-500 hover:scale-105">
                        </button>
                    @endforeach
                </div>

                <button type="button" onclick="openDealerGallery(0)" class="absolute bottom-4 right-4 inline-flex items-center rounded-full bg-black/65 px-4 py-2 text-xs font-black uppercase text-white shadow-lg backdrop-blur transition hover:bg-[#C81424]">
                    Vezi galeria · {{ count($galleryUrls) }} fotografii
                </button>
            @else
                <div class="flex min-h-[250px] items-center justify-center rounded-xl bg-gray-50 p-8 text-center dark:bg-[#101012] lg:min-h-[340px]">
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

    @if($dealer->county || $dealer->city || $dealer->address || $primaryPhone)
        <section class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm dark:border-[#333] dark:bg-[#18181B] sm:p-6">
            <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-2xl font-black text-gray-950 dark:text-white">Unde ne găsești</h2>
                    <div class="mt-3 grid gap-2 text-sm font-semibold text-gray-600 dark:text-gray-300 sm:grid-cols-3">
                        @if($dealer->county)
                            <p><span class="font-black text-gray-900 dark:text-white">Județ:</span> {{ $dealer->county }}</p>
                        @endif
                        @if($dealer->city)
                            <p><span class="font-black text-gray-900 dark:text-white">Oraș:</span> {{ $dealer->city }}</p>
                        @endif
                        @if($dealer->address)
                            <p><span class="font-black text-gray-900 dark:text-white">Adresă:</span> {{ $dealer->address }}</p>
                        @endif
                    </div>
                </div>

                <div class="flex flex-col gap-2 sm:flex-row">
                    @if($primaryPhone)
                        <a href="tel:{{ $primaryPhoneHref }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#C81424] px-6 text-sm font-black uppercase text-white transition hover:bg-[#94111B]">
                            Sună acum
                        </a>
                    @endif

                    @if($mapsUrl)
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener" class="inline-flex h-12 items-center justify-center rounded-xl border border-gray-200 bg-white px-6 text-sm font-black uppercase text-gray-800 transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#202024] dark:text-gray-100">
                            Google Maps
                        </a>
                    @endif
                </div>
            </div>
        </section>
    @endif

    <section id="dealer-listings" class="scroll-mt-24 grid gap-6 lg:grid-cols-[300px_minmax(0,1fr)]">
        <aside>
            <form action="{{ $dealerUrl }}#dealer-listings" method="GET" class="sticky top-24 z-20 rounded-2xl border border-gray-100 bg-white p-4 shadow-sm dark:border-[#333] dark:bg-[#18181B]">
                <h2 class="mb-4 text-lg font-black text-gray-950 dark:text-white">Filtre anunțuri</h2>

                <div class="space-y-4">
                    <div>
                        <label for="dealerBrandTrigger" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Marca</label>
                        <input type="hidden" id="brand_id" name="brand_id" value="{{ $selectedBrandId }}" data-dealer-select-input="brand">
                        <div class="dealer-custom-select relative">
                            <button id="dealerBrandTrigger" type="button" data-dealer-select-trigger="brand" aria-haspopup="listbox" aria-expanded="false" class="flex h-11 w-full items-center justify-between rounded-lg border border-gray-200 bg-white px-3 text-left text-sm font-semibold text-gray-900 transition hover:border-[#C81424] focus:border-[#C81424] focus:outline-none focus:ring-2 focus:ring-[#C81424]/20 dark:border-[#333] dark:bg-[#202024] dark:text-white">
                                <span class="truncate" data-dealer-select-label="brand">{{ $selectedBrandName }}</span>
                                <svg class="h-4 w-4 shrink-0 text-gray-500 transition" data-dealer-select-icon="brand" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div data-dealer-select-menu="brand" class="absolute left-0 right-0 z-40 mt-1 hidden max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-xl dark:border-[#333] dark:bg-[#202024]" role="listbox">
                                <button type="button" data-dealer-select-option="brand" data-value="" @class([
                                    'block w-full px-3 py-2 text-left text-sm font-semibold transition',
                                    'bg-[#C81424] text-white' => ! $selectedBrandId,
                                    'text-gray-700 hover:bg-[#fff4f5] hover:text-[#C81424] dark:text-gray-100 dark:hover:bg-[#2A1418]' => $selectedBrandId,
                                ])>
                                    Toate mărcile
                                </button>
                                @foreach($brands as $brand)
                                    <button type="button" data-dealer-select-option="brand" data-value="{{ $brand->id }}" @class([
                                        'block w-full px-3 py-2 text-left text-sm font-semibold transition',
                                        'bg-[#C81424] text-white' => (string) $selectedBrandId === (string) $brand->id,
                                        'text-gray-700 hover:bg-[#fff4f5] hover:text-[#C81424] dark:text-gray-100 dark:hover:bg-[#2A1418]' => (string) $selectedBrandId !== (string) $brand->id,
                                    ])>
                                        {{ $brand->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div>
                        <label for="dealerModelTrigger" class="mb-2 block text-xs font-bold uppercase tracking-wide text-gray-500">Model</label>
                        <input type="hidden" id="model_id" name="model_id" value="{{ $selectedModelId }}" data-dealer-select-input="model">
                        <div class="dealer-custom-select relative">
                            <button id="dealerModelTrigger" type="button" data-dealer-select-trigger="model" aria-haspopup="listbox" aria-expanded="false" class="flex h-11 w-full items-center justify-between rounded-lg border border-gray-200 bg-white px-3 text-left text-sm font-semibold text-gray-900 transition hover:border-[#C81424] focus:border-[#C81424] focus:outline-none focus:ring-2 focus:ring-[#C81424]/20 dark:border-[#333] dark:bg-[#202024] dark:text-white">
                                <span class="truncate" data-dealer-select-label="model">{{ $selectedModelName }}</span>
                                <svg class="h-4 w-4 shrink-0 text-gray-500 transition" data-dealer-select-icon="model" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div data-dealer-select-menu="model" class="absolute left-0 right-0 z-40 mt-1 hidden max-h-64 overflow-y-auto rounded-lg border border-gray-200 bg-white py-1 shadow-xl dark:border-[#333] dark:bg-[#202024]" role="listbox">
                                <button type="button" data-dealer-select-option="model" data-value="" @class([
                                    'block w-full px-3 py-2 text-left text-sm font-semibold transition',
                                    'bg-[#C81424] text-white' => ! $selectedModelId,
                                    'text-gray-700 hover:bg-[#fff4f5] hover:text-[#C81424] dark:text-gray-100 dark:hover:bg-[#2A1418]' => $selectedModelId,
                                ])>
                                    Toate modelele
                                </button>
                                @foreach($models as $model)
                                    <button type="button" data-dealer-select-option="model" data-value="{{ $model->id }}" @class([
                                        'block w-full px-3 py-2 text-left text-sm font-semibold transition',
                                        'bg-[#C81424] text-white' => (string) $selectedModelId === (string) $model->id,
                                        'text-gray-700 hover:bg-[#fff4f5] hover:text-[#C81424] dark:text-gray-100 dark:hover:bg-[#2A1418]' => (string) $selectedModelId !== (string) $model->id,
                                    ])>
                                        {{ $model->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2 pt-2">
                        <a href="{{ $dealerUrl }}#dealer-listings" class="inline-flex h-11 items-center justify-center rounded-lg border border-gray-200 bg-white text-xs font-black uppercase text-gray-600 transition hover:border-[#C81424] hover:text-[#C81424] dark:border-[#333] dark:bg-[#202024] dark:text-gray-200">
                            Șterge filtre
                        </a>
                        <button type="submit" class="inline-flex h-11 items-center justify-center rounded-lg bg-[#C81424] text-xs font-black uppercase text-white shadow-sm transition hover:bg-[#94111B]">
                            Caută
                        </button>
                    </div>
                </div>
            </form>
        </aside>

        <div>
            <div class="mb-4 flex flex-col gap-1 sm:flex-row sm:items-end sm:justify-between">
                <div>
                    <h2 class="text-2xl font-black text-gray-950 dark:text-white">Anunțurile parcului auto</h2>
                    <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ number_format($totalCount, 0, ',', '.') }} rezultate</p>
                </div>
            </div>

            <div class="flex flex-col gap-4">
                @include('services.partials.service_cards_horizontal', ['services' => $services])
            </div>
        </div>
    </section>
</div>

@if($primaryPhone)
    <div class="fixed inset-x-0 bottom-0 z-[60] border-t border-gray-200 bg-white/95 p-3 shadow-2xl backdrop-blur dark:border-[#333] dark:bg-[#18181B]/95 lg:hidden">
        <div class="grid grid-cols-2 gap-2">
            <a href="tel:{{ $primaryPhoneHref }}" class="inline-flex h-12 items-center justify-center rounded-xl bg-[#C81424] text-sm font-black uppercase text-white">
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
let dealerGalleryIndex = 0;
let dealerGalleryTouchStartX = 0;
let dealerGalleryTouchStartY = 0;

function resetFilters() {
    window.location.href = @json($dealerUrl);
}

window.toggleHeart = function(btn, serviceId) {
    @if(!auth()->check())
        window.location.href = "{{ route('login') }}";
        return;
    @endif

    const icon = btn.querySelector('svg');
    if (!icon) return;

    const isLiked = icon.classList.contains('text-[#C81424]');

    if (isLiked) {
        icon.classList.remove('text-[#C81424]', 'fill-[#C81424]', 'scale-110');
        icon.classList.add('text-gray-600', 'dark:text-gray-300', 'fill-none');
    } else {
        icon.classList.remove('text-gray-600', 'dark:text-gray-300', 'fill-none');
        icon.classList.add('text-[#C81424]', 'fill-[#C81424]', 'scale-125');
        setTimeout(() => {
            icon.classList.remove('scale-125');
            icon.classList.add('scale-110');
        }, 200);
    }

    fetch("{{ route('favorite.toggle') }}", {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
            "Accept": "application/json"
        },
        body: JSON.stringify({ service_id: serviceId })
    }).catch(err => console.error(err));
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

document.addEventListener('keydown', function (event) {
    const modal = document.getElementById('dealerGalleryModal');
    if (!modal || modal.classList.contains('hidden')) return;

    if (event.key === 'Escape') closeDealerGallery();
    if (event.key === 'ArrowLeft') changeDealerGalleryImage(-1);
    if (event.key === 'ArrowRight') changeDealerGalleryImage(1);
});

document.addEventListener('DOMContentLoaded', function () {
    const selectedOptionClasses = ['bg-[#C81424]', 'text-white'];
    const defaultOptionClasses = ['text-gray-700', 'hover:bg-[#fff4f5]', 'hover:text-[#C81424]', 'dark:text-gray-100', 'dark:hover:bg-[#2A1418]'];

    function updateDealerSelectOptions(type, selectedValue) {
        document.querySelectorAll(`[data-dealer-select-option="${type}"]`).forEach(option => {
            option.classList.remove(...selectedOptionClasses, ...defaultOptionClasses);

            if ((option.dataset.value || '') === (selectedValue || '')) {
                option.classList.add(...selectedOptionClasses);
            } else {
                option.classList.add(...defaultOptionClasses);
            }
        });
    }

    function closeDealerSelects(exceptType = null) {
        document.querySelectorAll('[data-dealer-select-menu]').forEach(menu => {
            const type = menu.dataset.dealerSelectMenu;

            if (type === exceptType) return;

            const trigger = document.querySelector(`[data-dealer-select-trigger="${type}"]`);
            const icon = document.querySelector(`[data-dealer-select-icon="${type}"]`);

            menu.classList.add('hidden');
            trigger?.setAttribute('aria-expanded', 'false');
            trigger?.classList.remove('border-[#C81424]', 'ring-2', 'ring-[#C81424]/20');
            icon?.classList.remove('rotate-180');
        });
    }

    document.querySelectorAll('[data-dealer-select-trigger]').forEach(trigger => {
        trigger.addEventListener('click', function () {
            const type = this.dataset.dealerSelectTrigger;
            const menu = document.querySelector(`[data-dealer-select-menu="${type}"]`);
            const icon = document.querySelector(`[data-dealer-select-icon="${type}"]`);
            const willOpen = menu?.classList.contains('hidden');

            closeDealerSelects(type);

            if (!menu) return;

            menu.classList.toggle('hidden', !willOpen);
            this.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
            this.classList.toggle('border-[#C81424]', willOpen);
            this.classList.toggle('ring-2', willOpen);
            this.classList.toggle('ring-[#C81424]/20', willOpen);
            icon?.classList.toggle('rotate-180', willOpen);
        });
    });

    document.querySelectorAll('[data-dealer-select-option]').forEach(option => {
        option.addEventListener('click', function () {
            const type = this.dataset.dealerSelectOption;
            const value = this.dataset.value || '';
            const input = document.querySelector(`[data-dealer-select-input="${type}"]`);
            const label = document.querySelector(`[data-dealer-select-label="${type}"]`);
            const previousValue = input?.value || '';

            if (input) input.value = value;
            if (label) label.textContent = this.textContent.trim();
            updateDealerSelectOptions(type, value);
            closeDealerSelects();

            if (type === 'brand' && value !== previousValue) {
                const modelInput = document.querySelector('[data-dealer-select-input="model"]');
                const modelLabel = document.querySelector('[data-dealer-select-label="model"]');

                if (modelInput) modelInput.value = '';
                if (modelLabel) modelLabel.textContent = 'Toate modelele';
                updateDealerSelectOptions('model', '');
            }
        });
    });

    document.addEventListener('click', function (event) {
        if (!event.target.closest('.dealer-custom-select')) {
            closeDealerSelects();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            closeDealerSelects();
        }
    });

    const modal = document.getElementById('dealerGalleryModal');
    const touchArea = document.getElementById('dealerGalleryTouchArea');

    if (modal) {
        document.body.appendChild(modal);

        modal.addEventListener('click', function (event) {
            if (event.target === modal) closeDealerGallery();
        });
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
