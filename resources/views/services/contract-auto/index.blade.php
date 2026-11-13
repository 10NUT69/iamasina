@extends('layouts.app')

@section('title', $contractTitle)
@section('meta_title', $contractMetaTitle)
@section('meta_description', $contractMetaDescription)

@section('schema')
    @include('services.contract-auto.schema')
@endsection

@section('head')
    @vite('resources/js/contract-auto/index.js')
@endsection

@push('page_styles')
    <style>
        [data-contract] [hidden] { display: none !important; }
        [data-contract] p {
            text-align: justify;
            hyphens: auto;
        }
    </style>
@endpush

@section('content')
    @php
        $steps = ['Vânzător', 'Cumpărător', 'Vehicul', 'Tranzacție', 'Verificare'];
        $panel = 'rounded-2xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-800 dark:bg-[#18181B] sm:p-6';
        $primary = 'inline-flex min-h-11 items-center justify-center rounded-xl bg-[#C81424] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#a6101d] disabled:opacity-60';
        $secondary = 'inline-flex min-h-11 items-center justify-center rounded-xl border border-gray-300 px-4 py-2.5 text-sm font-bold transition hover:bg-gray-50 disabled:opacity-60 dark:border-gray-700 dark:hover:bg-[#252525]';
    @endphp

    <div data-contract class="mx-auto max-w-4xl space-y-5">
        <header>
            <h1 class="text-2xl font-extrabold tracking-tight sm:text-3xl lg:text-4xl">{{ $contractTitle }}</h1>
            <p class="mt-2 max-w-2xl text-sm leading-relaxed text-gray-600 dark:text-gray-300 sm:text-base">
                Completează online gratuit contract de vânzare cumpărare Model 2026 ITL 054.
                Descarcă Contractul auto format PDF sau Word Modelul Oficial.
            </p>
        </header>

        <noscript>
            <p class="{{ $panel }}">Activează JavaScript în browser pentru a putea completa și genera contractul.</p>
        </noscript>

        <p data-contract-loading role="status" class="text-sm text-gray-600 dark:text-gray-300">Se încarcă formularul…</p>
        <form data-contract-form hidden method="dialog" autocomplete="off" novalidate aria-describedby="contract-privacy" class="{{ $panel }}">
            <p data-step-count aria-live="polite" class="text-sm font-semibold text-gray-600 dark:text-gray-300 sm:hidden"></p>
            <ol aria-label="Pașii formularului" class="hidden grid-cols-5 gap-2 sm:grid">
                @foreach ($steps as $step)
                    <li data-step-marker class="rounded-lg px-2 py-2 text-center text-sm font-semibold text-gray-600 aria-[current=step]:bg-red-50 aria-[current=step]:text-[#a6101d] dark:text-gray-300 dark:aria-[current=step]:bg-red-950/50 dark:aria-[current=step]:text-red-200">
                        {{ $step }}
                    </li>
                @endforeach
            </ol>

            @foreach (['v' => 'Vânzător', 'c' => 'Cumpărător'] as $prefix => $label)
                <section data-step data-party hidden class="mt-4 space-y-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <h2 tabindex="-1" class="scroll-mt-24 text-xl font-bold">{{ $label }}</h2>
                    <div>
                        @include('services.contract-auto.party', ['prefix' => $prefix])
                    </div>
                </section>
            @endforeach

            <section data-step hidden class="mt-4 space-y-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                <h2 tabindex="-1" class="scroll-mt-24 text-xl font-bold">Vehicul</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    @include('services.contract-auto.field', ['field' => 'm_marca', 'label' => 'Marcă', 'required' => true])
                    @include('services.contract-auto.field', ['field' => 'm_tip', 'label' => 'Tip / model', 'required' => true])
                    @include('services.contract-auto.field', ['field' => 'm_vin', 'label' => 'Serie de șasiu (VIN)', 'required' => true, 'hint' => 'Copiază seria exact cum apare în cartea de identitate a vehiculului.'])
                    @include('services.contract-auto.field', ['field' => 'm_civ', 'label' => 'Număr carte de identitate a vehiculului (CIV)'])
                    @foreach ([
                        ['m_nr_inmatriculare', 'Număr de înmatriculare / înregistrare', 'text'],
                        ['m_an_fabricatie', 'An de fabricație', 'text'],
                        ['m_serie_motor', 'Serie motor', 'text'],
                        ['m_cilindree_kw', 'Capacitate cilindrică / putere (kW)', 'text'],
                        ['m_norma_euro', 'Normă Euro', 'text'],
                        ['m_itp', 'Data expirării ITP', 'date'],
                        ['m_dobandit_data', 'Data la care vânzătorul a dobândit vehiculul', 'date'],
                        ['m_act', 'Actul prin care vânzătorul a dobândit vehiculul', 'text'],
                        ['m_gma', 'Greutate maximă admisă în tone (remorci și semiremorci)', 'text'],
                    ] as [$field, $fieldLabel, $inputType])
                        @include('services.contract-auto.field', ['field' => $field, 'label' => $fieldLabel, 'type' => $inputType])
                    @endforeach
                </div>
            </section>

            <section data-step hidden class="mt-4 space-y-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                <h2 tabindex="-1" class="scroll-mt-24 text-xl font-bold">Tranzacție</h2>
                <div class="grid gap-4 sm:grid-cols-2">
                    <label class="block min-w-0">
                        <span class="block text-sm font-semibold">Monedă</span>
                        <select data-contract-currency class="mt-1 block min-h-11 w-full rounded-xl border-gray-300 bg-white px-3 py-2 text-base text-gray-900 shadow-sm focus:border-[#C81424] focus:ring-[#C81424] dark:border-gray-700 dark:bg-[#202024] dark:text-gray-100">
                            <option value="RON">Lei (RON)</option>
                            <option value="EUR">Euro (EUR)</option>
                        </select>
                    </label>
                    @include('services.contract-auto.field', ['field' => 'price', 'label' => 'Preț în lei', 'required' => true, 'amount' => true])
                    <div data-euro-only hidden class="sm:col-span-2">
                        @include('services.contract-auto.field', ['field' => 'price_ron', 'label' => 'Echivalentul convenit în lei', 'required' => true, 'amount' => true, 'hint' => 'Modelul ITL 054 are moneda lei tipărită. Introdu echivalentul convenit de părți. PDF-ul va menționa și suma în euro.'])
                    </div>
                    @include('services.contract-auto.field', ['field' => 'contract_data', 'label' => 'Data semnării', 'required' => true, 'type' => 'date'])
                    @include('services.contract-auto.field', ['field' => 'contract_loc', 'label' => 'Localitatea semnării', 'required' => true])
                </div>
            </section>

            <section data-step hidden class="mt-4 space-y-4 border-t border-gray-100 pt-4 dark:border-gray-800">
                <h2 tabindex="-1" class="scroll-mt-24 text-xl font-bold">Verifică datele</h2>
                <p class="text-sm text-gray-600 dark:text-gray-300">Compară datele cu actele înainte de a genera contractul.</p>
                <div data-review class="grid gap-3 sm:grid-cols-2"></div>
                <p class="text-sm text-gray-600 dark:text-gray-300">
                    Semnăturile și bifarea rubricii „Anexe Da/Nu” se pot completa cu pixul după tipărire.
                </p>
            </section>

            <p data-contract-error role="alert" hidden class="mt-4 rounded-xl border border-red-200 bg-red-50 px-3 py-2 text-sm font-semibold text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200"></p>
            <div class="mt-6 flex flex-wrap justify-end gap-2 border-t border-gray-100 pt-4 dark:border-gray-800">
                <button data-back type="button" hidden class="{{ $secondary }}">Înapoi</button>
                <button data-continue type="submit" class="{{ $primary }}">Continuă</button>
            </div>
        </form>

        <section data-result hidden class="{{ $panel }}" aria-labelledby="contract-result-title">
            <h2 id="contract-result-title" tabindex="-1" class="scroll-mt-24 text-xl font-bold">Contractul este gata</h2>
            <div class="mt-4 flex flex-wrap gap-2">
                <a data-download class="{{ $primary }}">Descarcă PDF</a>
                <a data-print target="_blank" rel="noopener" class="{{ $secondary }}">Deschide pentru tipărire</a>
                <button data-share type="button" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-[#25D366] px-4 py-2.5 text-sm font-bold text-[#10251a] transition hover:bg-[#20bd5a]">
                    <svg aria-hidden="true" class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.224-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.058-.173-.297-.018-.458.13-.606.134-.133.298-.347.447-.52.149-.174.198-.298.298-.497.099-.198.05-.372-.025-.521-.075-.149-.669-1.62-.916-2.218-.242-.583-.487-.504-.669-.513l-.57-.01c-.198 0-.52.074-.792.372-.272.297-1.04 1.015-1.04 2.479s1.065 2.879 1.213 3.077c.149.198 2.095 3.2 5.077 4.487.709.306 1.262.489 1.693.626.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.29.173-1.414-.074-.124-.272-.198-.569-.347zM12.052 21.785a9.87 9.87 0 0 1-5.031-1.378l-.36-.215-3.741.982.998-3.648-.235-.374a9.86 9.86 0 0 1-1.51-5.24c.002-5.45 4.437-9.884 9.883-9.884a9.82 9.82 0 0 1 6.988 2.896 9.82 9.82 0 0 1 2.893 6.992c-.003 5.449-4.437 9.884-9.885 9.884zm8.413-18.293A11.815 11.815 0 0 0 12.053 0C5.495 0 .16 5.335.157 11.893c0 2.096.547 4.144 1.588 5.95L.057 24l6.305-1.654a11.885 11.885 0 0 0 5.69 1.45h.005c6.557 0 11.894-5.335 11.897-11.894a11.82 11.82 0 0 0-3.489-8.41z"/></svg>
                    Distribuie prin WhatsApp
                </button>
                <button data-edit-result type="button" class="{{ $secondary }}">Modifică datele</button>
            </div>
            <p data-share-status role="status" class="mt-3 text-sm text-gray-600 dark:text-gray-300"></p>
        </section>

        <aside id="contract-privacy" class="rounded-xl border border-gray-200 bg-white px-4 py-3 text-sm leading-relaxed dark:border-gray-800 dark:bg-[#18181B]">
            <p class="font-bold">Datele tale rămân pe dispozitivul tău.</p>
            <p class="mt-1 text-gray-600 dark:text-gray-300">
                Contractul se completează în browser. iaAuto.ro nu primește și nu stochează numele, CNP-urile,
                adresele, celelalte informații introduse sau PDF-ul generat. Dacă alegi să distribui documentul,
                fișierul este transmis prin aplicația selectată de tine.
            </p>
        </aside>

        <nav aria-label="Descarcă modelul necompletat" class="grid gap-2">
            <a href="{{ route('page.contract-auto.download', 'pdf') }}" class="{{ $secondary }} text-center">Descarcă {{ $contractTitle }} PDF</a>
            <a href="{{ route('page.contract-auto.download', 'docx') }}" class="{{ $secondary }} text-center">
                <span>Descarcă {{ $contractTitle }} <span class="whitespace-nowrap">Word (.doc .docx)</span></span>
            </a>
        </nav>

        @include('services.contract-auto.guide')
    </div>
@endsection
