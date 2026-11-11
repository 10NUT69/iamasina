<fieldset class="mb-5 min-w-0">
    <legend class="mb-2 text-sm font-semibold">Tip persoană</legend>
    <div class="flex flex-wrap gap-2">
        @foreach (['individual' => 'Persoană fizică', 'company' => 'Persoană juridică'] as $partyType => $label)
            <label class="flex min-h-11 cursor-pointer items-center gap-2 rounded-xl border border-gray-300 px-2 text-sm font-semibold has-[:checked]:border-[#C81424] has-[:checked]:bg-red-50 dark:border-gray-700 dark:has-[:checked]:bg-red-950/40 sm:px-3">
                <input type="radio" name="{{ $prefix }}_type" value="{{ $partyType }}" data-party-type
                       @checked($partyType === 'individual')
                       class="border-gray-300 text-[#C81424] focus:ring-[#C81424]">
                {{ $label }}
            </label>
        @endforeach
    </div>
</fieldset>

<div class="grid gap-4 sm:grid-cols-2">
    @include('services.contract-auto.field', ['field' => $prefix.'_nume', 'label' => 'Nume și prenume', 'companyLabel' => 'Denumirea firmei', 'required' => true, 'wide' => true])
    @include('services.contract-auto.field', ['field' => $prefix.'_cnp', 'label' => 'CNP / NIF', 'companyLabel' => 'CUI / CIF', 'required' => true])
    <div data-individual-only class="grid min-w-0 grid-cols-2 gap-3">
        @include('services.contract-auto.field', ['field' => $prefix.'_act_serie', 'label' => 'Serie act'])
        @include('services.contract-auto.field', ['field' => $prefix.'_act_nr', 'label' => 'Număr act', 'required' => true])
    </div>
    @include('services.contract-auto.field', ['field' => $prefix.'_telefon', 'label' => 'Telefon (opțional)', 'type' => 'tel'])
    @include('services.contract-auto.field', ['field' => $prefix.'_email', 'label' => 'E-mail (opțional)', 'type' => 'email'])
</div>

<fieldset class="mt-6 min-w-0">
    <legend class="mb-3 text-base font-bold" data-field-label data-company-label="Sediu social">Domiciliu</legend>
    <div class="grid gap-4 sm:grid-cols-2">
        @include('services.contract-auto.address', ['addressPrefix' => $prefix])
    </div>
</fieldset>

<label class="mt-5 flex min-h-11 cursor-pointer items-center gap-2 text-sm font-semibold">
    <input type="checkbox" data-fiscal-toggle class="rounded border-gray-300 text-[#C81424] focus:ring-[#C81424]">
    Adaugă o adresă fiscală diferită
</label>
<fieldset data-fiscal-address hidden disabled class="mt-3 min-w-0">
    <legend class="mb-3 text-base font-bold">Domiciliu fiscal</legend>
    <div class="grid gap-4 sm:grid-cols-2">
        @include('services.contract-auto.address', ['addressPrefix' => $prefix.'_df'])
    </div>
</fieldset>

<fieldset data-company-only hidden disabled class="mt-6 min-w-0">
    <legend class="mb-3 text-base font-bold">Reprezentantul firmei</legend>
    <div class="grid gap-4 sm:grid-cols-2">
        @foreach ([
            ['repr_nume', 'Nume și prenume', 'text', true],
            ['calitate', 'Calitate în firmă', 'text', true],
            ['repr_act_serie', 'Serie act', 'text', false],
            ['repr_act_nr', 'Număr act', 'text', true],
            ['repr_cif', 'Cod de identificare fiscală (dacă se aplică)', 'text', false],
            ['repr_tel', 'Telefon (opțional)', 'tel', false],
            ['repr_email', 'E-mail (opțional)', 'email', false],
        ] as [$key, $label, $inputType, $isRequired])
            @include('services.contract-auto.field', ['field' => $prefix.'_'.$key, 'label' => $label, 'type' => $inputType, 'required' => $isRequired])
        @endforeach
    </div>
</fieldset>
