@extends('admin.layout')

@section('content')
<div class="max-w-[1536px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Vânzări</h1>
            <p class="text-sm text-slate-500 mt-1">Declarațiile proprietarilor la ultima dezactivare a fiecărui anunț.</p>
        </div>
        <form method="POST" action="{{ route('admin.deactivation-feedback.clear') }}" onsubmit="return confirm('Ștergi toate statisticile de vânzări? Acțiunea nu se poate anula.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="w-full rounded-lg border border-red-200 bg-red-50 px-4 py-2 text-sm font-bold text-red-700 transition hover:bg-red-100 sm:w-auto">
                Șterge statisticile
            </button>
        </form>
    </div>

    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800">
            {{ session('error') }}
        </div>
    @endif

    <div class="mb-5 flex flex-wrap gap-2" aria-label="Perioada statisticilor">
        @foreach([
            'today' => 'Azi',
            'yesterday' => 'Ieri',
            '7d' => '7 zile',
            '30d' => '30 zile',
            '90d' => '90 zile',
            'all' => 'Tot timpul',
        ] as $periodKey => $periodLabel)
            <a href="{{ route('admin.deactivation-feedback.index', array_merge(request()->except(['period', 'from', 'to', 'page']), ['period' => $periodKey])) }}"
               @if($period === $periodKey) aria-current="page" @endif
               class="rounded-full border px-4 py-2 text-sm font-bold transition focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#C81424] {{ $period === $periodKey ? 'border-[#C81424] bg-[#C81424] text-white' : 'border-slate-200 bg-white text-slate-600 hover:border-[#C81424] hover:text-[#C81424]' }}">
                {{ $periodLabel }}
            </a>
        @endforeach
    </div>

    <form method="GET" class="mb-6 flex flex-wrap items-end gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
        <input type="hidden" name="period" value="custom">
        @if(request()->filled('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
        @if(request()->filled('answer')) <input type="hidden" name="answer" value="{{ request('answer') }}"> @endif
        @if(request()->filled('sold_on')) <input type="hidden" name="sold_on" value="{{ request('sold_on') }}"> @endif
        @if(request()->filled('completion_status')) <input type="hidden" name="completion_status" value="{{ request('completion_status') }}"> @endif
        @if($showExcluded) <input type="hidden" name="show_excluded" value="1"> @endif
        <label class="text-xs font-bold text-slate-600">De la
            <input type="date" name="from" required value="{{ $period === 'custom' ? $from?->format('Y-m-d') : '' }}" class="mt-1 block rounded-lg border-slate-200 text-sm">
        </label>
        <label class="text-xs font-bold text-slate-600">Până la
            <input type="date" name="to" required value="{{ $period === 'custom' ? $toExclusive->copy()->subDay()->format('Y-m-d') : '' }}" class="mt-1 block rounded-lg border-slate-200 text-sm">
        </label>
        <button type="submit" class="rounded-lg bg-slate-800 px-4 py-2.5 text-sm font-bold text-white hover:bg-slate-900">Aplică intervalul</button>
        <span class="text-xs text-slate-500">Zile calendaristice, ora României.</span>
    </form>

    <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
        @foreach([
            ['label' => 'Vânzări declarate', 'value' => number_format($stats['sold'], 0, ',', '.'), 'class' => 'text-emerald-700', 'detail' => $previousSold !== null ? 'Perioada anterioară: ' . number_format($previousSold, 0, ',', '.') . ' (' . ($stats['sold'] - $previousSold >= 0 ? '+' : '') . number_format($stats['sold'] - $previousSold, 0, ',', '.') . ')' : 'Ultimul răspuns per anunț'],
            ['label' => 'Pe iaAuto', 'value' => $stats['iaauto'], 'class' => 'text-[#C81424]'],
            ['label' => 'Pe alt site', 'value' => $stats['other_site'], 'class' => 'text-amber-700'],
            ['label' => 'Răspunsuri completate', 'value' => $stats['response_rate'] === null ? '—' : number_format($stats['response_rate'], 1, ',', '.') . '%', 'class' => 'text-blue-700', 'detail' => $stats['completed'] . ' din ' . $stats['total'] . ' dezactivări'],
            ['label' => 'Durată mediană', 'value' => $stats['median_days'] === null ? '—' : number_format($stats['median_days'], 1, ',', '.') . ' zile', 'class' => 'text-slate-800'],
            ['label' => 'Durată medie', 'value' => $stats['average_days'] === null ? '—' : number_format((float) $stats['average_days'], 1, ',', '.') . ' zile', 'class' => 'text-slate-800'],
            ['label' => 'Nu era vândută', 'value' => $stats['not_sold'], 'class' => 'text-slate-600'],
            ['label' => 'Omise', 'value' => $stats['skipped'], 'class' => 'text-slate-500'],
        ] as $stat)
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-black {{ $stat['class'] }}">{{ $stat['value'] }}</p>
                @if(isset($stat['detail'])) <p class="mt-1 text-xs text-slate-500">{{ $stat['detail'] }}</p> @endif
            </div>
        @endforeach
    </div>

    @if($stats['sold'] > 0 && $stats['sold'] < 10)
        <p class="mb-6 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3 text-xs font-semibold text-amber-800">
            Eșantion mic: {{ $stats['sold'] }} {{ $stats['sold'] === 1 ? 'vânzare declarată' : 'vânzări declarate' }} în interval. Duratele și clasamentul modelelor pot varia mult la următoarele răspunsuri.
        </p>
    @endif

    <div class="mb-6 grid gap-4 lg:grid-cols-[minmax(0,2fr)_minmax(260px,1fr)]">
        <section class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm" aria-label="Evoluția vânzărilor declarate">
            <h2 class="font-bold text-slate-800">Evoluția vânzărilor declarate</h2>
            <p class="mt-1 text-xs text-slate-500">{{ $chartLimited ? 'Ultimele 12 luni din intervalul selectat' : 'Pentru intervalul selectat' }} · ultima dezactivare per anunț</p>
            @php $chartMax = max(1, ...array_column($chart, 'total')); @endphp
            <div class="mt-5 flex h-44 items-end gap-1.5 overflow-x-auto border-b border-slate-200 pb-1">
                @foreach($chart as $bucket)
                    <div class="flex h-full min-w-[14px] flex-1 items-end" role="img" aria-label="{{ $bucket['label'] }}: {{ $bucket['total'] }} vânzări declarate" title="{{ $bucket['label'] }}: {{ $bucket['total'] }} vânzări declarate">
                        <div class="w-full rounded-t bg-[#C81424] {{ $bucket['total'] ? '' : 'opacity-15' }}" style="height: {{ max(3, round(100 * $bucket['total'] / $chartMax)) }}%"></div>
                    </div>
                @endforeach
            </div>
            <div class="mt-2 flex justify-between text-xs text-slate-500">
                <span>{{ $chart[0]['label'] ?? '—' }}</span><span>{{ $chart[count($chart) - 1]['label'] ?? '—' }}</span>
            </div>
        </section>
        <section class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
            <h2 class="font-bold text-slate-800">Modele declarate vândute</h2>
            <p class="mt-1 text-xs text-slate-500">Top 5 în intervalul ales</p>
            <ol class="mt-4 space-y-3">
                @forelse($topModels as $model)
                    <li class="flex items-center justify-between gap-3 border-b border-slate-100 pb-2 text-sm">
                        <span class="font-semibold text-slate-700">{{ $model->brand_name }} {{ $model->model_name }}</span>
                        <span class="font-black text-[#C81424]">{{ $model->total }}</span>
                    </li>
                @empty
                    <li class="text-sm text-slate-500">Nu există încă date în această perioadă.</li>
                @endforelse
            </ol>
        </section>
    </div>

    <p class="mb-4 text-xs text-slate-500">Cifrele și graficele folosesc perioada și căutarea, fără înregistrările excluse. Filtrele de răspuns, sursă și completare de mai jos restrâng doar tabelul. „Vândut” înseamnă declarat de proprietar la dezactivare; durata pornește de la publicarea inițială. Data exactă a tranzacției nu este cunoscută.</p>

    <form method="GET" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6">
        <input type="hidden" name="period" value="{{ $period }}">
        @if($period === 'custom')
            <input type="hidden" name="from" value="{{ $from->format('Y-m-d') }}">
            <input type="hidden" name="to" value="{{ $toExclusive->copy()->subDay()->format('Y-m-d') }}">
        @endif
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Utilizator, anunț, marcă..." class="rounded-lg border-slate-200 text-sm xl:col-span-2">
        <select name="answer" class="rounded-lg border-slate-200 text-sm">
            <option value="sold" @selected($answerFilter === 'sold')>Vânzări confirmate</option>
            <option value="not_sold" @selected($answerFilter === 'not_sold')>Nu era vândută</option>
            <option value="skipped" @selected($answerFilter === 'skipped')>Omise</option>
            <option value="all" @selected($answerFilter === 'all')>Toate răspunsurile</option>
        </select>
        <select name="sold_on" class="rounded-lg border-slate-200 text-sm">
            <option value="">Toate sursele</option>
            <option value="iaauto" @selected(request('sold_on') === 'iaauto')>Pe iaAuto</option>
            <option value="other_site" @selected(request('sold_on') === 'other_site')>Pe alt site</option>
        </select>
        <select name="completion_status" class="rounded-lg border-slate-200 text-sm">
            <option value="">Completare: toate</option>
            <option value="completed" @selected(request('completion_status') === 'completed')>Completate</option>
            <option value="skipped" @selected(request('completion_status') === 'skipped')>Omise</option>
        </select>
        <label class="flex items-center gap-2 text-sm font-semibold text-slate-600">
            <input type="checkbox" name="show_excluded" value="1" @checked($showExcluded) class="rounded border-slate-300 text-[#C81424] focus:ring-[#C81424]">
            Arată și excluse
        </label>
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white hover:bg-slate-900">Filtrează</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[1200px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="p-4">Data</th>
                        <th class="p-4">Utilizator</th>
                        <th class="p-4">Anunț</th>
                        <th class="p-4">Mașină</th>
                        <th class="p-4">Răspuns</th>
                        <th class="p-4">Sursă</th>
                        <th class="p-4">Durată</th>
                        <th class="p-4">Completare</th>
                        <th class="p-4">În statistici</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($feedback as $item)
                        <tr class="align-top hover:bg-slate-50/70 {{ $item->excluded_at ? 'opacity-60' : '' }}">
                            <td class="p-4 whitespace-nowrap text-slate-500">{{ optional($item->deactivated_at)->format('d.m.Y H:i') }}</td>
                            <td class="p-4">
                                <div class="font-bold text-slate-800">{{ $item->user?->name ?: 'Utilizator șters' }}</div>
                                <div class="text-xs text-slate-500">{{ $item->user?->email ?: ('ID #' . ($item->user_id ?? '-')) }}</div>
                            </td>
                            <td class="p-4">
                                <div class="max-w-xs font-semibold text-slate-800">{{ $item->title ?: '—' }}</div>
                                @if($item->service)
                                    <a href="{{ $item->service->public_url }}" target="_blank" rel="noopener" class="mt-1 inline-block text-xs font-bold text-[#C81424] hover:underline">Vezi anunțul</a>
                                @endif
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <div class="font-semibold text-slate-800">{{ trim(($item->brand_name ?? '') . ' ' . ($item->model_name ?? '')) ?: '—' }}</div>
                                <div class="text-xs text-slate-500">{{ $item->an_fabricatie ?: '—' }} · {{ $item->km ? number_format($item->km, 0, ',', '.') . ' km' : 'km —' }}</div>
                            </td>
                            <td class="p-4">
                                @if($item->answer === 'sold')
                                    <span class="font-bold text-emerald-700">Da</span>
                                @elseif($item->answer === 'not_sold')
                                    <span class="font-bold text-slate-600">Nu</span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-4">
                                {{ $item->sold_on === 'iaauto' ? 'Pe iaAuto' : ($item->sold_on === 'other_site' ? 'Pe alt site' : '—') }}
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                @if($item->answer === 'sold' && $item->days_to_deactivate !== null)
                                    <span class="font-bold text-blue-700">
                                        {{ $item->days_to_deactivate === 0 ? 'Vândut în aceeași zi' : 'Vândut în ' . $item->days_to_deactivate . ' ' . ($item->days_to_deactivate === 1 ? 'zi' : 'zile') }}
                                    </span>
                                @else
                                    <span class="text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <span class="rounded-full px-2 py-1 text-xs font-bold {{ $item->completion_status === 'completed' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $item->completion_status === 'completed' ? 'Completat' : 'Omis' }}
                                </span>
                            </td>
                            <td class="p-4 whitespace-nowrap">
                                <form method="POST" action="{{ route('admin.deactivation-feedback.exclude', $item) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="rounded-lg border px-3 py-2 text-xs font-bold {{ $item->excluded_at ? 'border-emerald-200 text-emerald-700 hover:bg-emerald-50' : 'border-slate-200 text-slate-600 hover:border-red-300 hover:text-red-700' }}">
                                        {{ $item->excluded_at ? 'Reinclude' : 'Exclude' }}
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="9" class="p-10 text-center text-slate-500">Nu există răspunsuri pentru filtrele selectate.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($feedback->hasPages())
            <div class="border-t border-slate-100 p-4">{{ $feedback->links() }}</div>
        @endif
    </div>
</div>
@endsection
