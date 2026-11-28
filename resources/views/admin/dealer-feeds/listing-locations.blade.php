<form id="listing-locations" method="POST" action="{{ route('admin.dealer-feeds.update', $source) }}" class="space-y-3 rounded-md border border-slate-200 p-3">
    @csrf
    @method('PATCH')
    <input type="hidden" name="run_id" value="{{ $report['run_id'] }}">
    <input type="hidden" name="fingerprint" value="{{ $report['fingerprint'] }}">
    <input type="hidden" name="location_choices" value="{}">
    @php($hasMissingLocation = collect($report['branches'] + $report['locations'])->contains(fn ($item) => $item['locality_id'] === null))
    <h4 class="font-semibold text-slate-800">{{ $hasMissingLocation ? 'Locații de completat' : 'Locații pentru import' }}</h4>
    @if ($hasMissingLocation)
        <p class="text-slate-600">Alege județul și localitatea pentru rândurile necompletate, apoi salvează.</p>
    @endif
    <div class="space-y-3">
        @foreach ($report['branches'] + $report['locations'] as $key => $item)
            <fieldset data-location-key="{{ $key }}" data-locality="{{ $item['locality_id'] }}" @if (!isset($item['count'])) data-listing-location @endif class="border-t border-slate-200 pt-2">
                <legend class="break-words font-semibold text-slate-800">
                    {{ $item['title'] ?: 'Anunț' }} · {{ isset($item['count']) ? $item['count'].' anunțuri' : 'ID '.$item['id'] }}
                </legend>
                <div class="grid gap-2 sm:grid-cols-2">
                    <div>
                        <label for="county-{{ $key }}" class="block text-slate-700">Județ</label>
                        <select id="county-{{ $key }}" data-county class="mt-1 w-full rounded-md border-slate-300 text-sm">
                            <option value="">Alege județul</option>
                            @foreach ($locationCatalog as $countyId => $county)
                                <option value="{{ $countyId }}" @selected($item['county_id'] === $countyId)>{{ $county['name'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label for="locality-{{ $key }}" class="block text-slate-700">Localitate</label>
                        <select id="locality-{{ $key }}" data-locality-select class="mt-1 w-full rounded-md border-slate-300 text-sm"></select>
                    </div>
                </div>
                @if ($key === array_key_first($report['locations']) && count($report['locations']) > 1)
                    <label class="mt-3 flex items-center gap-2 font-medium text-slate-800">
                        <input type="checkbox" data-copy-location disabled class="rounded border-slate-300 disabled:opacity-50">
                        Folosește județul și localitatea de aici pentru toate anunțurile din listă
                    </label>
                @endif
            </fieldset>
        @endforeach
    </div>
    <x-primary-button>Salvează locațiile și verifică</x-primary-button>
</form>

<script>
    (() => {
        const form = document.getElementById('listing-locations');
        const catalog = @js($locationCatalog->all());
        const rows = [...form.querySelectorAll('[data-location-key]')];

        const showLocalities = (row, selected = '') => {
            const county = row.querySelector('[data-county]').value;
            const locality = row.querySelector('[data-locality-select]');
            locality.replaceChildren(new Option(county ? 'Alege localitatea' : 'Alege întâi județul', ''));
            for (const [id, name] of catalog[county]?.cities ?? []) {
                locality.add(new Option(name, id));
            }
            locality.value = selected;
            locality.disabled = !county;
        };

        for (const row of rows) {
            showLocalities(row, row.dataset.locality);
            row.querySelector('[data-county]').addEventListener('change', () => showLocalities(row));
        }

        const copyLocation = form.querySelector('[data-copy-location]');
        if (copyLocation) {
            const [first, ...remaining] = form.querySelectorAll('[data-listing-location]');
            const firstCounty = first.querySelector('[data-county]');
            const firstLocality = first.querySelector('[data-locality-select]');
            copyLocation.disabled = !firstLocality.value;

            form.addEventListener('change', (event) => {
                if (event.target === copyLocation && copyLocation.checked) {
                    for (const row of remaining) {
                        row.querySelector('[data-county]').value = firstCounty.value;
                        showLocalities(row, firstLocality.value);
                    }
                } else if (event.target.matches('[data-county], [data-locality-select]')) {
                    copyLocation.checked = false;
                    copyLocation.disabled = !firstLocality.value;
                }
            });
        }

        form.addEventListener('submit', () => {
            const choices = {};
            for (const row of rows) {
                const locality = row.querySelector('[data-locality-select]').value;
                if (locality !== row.dataset.locality) {
                    choices[row.dataset.locationKey] = locality ? Number(locality) : null;
                }
            }
            form.elements.location_choices.value = JSON.stringify(choices);
        });
    })();
</script>
