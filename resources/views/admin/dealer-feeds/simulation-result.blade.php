<section id="simulare-import" class="scroll-mt-6 space-y-3 rounded-md border border-slate-200 p-4 text-sm">
    <h3 class="font-semibold text-slate-800">Rezultatul simulării</h3>
    @if ($simulationError)
        <p role="alert" class="text-red-700">{{ $simulationError }}</p>
    @else
        @if ($report['blocked'])
            <p class="font-semibold text-red-700">Simularea a găsit blocaje. Verifică motivele de mai jos.</p>
        @else
            <p class="font-semibold text-emerald-800">Toate anunțurile au trecut verificarea datelor.</p>
        @endif
        <p class="text-slate-700">
            {{ $report['total'] }} verificate · {{ $report['new'] }} noi și valide · {{ $report['linked'] }} deja asociate și valide · {{ $report['blocked'] }} blocate
        </p>
        @if ($report['is_sync'])
            @include('admin.dealer-feeds.sync-counts', ['counts' => $report['change_counts']])
            @if ($report['changes'])
                <details class="text-slate-700">
                    <summary class="cursor-pointer font-semibold">Modificări de aplicat</summary>
                    <ul class="mt-2 space-y-1">
                        @foreach ($report['changes'] as $change)
                            <li>
                                <strong>ID {{ $change['id'] }} · {{ $change['title'] }}</strong>
                                @if ($change['action'] === 'withdrawn') · Se retrage conform statusului din feed @endif
                                @if ($change['action'] === 'reactivated') · Se reactivează conform statusului din feed @endif
                                @if ($change['details']) · {{ implode(' · ', $change['details']) }} @endif
                            </li>
                        @endforeach
                    </ul>
                </details>
            @endif
            @if ($report['missing'])
                <details class="text-slate-700">
                    <summary class="cursor-pointer font-semibold">Anunțuri care lipsesc din feed</summary>
                    <p>Aceste anunțuri rămân publicate.</p>
                    <ul class="mt-2 space-y-1">
                        @foreach ($report['missing'] as $missing)
                            <li>ID {{ $missing['id'] }} · {{ $missing['title'] }}</li>
                        @endforeach
                    </ul>
                </details>
            @endif
        @endif
        @if ($report['locations'] || $report['branches'])
            @include('admin.dealer-feeds.listing-locations')
        @endif
        <p class="text-slate-600">Simularea verifică datele și asocierile cu anunțurile existente. Pozele nu sunt descărcate.</p>
        @php($changes = $report['change_counts'])
        @php($hasChanges = $changes['new'] + $changes['updated'] + $changes['withdrawn'] + $changes['reactivated'] > 0)
        @if ($report['blocked'] === 0 && $hasChanges && $latestImport?->import_status !== 'queued' && ! $latestImport?->import_waiting && ! $galleryWaiting)
            <form method="POST" action="{{ route('admin.dealer-feeds.import', $source) }}"
                  data-confirm-message="{{ $report['is_sync'] ? 'Aplici modificările din această verificare?' : 'Pornești importul. Anunțurile vor fi publicate automat după procesarea pozelor. Continui?' }}"
                  onsubmit="return confirm(this.dataset.confirmMessage)">
                @csrf
                <input type="hidden" name="run_id" value="{{ $report['run_id'] }}">
                <input type="hidden" name="fingerprint" value="{{ $report['fingerprint'] }}">
                <x-primary-button>{{ $report['is_sync'] ? 'Aplică sincronizarea' : 'Importă toate' }}</x-primary-button>
            </form>
        @endif

        @if ($report['issues'])
            <details @if ($report['blocked'] && ! $report['locations'] && ! $report['branches']) open @endif>
                <summary class="cursor-pointer font-semibold text-slate-800">Erori și avertismente</summary>
                <ul class="mt-2 space-y-2">
                    @foreach ($report['issues'] as $issue)
                        <li class="break-words border-t border-slate-200 pt-2">
                            <p class="font-semibold text-slate-800">
                                Rândul {{ $issue['row'] }}
                                @if ($issue['id'] !== '') · ID {{ \Illuminate\Support\Str::limit($issue['id'], 191) }} @endif
                                @if ($issue['title'] !== '') · {{ \Illuminate\Support\Str::limit($issue['title'], 90) }} @endif
                            </p>
                            @foreach ($issue['errors'] as $error)
                                <p class="text-red-700">{{ $error }}</p>
                            @endforeach
                            @foreach ($issue['warnings'] as $warning)
                                <p class="text-amber-800">{{ $warning }}</p>
                            @endforeach
                        </li>
                    @endforeach
                </ul>
            </details>
        @endif
    @endif
</section>
