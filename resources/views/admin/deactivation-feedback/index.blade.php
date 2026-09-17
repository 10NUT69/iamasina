@extends('admin.layout')

@section('content')
<div class="max-w-[1536px] mx-auto py-8 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-slate-800 tracking-tight">Feedback dezactivări</h1>
        <p class="text-sm text-slate-500 mt-1">Răspunsurile colectate când proprietarii își dezactivează anunțurile.</p>
    </div>

    <div class="grid grid-cols-2 gap-3 md:grid-cols-3 xl:grid-cols-6 mb-6">
        @foreach([
            ['label' => 'Total', 'value' => $stats['total'], 'class' => 'text-slate-800'],
            ['label' => 'Completate', 'value' => $stats['completed'], 'class' => 'text-blue-700'],
            ['label' => 'Vândute', 'value' => $stats['sold'], 'class' => 'text-emerald-700'],
            ['label' => 'Pe iaAuto', 'value' => $stats['iaauto'], 'class' => 'text-[#C81424]'],
            ['label' => 'Pe alt site', 'value' => $stats['other_site'], 'class' => 'text-amber-700'],
            ['label' => 'Omise', 'value' => $stats['skipped'], 'class' => 'text-slate-500'],
        ] as $stat)
            <div class="rounded-xl border border-slate-100 bg-white p-4 shadow-sm">
                <p class="text-xs font-bold uppercase tracking-wide text-slate-400">{{ $stat['label'] }}</p>
                <p class="mt-1 text-2xl font-black {{ $stat['class'] }}">{{ number_format($stat['value'], 0, ',', '.') }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" class="mb-6 grid grid-cols-1 gap-3 rounded-xl border border-slate-100 bg-white p-4 shadow-sm md:grid-cols-2 xl:grid-cols-6">
        <input type="search" name="search" value="{{ request('search') }}" placeholder="Utilizator, anunț, marcă..." class="rounded-lg border-slate-200 text-sm xl:col-span-2">
        <select name="answer" class="rounded-lg border-slate-200 text-sm">
            <option value="">Toate răspunsurile</option>
            <option value="sold" @selected(request('answer') === 'sold')>Vândută</option>
            <option value="not_sold" @selected(request('answer') === 'not_sold')>Nu era vândută</option>
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
        <button class="rounded-lg bg-slate-800 px-4 py-2 text-sm font-bold text-white hover:bg-slate-900">Filtrează</button>
    </form>

    <div class="overflow-hidden rounded-xl border border-slate-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="min-w-[1100px] w-full text-left text-sm">
                <thead class="bg-slate-50 text-xs uppercase tracking-wide text-slate-500">
                    <tr>
                        <th class="p-4">Data</th>
                        <th class="p-4">Utilizator</th>
                        <th class="p-4">Anunț</th>
                        <th class="p-4">Mașină</th>
                        <th class="p-4">Răspuns</th>
                        <th class="p-4">Sursă</th>
                        <th class="p-4">Completare</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($feedback as $item)
                        <tr class="align-top hover:bg-slate-50/70">
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
                            <td class="p-4">
                                <span class="rounded-full px-2 py-1 text-xs font-bold {{ $item->completion_status === 'completed' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-500' }}">
                                    {{ $item->completion_status === 'completed' ? 'Completat' : 'Omis' }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="p-10 text-center text-slate-500">Nu există feedback pentru filtrele selectate.</td></tr>
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
