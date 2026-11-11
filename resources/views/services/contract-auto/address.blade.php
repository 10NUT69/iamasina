@foreach ([
    ['tara', 'Țară', false],
    ['judet', 'Județ / regiune', false],
    ['localitate', 'Municipiu / oraș / comună', true],
    ['sat_sector', 'Sat / sector (dacă se aplică)', false],
    ['strada', 'Stradă', false],
    ['nr', 'Număr', false],
    ['cod_postal', 'Cod poștal', false],
] as [$key, $addressLabel, $isRequired])
    @include('services.contract-auto.field', ['field' => $addressPrefix.'_'.$key, 'label' => $addressLabel, 'required' => $isRequired])
@endforeach
<div class="grid min-w-0 grid-cols-2 gap-3 min-[400px]:grid-cols-4 sm:col-span-2">
    @foreach (['bl' => 'Bloc', 'sc' => 'Scară', 'et' => 'Etaj', 'ap' => 'Apartament'] as $key => $addressLabel)
        @include('services.contract-auto.field', ['field' => $addressPrefix.'_'.$key, 'label' => $addressLabel])
    @endforeach
</div>
