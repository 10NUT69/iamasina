@extends('admin.layout')

@section('content')
    <div class="mx-auto max-w-5xl space-y-4 px-4 py-8 sm:px-6">
        <a class="text-sm font-semibold text-slate-700 underline" href="{{ route('admin.dealer-feeds.edit', $source) }}">Înapoi la sursă</a>
        <h1 class="text-2xl font-bold text-slate-800">Verifică anunțul · ID {{ $externalId }}</h1>

        @if ($error || $errors->has('trial'))
            <p role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $error ?? $errors->first('trial') }}</p>
        @endif

        @if ($review)
            @if ($review['values']['branch_name'] ?? '')
                <p class="text-sm text-slate-700">Punct de vânzare în feed · {{ $review['values']['branch_name'] }}</p>
            @endif
            @if ($review['needs_location'])
            <form method="GET" action="{{ route('admin.dealer-feeds.trials.review', $source) }}" class="flex flex-wrap items-end gap-2">
                <input type="hidden" name="external_id" value="{{ $externalId }}">
                <div>
                    <x-input-label for="locality_id" value="Localitatea și județul pentru probă" />
                    <select id="locality_id" name="locality_id" class="mt-1 block w-full rounded-md border-slate-300">
                        <option value="">Alege localitatea și județul</option>
                        @foreach ($localities as $locality)
                            <option value="{{ $locality->id }}" @selected($review['attributes']['locality_id'] === $locality->id)>
                                {{ $locality->name }}{{ $locality->county ? ' · '.$locality->county->name : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <x-primary-button>Aplică localitatea</x-primary-button>
                @if ($review['catalog']['county'])
                    <span class="text-sm text-slate-700">
                        În probă: {{ implode(', ', array_filter([$review['locality'], $review['catalog']['county']])) }}
                    </span>
                @endif
            </form>
            @endif
            @foreach ($review['errors'] as $issue)
                <p role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-800">{{ $issue }}</p>
            @endforeach
            @foreach ($review['warnings'] as $issue)
                <p class="rounded-lg bg-amber-50 p-3 text-sm text-amber-900">{{ $issue }}</p>
            @endforeach

            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50">
                        <tr>
                            <th scope="col" class="p-3">Câmp</th>
                            <th scope="col" class="p-3">În feed</th>
                            <th scope="col" class="p-3">În iaAuto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @php
                            $values = ['title' => 'title', 'description' => 'description', 'year' => 'an_fabricatie', 'mileage' => 'km', 'engine_cc' => 'capacitate_cilindrica', 'power' => 'putere'];
                        @endphp
                        @foreach ($fields as $field => $label)
                            <tr>
                                <th scope="row" class="p-3">{{ $label }}</th>
                                @if ($field === 'photos')
                                    <td class="p-3">{{ $review['photo_total'] }}</td>
                                    <td class="p-3">Primele {{ count($review['photos']) }}, în ordinea feedului</td>
                                @else
                                    <td class="max-w-xs break-words p-3">{{ \Illuminate\Support\Str::limit(strip_tags($review['values'][$field]), 180) ?: '—' }}</td>
                                    <td class="max-w-xs break-words p-3">
                                        @if (isset($values[$field]))
                                            {{ \Illuminate\Support\Str::limit((string) ($review['attributes'][$values[$field]] ?? ''), 180) ?: '—' }}
                                        @elseif ($field === 'price_eur')
                                            {{ $review['attributes']['price_value'] === null ? 'La cerere' : $review['attributes']['price_value'].' '.$review['attributes']['currency'] }}
                                        @elseif ($field === 'currency')
                                            {{ $review['attributes']['price_value'] === null ? '—' : $review['attributes']['currency'] }}
                                        @elseif ($field === 'status')
                                            Nepublicat
                                        @elseif ($field === 'id')
                                            {{ $externalId }}
                                        @else
                                            {{ $review['catalog'][$field] ?? '—' }}
                                        @endif
                                    </td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <form method="POST" action="{{ route('admin.dealer-feeds.trials.store', $source) }}" class="space-y-2">
                @csrf
                <input type="hidden" name="external_id" value="{{ $externalId }}">
                @if ($localityId)
                    <input type="hidden" name="locality_id" value="{{ $localityId }}">
                @endif
                <input type="hidden" name="fingerprint" value="{{ $review['fingerprint'] }}">
                <x-primary-button :disabled="$review['errors'] !== []" class="disabled:cursor-not-allowed disabled:opacity-50">
                    Importă de probă
                </x-primary-button>
                @if ($review['errors'] !== [])
                    <p class="text-sm text-slate-600">Corectează erorile afișate deasupra tabelului ca să poți porni proba.</p>
                @endif
            </form>
        @endif
    </div>
@endsection
