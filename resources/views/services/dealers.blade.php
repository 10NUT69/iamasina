@extends('layouts.app')

@php
    $dealersCanonicalUrl = route('dealers.index');
    $dealersPageName = 'Dealeri auto și parcuri auto pe iaAuto.ro';
    $dealersIntro = 'Descoperă parcuri auto și dealeri din România care publică anunțuri pe iaAuto.ro. Intră în pagina fiecărui dealer pentru a vedea mașinile disponibile, datele de contact, fotografiile și stocul auto actualizat direct de vânzător.';
    $dealersTotal = method_exists($dealers, 'total') ? (int) $dealers->total() : (int) $dealers->count();
    $dealersLabel = number_format($dealersTotal, 0, ',', '.') . ' ' . ($dealersTotal === 1 ? 'dealer auto' : 'dealeri auto');
    $activeListingsTotal = isset($dealerActiveServicesTotal) ? (int) $dealerActiveServicesTotal : null;
    $activeListingsLabel = $activeListingsTotal !== null
        ? number_format($activeListingsTotal, 0, ',', '.') . ' ' . ($activeListingsTotal === 1 ? 'anunț activ' : 'anunțuri active')
        : null;
    $breadcrumbItems = [
        ['name' => 'Acasă', 'item' => route('services.index')],
        ['name' => 'Dealeri auto', 'item' => $dealersCanonicalUrl],
    ];

    $cleanDealerPageValue = static function ($value) {
        $value = trim((string) $value);

        return $value === '' ? null : trim(preg_replace('/\s+/', ' ', $value));
    };

    $formatDealerPageLocationPart = static function ($value) use ($cleanDealerPageValue) {
        $value = $cleanDealerPageValue($value);

        if ($value === null) {
            return null;
        }

        return mb_convert_case(mb_strtolower($value, 'UTF-8'), MB_CASE_TITLE, 'UTF-8');
    };

    $firstDealerPosition = method_exists($dealers, 'firstItem') ? (int) ($dealers->firstItem() ?: 1) : 1;
    $dealerListItems = $dealers->values()->map(function ($dealer, $index) use ($cleanDealerPageValue, $formatDealerPageLocationPart, $firstDealerPosition) {
        $dealerName = $cleanDealerPageValue($dealer->company_name ?: $dealer->name) ?: 'Parc auto';
        $dealerUrl = $dealer->dealer_canonical_url ?: ($dealer->dealer_public_url ?: route('cars.index', ['seller_type' => 'dealer']));
        $dealerCity = $formatDealerPageLocationPart($dealer->city);
        $dealerCounty = $formatDealerPageLocationPart($dealer->county);
        $dealerSchema = [
            '@type' => 'AutoDealer',
            'name' => $dealerName,
            'url' => $dealerUrl,
        ];
        $address = [
            '@type' => 'PostalAddress',
            'addressCountry' => 'RO',
        ];

        if ($dealerCity) {
            $address['addressLocality'] = $dealerCity;
        }

        if ($dealerCounty) {
            $address['addressRegion'] = $dealerCounty;
        }

        if (count($address) > 2) {
            $dealerSchema['address'] = $address;
        }

        return [
            '@type' => 'ListItem',
            'position' => $firstDealerPosition + $index,
            'item' => $dealerSchema,
        ];
    })->values()->all();

    $dealersSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'CollectionPage',
        '@id' => $dealersCanonicalUrl . '#dealers',
        'name' => $dealersPageName,
        'description' => 'Descoperă parcuri auto și dealeri din România care publică anunțuri pe iaAuto.ro.',
        'url' => $dealersCanonicalUrl,
        'mainEntity' => [
            '@type' => 'ItemList',
            'numberOfItems' => count($dealerListItems),
            'itemListElement' => $dealerListItems,
        ],
    ];
@endphp

@section('title', 'Dealeri auto și parcuri auto')
@section('meta_title', 'Dealeri auto și parcuri auto din România - iaAuto.ro')
@section('meta_description', 'Descoperă dealeri auto și parcuri auto din România pe iaAuto.ro. Vezi stocuri auto, anunțuri active, mașini de vânzare și pagini dedicate pentru fiecare dealer.')
@section('canonical')
    <link rel="canonical" href="{{ $dealersCanonicalUrl }}">
@endsection
@section('schema')
<script type="application/ld+json">
{!! json_encode($dealersSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT) !!}
</script>
@endsection

