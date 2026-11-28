@extends('admin.layout')

@section('content')
    <div class="mx-auto max-w-6xl space-y-7 px-4 py-8 sm:px-6">
        <header class="flex flex-wrap items-center justify-between gap-4">
            <h1 class="text-3xl font-bold tracking-tight text-slate-900 dark:text-slate-100">Import anunțuri</h1>
            <a href="{{ route('admin.users.dealers.create') }}" target="_blank" rel="noopener"
               class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-4 py-2.5 text-sm font-semibold text-slate-700 transition hover:bg-slate-50">
                Adaugă dealer
            </a>
        </header>

        <section aria-labelledby="start-feed" class="overflow-hidden rounded-2xl border border-slate-200 bg-white shadow-sm">
            <div class="border-b border-slate-200 px-5 py-4 sm:px-7">
                <h2 id="start-feed" class="text-lg font-semibold text-slate-900 dark:text-slate-100">Testează un feed</h2>
                <p class="mt-1 text-sm text-slate-600">Salvezi sursa și mergi direct la mapare și previzualizare.</p>
            </div>

            <form method="POST" action="{{ route('admin.dealer-feeds.store') }}" class="space-y-5 p-5 sm:p-7">
                @csrf
                <input type="hidden" name="test_feed" value="1">
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="dealer_id" value="Dealer" />
                        <select id="dealer_id" name="dealer_id" required class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm">
                            <option value="">Alege dealerul</option>
                            @foreach ($dealers as $dealer)
                                <option value="{{ $dealer->id }}" @selected((string) old('dealer_id') === (string) $dealer->id)>
                                    {{ $dealer->name }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('dealer_id')" class="mt-1" />
                    </div>
                    <div>
                        <x-input-label for="profile" value="Model de import" />
                        <select id="profile" name="profile" required class="mt-1.5 block w-full rounded-lg border-slate-300 text-sm">
                            @foreach ($profiles as $key => $profile)
                                <option value="{{ $key }}" @selected(old('profile', \App\Models\DealerFeedSource::DEFAULT_PROFILE) === $key)>
                                    {{ $profile['label'] }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('profile')" class="mt-1" />
                    </div>
                </div>
                <div>
                    <x-input-label for="url" value="Linkul complet al feedului" />
                    <x-text-input id="url" name="url" type="url" placeholder="https://..."
                                  class="mt-1.5 block w-full rounded-lg text-sm" maxlength="4096" autocomplete="off" spellcheck="false" required />
                    <x-input-error :messages="$errors->get('url')" class="mt-1" />
                </div>
                <div class="flex flex-wrap items-center justify-between gap-3 border-t border-slate-100 pt-4">
                    <p class="text-sm text-slate-600">Fișierul verificat este păstrat temporar pentru previzualizare.</p>
                    <button type="submit" class="rounded-lg bg-[#C81424] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#A7101E]">
                        Testează feedul
                    </button>
                </div>
            </form>
        </section>

        @if ($sources->isNotEmpty())
            <section aria-labelledby="feed-sources" class="space-y-3">
                <h2 id="feed-sources" class="text-xl font-semibold text-slate-900 dark:text-slate-100">Surse configurate</h2>
                <ul class="divide-y divide-slate-100 rounded-xl border border-slate-200 bg-white text-sm">
                    @foreach ($sources as $source)
                        <li class="flex flex-wrap items-center justify-between gap-2 px-4 py-3">
                            <div>
                                <p class="font-semibold text-slate-900">{{ $source->dealer->company_name ?: $source->dealer->name }}</p>
                                <p class="text-slate-600">{{ $profiles[$source->profile]['label'] ?? $source->profile }} · {{ $source->name }}</p>
                            </div>
                            @include('admin.dealer-feeds.source-actions', ['source' => $source, 'importRun' => null])
                        </li>
                    @endforeach
                </ul>
                {{ $sources->links() }}
            </section>
        @endif

        <section aria-labelledby="feed-history" class="space-y-3">
            <h2 id="feed-history" class="text-xl font-semibold text-slate-900 dark:text-slate-100">Istoric importuri și sincronizări</h2>
            @if (session('success'))
                <p role="status" class="text-sm font-semibold text-emerald-800 dark:text-emerald-300">{{ session('success') }}</p>
            @endif
            @if (session('error'))
                <p role="alert" class="text-sm font-semibold text-red-700 dark:text-red-300">{{ session('error') }}</p>
            @endif
            <div class="overflow-x-auto rounded-xl border border-slate-200 bg-white">
                <table class="w-full text-left text-sm">
                    <thead class="bg-slate-50 text-xs font-semibold text-slate-600">
                        <tr>
                            <th scope="col" class="px-4 py-3">Dealer</th>
                            <th scope="col" class="px-4 py-3">Model</th>
                            <th scope="col" class="px-4 py-3">Data</th>
                            <th scope="col" class="px-4 py-3">Rezultat</th>
                            <th scope="col" class="px-4 py-3"><span class="sr-only">Acțiuni</span></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100 text-slate-700">
                        @foreach ($runs as $run)
                            <tr>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-slate-900 dark:text-slate-100">{{ $run->result['dealer_name'] ?? ($run->source->dealer->company_name ?: $run->source->dealer->name) }}</span>
                                    <div class="text-xs text-slate-500">{{ $run->result['source_name'] ?? $run->source->name }}</div>
                                </td>
                                <td class="px-4 py-3">{{ $profiles[$run->profile]['label'] ?? $run->profile }}</td>
                                <td class="whitespace-nowrap px-4 py-3">{{ $run->created_at->format('d.m.Y H:i') }}</td>
                                <td class="px-4 py-3">
                                    <span class="font-semibold text-emerald-800 dark:text-emerald-300">{{ $run->importLabel() }}</span>
                                    @if ($run->result['automatic'] ?? false)
                                        <span class="text-xs text-slate-600">· Automat</span>
                                    @endif
                                    @if ($run->sync_summary)
                                        @include('admin.dealer-feeds.sync-counts', ['counts' => $run->sync_summary])
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-4 py-3 text-right">
                                    @include('admin.dealer-feeds.source-actions', ['source' => $run->source, 'importRun' => $run])
                                </td>
                            </tr>
                        @endforeach
                        @if ($runs->isEmpty())
                            <tr><td colspan="5" class="px-4 py-6 text-slate-600">Importurile și sincronizările reușite vor apărea aici.</td></tr>
                        @endif
                    </tbody>
                </table>
            </div>
            {{ $runs->links() }}
        </section>
    </div>
@endsection
