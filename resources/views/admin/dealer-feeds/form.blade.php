@extends('admin.layout')

@section('content')
    @php
        $check = $source->last_check ?? [];
        $mapping = $source->mapping ?? [];
        $columns = $check['columns'] ?? [];
        $suggested = $check['suggested'] ?? [];
        $hasSnapshot = isset($check['item_count'], $check['run_id']);
    @endphp

    <div class="mx-auto max-w-5xl space-y-5 px-4 py-8 sm:px-6">
        <a href="{{ route('admin.dealer-feeds.index') }}" class="text-sm font-semibold text-slate-700 underline">
            Înapoi la Import anunțuri
        </a>
        <div class="flex flex-wrap items-center justify-between gap-2">
            <h1 class="text-2xl font-bold text-slate-800">{{ $source->exists ? 'Editează sursa' : 'Adaugă sursă' }}</h1>
            @if ($source->exists)
                <a href="#previzualizare-feed" class="text-sm font-semibold text-slate-700 underline">Mergi la previzualizare și probă</a>
            @endif
        </div>

        @if (session('status'))
            <p role="status" class="rounded-lg bg-emerald-50 p-3 text-sm text-emerald-800">{{ session('status') }}</p>
        @endif
        @if ($errors->any())
            <ul role="alert" class="rounded-lg bg-red-50 p-3 text-sm text-red-800">
                @foreach ($errors->all() as $message)
                    <li>{{ $message }}</li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ $source->exists ? route('admin.dealer-feeds.update', $source) : route('admin.dealer-feeds.store') }}"
              class="space-y-4 rounded-xl border border-slate-200 bg-white p-5">
            @csrf
            @if ($source->exists)
                @method('PATCH')
            @endif

            <div>
                @if ($source->exists)
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <p class="text-sm font-medium text-slate-700">Dealer</p>
                        <a href="{{ route('admin.users.dealers.create') }}" target="_blank" rel="noopener" class="text-sm font-semibold text-slate-700 underline">
                            Creează dealer
                        </a>
                    </div>
                    <p class="mt-1 text-sm font-semibold text-slate-800">{{ $source->dealer->name }}</p>
                @else
                    <div class="flex flex-wrap items-center justify-between gap-2">
                        <x-input-label for="dealer_id" value="Dealer" />
                        <a href="{{ route('admin.users.dealers.create') }}" target="_blank" rel="noopener" class="text-sm font-semibold text-slate-700 underline">
                            Creează dealer
                        </a>
                    </div>
                    <select id="dealer_id" name="dealer_id" class="mt-1 block w-full rounded-md border-slate-300" required>
                        <option value="">Alege dealerul</option>
                        @foreach ($dealers as $dealer)
                            <option value="{{ $dealer->id }}" @selected((string) old('dealer_id') === (string) $dealer->id)>
                                {{ $dealer->name }}
                            </option>
                        @endforeach
                    </select>
                @endif
            </div>
            <div>
                <x-input-label for="profile" value="Model de import" />
                <select id="profile" name="profile" class="mt-1 block w-full rounded-md border-slate-300" required>
                    @foreach ($profiles as $key => $profile)
                        <option value="{{ $key }}" @selected(old('profile', $source->profile) === $key)>{{ $profile['label'] }}</option>
                    @endforeach
                </select>
                <p class="mt-1 text-sm text-slate-600">Modelul aplică automat maparea. Dacă îl schimbi, sursa preia setările noului model.</p>
            </div>
            <div>
                <x-input-label for="name" value="Numele sursei (opțional)" />
                <x-text-input id="name" name="name" class="mt-1 block w-full" :value="old('name', $source->name)" maxlength="120" />
            </div>
            <div>
                <x-input-label for="url" value="URL feed" />
                <x-text-input id="url" name="url" type="url" class="mt-1 block w-full" :value="$source->url" maxlength="4096" autocomplete="off" spellcheck="false" required />
            </div>

            @if ($columns !== [])
                <fieldset class="space-y-3 border-t border-slate-200 pt-4">
                    <legend class="px-1 font-semibold text-slate-800">Maparea câmpurilor</legend>
                    <p class="text-sm text-slate-600">
                        {{ $hasSnapshot ? 'Corecțiile salvate se aplică fișierului verificat.' : 'Testează feedul pentru a salva fișierul folosit la previzualizare.' }}
                    </p>
                    <div class="grid gap-3 sm:grid-cols-2">
                        @foreach ($fields as $field => $label)
                            @php
                                $selected = old('mapping.'.$field, $mapping[$field] ?? $automaticMapping) ?? '';
                                $options = $selected !== '' && $selected !== $automaticMapping && ! in_array($selected, $columns, true)
                                    ? [...$columns, $selected]
                                    : $columns;
                            @endphp
                            <div>
                                <x-input-label :for="'mapping_'.$field" :value="$label" />
                                <select id="mapping_{{ $field }}" name="mapping[{{ $field }}]" class="mt-1 block w-full rounded-md border-slate-300">
                                    <option value="{{ $automaticMapping }}" @selected($selected === $automaticMapping)>
                                        Automat{{ !empty($suggested[$field]) ? ' · '.$suggested[$field] : '' }}
                                    </option>
                                    <option value="" @selected($selected === '')>Nemapat</option>
                                    @foreach ($options as $column)
                                        <option value="{{ $column }}" @selected($selected === $column)>
                                            {{ $column }}{{ in_array($column, $columns, true) ? '' : ' (lipsește din feed)' }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endforeach
                    </div>
                </fieldset>
            @endif

            <x-primary-button>Salvează sursa</x-primary-button>
        </form>

        @if ($source->exists)
            @if ($latestImport)
                <section id="status-import" class="scroll-mt-6 space-y-2 rounded-xl border border-slate-200 bg-white p-5">
                    <div class="flex items-center justify-between gap-3">
                        <h2 class="font-semibold text-slate-800">{{ $latestImport->importLabel() }}</h2>
                        <a class="text-sm font-semibold text-slate-700 underline" href="{{ route('admin.dealer-feeds.edit', $source) }}#status-import">Actualizează starea</a>
                    </div>
                    @if ($latestImport->import_error)
                        <p role="alert" class="text-sm text-red-700">{{ $latestImport->import_error }}</p>
                    @endif
                    @if ($latestImport->import_status === 'prepared')
                        <p class="text-sm text-slate-700">
                            {{ $latestImport->import_published }} publicate · {{ $latestImport->import_waiting }} în așteptare ·
                            {{ $latestImport->import_failed }} cu erori · {{ $latestImport->import_unchanged }} rămase neschimbate
                            @if ($latestImport->import_trashed) · {{ $latestImport->import_trashed }} în Coș @endif
                        </p>
                        @if ($latestImport->sync_summary !== null)
                            @include('admin.dealer-feeds.sync-counts', ['counts' => $latestImport->sync_summary])
                            @if ($galleryWaiting)
                                <p class="text-sm text-slate-700">{{ $galleryWaiting }} galerii în așteptare sau procesare</p>
                            @endif
                            @if ($galleryFailed->isNotEmpty())
                                <ul class="space-y-1 text-sm text-red-700">
                                    @foreach ($galleryFailed as $item)
                                        <li><strong>ID {{ $item->external_id }}</strong> · {{ $item->sync_gallery['error'] }}</li>
                                    @endforeach
                                </ul>
                            @endif
                        @endif
                    @endif
                    @if ($failedItems?->isNotEmpty())
                        <ul class="space-y-1 text-sm text-red-700">
                            @foreach ($failedItems as $item)
                                <li><strong>ID {{ $item->external_id }}</strong> · {{ $item->import_error }}</li>
                            @endforeach
                        </ul>
                        {{ $failedItems->links() }}
                    @endif
                </section>
            @endif
            <section id="previzualizare-feed" class="scroll-mt-6 space-y-3 rounded-xl border border-slate-200 bg-white p-5">
                <div class="flex flex-wrap items-center justify-between gap-3">
                    <h2 class="font-semibold text-slate-800">Previzualizare și probă</h2>
                    <form method="POST" action="{{ route('admin.dealer-feeds.check', $source) }}">
                        @csrf
                        <x-primary-button>{{ $hasSnapshot ? 'Descarcă și verifică din nou' : 'Testează feedul salvat' }}</x-primary-button>
                    </form>
                </div>
                @if ($hasSnapshot)
                    <p class="text-sm font-medium text-emerald-800 dark:text-emerald-300">Fișier verificat și salvat pentru această probă.</p>
                    <form method="GET" action="{{ route('admin.dealer-feeds.edit', $source) }}#simulare-import">
                        <input type="hidden" name="simuleaza" value="1">
                        <x-primary-button>{{ $latestImport ? 'Verifică modificările' : 'Simulează importul complet' }}</x-primary-button>
                    </form>
                    <form method="GET" action="{{ route('admin.dealer-feeds.trials.review', $source) }}" class="flex flex-wrap items-end gap-2">
                        <div>
                            <x-input-label for="external_id" value="ID extern al anunțului" />
                            <x-text-input id="external_id" name="external_id" class="mt-1 block w-full" maxlength="191" required />
                        </div>
                        <x-primary-button>Verifică un anunț</x-primary-button>
                    </form>
                @elseif (isset($check['item_count']))
                    <p class="text-sm text-amber-800 dark:text-amber-300">Testează feedul din nou pentru a salva fișierul necesar probei.</p>
                @endif
                @if ($simulation)
                    @include('admin.dealer-feeds.simulation-result', $simulation)
                @endif
                @if ($trials->isNotEmpty())
                    <ul class="space-y-1 text-sm">
                        @foreach ($trials as $trial)
                            <li>
                                <a class="font-semibold text-slate-700 underline" href="{{ route('admin.dealer-feeds.trials.show', [$source, $trial]) }}">
                                    Rezultat probă · ID {{ $trial->listing->external_id }}
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                @if (isset($check['error']))
                    <p role="alert" class="text-sm text-red-700">{{ $check['error'] }}</p>
                @elseif (isset($check['item_count']))
                    <p class="text-sm font-semibold text-slate-800">{{ $check['item_count'] }} anunțuri citite din {{ strtoupper($profiles[$source->profile]['format'] ?? 'xml') }}</p>
                    @if (($check['tested_mapping'] ?? []) !== $mapping)
                        <p class="text-sm text-amber-800">Maparea a fost schimbată. Retestează feedul pentru rezultate actuale.</p>
                    @endif
                    @if ($check['item_count'] === 0)
                        <p class="text-sm text-amber-800">Feedul nu conține anunțuri.</p>
                    @endif
                    <ul class="space-y-1 text-sm text-slate-700">
                        @foreach ($check['missing'] as $field => $count)
                            <li>
                                {{ $fields[$field] }} lipsește la {{ $count }} anunțuri.
                                @if (in_array($field, ['title', 'description'], true))
                                    Se completează automat din datele disponibile.
                                @endif
                            </li>
                        @endforeach
                        @if ($check['duplicate_ids'])
                            <li>{{ $check['duplicate_ids'] }} anunțuri au un ID extern repetat.</li>
                        @endif
                        @if ($check['over_photo_limit'])
                            <li>{{ $check['over_photo_limit'] }} anunțuri depășesc limita de {{ \App\Support\ServiceImageStorage::MAX_IMAGES }} fotografii.</li>
                        @endif
                    </ul>
                    @foreach ($check['sample'] as $item)
                        <details class="rounded-md border border-slate-200 p-3 text-sm">
                            <summary class="cursor-pointer font-semibold text-slate-800">
                                {{ $item['title'] ?: 'Anunț '.$loop->iteration }}
                                @if ($item['id']) <span class="text-slate-500">· ID {{ $item['id'] }}</span> @endif
                            </summary>
                            <dl class="mt-3 grid gap-2 sm:grid-cols-2">
                                @if (!empty($item['branch_name']))
                                    <div>
                                        <dt class="font-semibold">Punct de vânzare în feed</dt>
                                        <dd class="break-words text-slate-600">{{ $item['branch_name'] }}</dd>
                                    </div>
                                @endif
                                @foreach ($fields as $field => $label)
                                    @php($value = $item[$field] ?? '')
                                    <div>
                                        <dt class="font-semibold">{{ $label }}</dt>
                                        <dd class="break-words text-slate-600">{{ $value === '' ? '—' : $value }}</dd>
                                    </div>
                                @endforeach
                            </dl>
                            @if ($hasSnapshot && $item['id'])
                                <a class="mt-3 inline-block font-semibold text-slate-700 underline" href="{{ route('admin.dealer-feeds.trials.review', ['source' => $source, 'external_id' => $item['id']]) }}">
                                    Verifică acest anunț
                                </a>
                            @endif
                        </details>
                    @endforeach
                @else
                    <p class="text-sm text-slate-600">Testează linkul salvat pentru a vedea câmpurile și propunerile de mapare.</p>
                @endif
                @if ($source->last_checked_at)
                    <p class="text-xs text-slate-500">Ultima verificare {{ $source->last_checked_at->format('d.m.Y H:i') }}</p>
                @endif
            </section>
        @endif
    </div>
@endsection