@section('content')
<div class="w-full min-w-0 space-y-6 pb-12 pt-2">
    <x-breadcrumbs :items="$breadcrumbItems" />

    <section class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-[#333333] dark:bg-[#1E1E1E] sm:p-6 lg:p-7">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-end lg:justify-between">
            <div class="max-w-4xl">
                <h1 class="text-2xl font-black tracking-tight text-gray-950 dark:text-white sm:text-3xl">
                    {{ $dealersPageName }}
                </h1>
                <p class="mt-3 text-sm font-semibold leading-6 text-gray-600 dark:text-gray-300 sm:text-base sm:leading-7">
                    {{ $dealersIntro }}
                </p>
            </div>

            <div class="flex flex-wrap gap-2">
                <span class="inline-flex w-fit rounded-lg bg-gray-100 px-3 py-1.5 text-sm font-black text-gray-800 dark:bg-[#252525] dark:text-gray-100">
                    {{ $dealersLabel }}
                </span>
                @if($activeListingsLabel)
                    <span class="inline-flex w-fit rounded-lg bg-red-50 px-3 py-1.5 text-sm font-black text-[#C81424] dark:bg-[#2a1013] dark:text-red-200">
                        {{ $activeListingsLabel }}
                    </span>
                @endif
            </div>
        </div>
    </section>

    @if($dealers->isNotEmpty())
        <section aria-label="Lista dealerilor auto">
            <div class="grid grid-cols-2 gap-3 sm:gap-4 lg:grid-cols-4 2xl:grid-cols-5">
                @foreach($dealers as $dealer)
                    @include('services.partials.dealer_card', ['dealer' => $dealer])
                @endforeach
            </div>

            @if(method_exists($dealers, 'hasPages') && $dealers->hasPages())
                <div class="mt-8">
                    {{ $dealers->links() }}
                </div>
            @endif
        </section>
    @else
        <section class="rounded-xl border border-gray-200 bg-white px-5 py-14 text-center shadow-sm dark:border-[#333333] dark:bg-[#1E1E1E]">
            <h2 class="text-xl font-black text-gray-950 dark:text-white">Nu avem încă dealeri activi</h2>
            <p class="mt-2 text-sm font-semibold text-gray-500 dark:text-gray-400">Revino în curând pentru stocuri noi de la parcuri auto.</p>
        </section>
    @endif

    <section class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-[#333333] dark:bg-[#1E1E1E] sm:p-8 lg:p-10">
        <h2 class="max-w-4xl text-2xl font-black leading-tight text-gray-900 dark:text-white sm:text-3xl">
            Parcuri auto și dealeri din România, într-un singur loc
        </h2>

        <div class="mt-5 max-w-none space-y-4 text-justify text-sm leading-6 text-gray-700 dark:text-gray-300 sm:text-base sm:leading-7">
            <p>Pe iaAuto.ro poți descoperi parcuri auto și dealeri care își publică mașinile într-o pagină dedicată, ușor de parcurs și de filtrat. Fiecare dealer are propria pagină unde sunt grupate anunțurile active, astfel încât poți vedea rapid ce mașini are disponibile, în ce oraș se află și cum îl poți contacta.</p>
            <p>Această pagină te ajută să găsești mai ușor stocuri auto din mai multe zone ale țării, fără să cauți separat fiecare dealer. Poți intra direct în profilul unui parc auto, poți compara mașinile publicate și poți verifica anunțurile disponibile în funcție de marcă, model, preț, an, kilometraj sau localitate.</p>
            <p>Pentru cumpărători, paginile dealerilor sunt utile atunci când vor să vadă mai multe mașini de la același vânzător. Pentru parcurile auto, iaAuto.ro oferă o modalitate simplă de a-și prezenta stocul online, cu anunțuri auto publicate într-un format clar, modern și ușor de accesat.</p>
            <p>Lista dealerilor de pe iaAuto.ro este actualizată pe măsură ce apar parcuri auto noi și anunțuri active. Fie că ești în căutarea unei mașini second hand, a unui SUV, a unei mașini de oraș, a unei mașini de familie sau a unui autoturism premium, poți începe căutarea direct din paginile dealerilor auto prezenți pe platformă.</p>
        </div>
    </section>

    <section class="rounded-xl border border-red-100 bg-white p-6 shadow-sm dark:border-red-900/30 dark:bg-[#1E1E1E] sm:p-8">
        <div class="flex flex-col gap-5 lg:flex-row lg:items-center lg:justify-between">
            <div class="max-w-3xl">
                <h2 class="text-2xl font-black text-gray-950 dark:text-white">Ai parc auto?</h2>
                <p class="mt-2 text-sm font-semibold leading-6 text-gray-600 dark:text-gray-300 sm:text-base">
                    Publică anunțuri pe iaAuto.ro și oferă cumpărătorilor o pagină dedicată unde pot vedea toate mașinile tale într-un singur loc.
                </p>
            </div>

            <a href="{{ route('services.create') }}"
               class="inline-flex w-full items-center justify-center gap-2 rounded-lg bg-[#C81424] px-6 py-3 text-sm font-black text-white shadow-md shadow-red-700/20 transition hover:bg-[#94111B] active:scale-[0.98] sm:w-auto">
                Publică stocul gratuit
                <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </section>
</div>
@endsection
