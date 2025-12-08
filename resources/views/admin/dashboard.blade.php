@extends('admin.layout')

@section('content')
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/flag-icon-css/6.6.6/css/flag-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/jsvectormap/dist/css/jsvectormap.min.css" />

@php
    // --- PREGĂTIRE DATE PENTRU GRAFICE (PHP -> JS) ---
    
    // 1. Labels pentru Graficul Mare (Ore sau Zile)
    // Dacă utilizatorul a ales "Azi" sau "Ieri", controllerul trimite ore (14:00). Altfel, trimite date (2023-11-29).
    $chartLabels = $dailyStats->pluck('date')->map(function($val) {
        // Verificăm dacă e oră (conține :) sau dată
        return str_contains($val, ':') ? $val : \Carbon\Carbon::parse($val)->format('d M');
    });
    
    $visitsData = $dailyStats->pluck('visits');
    $uniqueData = $dailyStats->pluck('unique_ips');

    // 2. Date pentru Harta Lumii (Format: { "RO": 150, "IT": 40 })
    $mapData = $topCountries->pluck('total', 'country')->toArray();
@endphp

<div class="max-w-[1600px] mx-auto py-6 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">

    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-chart-pie text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 leading-tight">Statistici Vizitatori</h1>
                <p class="text-xs text-slate-500 font-medium">
                    Raport pentru: 
                    <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded">
                        {{ match(request('range', 'today')) {
                            'today' => 'Astăzi',
                            'yesterday' => 'Ieri',
                            '7days' => 'Ultimele 7 zile',
                            '30days' => 'Ultimele 30 zile',
                            'this_month' => 'Luna aceasta',
                            default => 'Personalizat'
                        } }}
                    </span>
                </p>
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-3 bg-slate-50 p-1 rounded-lg border border-slate-200">
            <div class="relative group">
                <select name="range" onchange="this.form.submit()" 
                        class="appearance-none bg-transparent border-none text-slate-700 text-sm font-semibold py-2 pl-4 pr-8 rounded-md focus:ring-0 cursor-pointer hover:text-blue-600 transition-colors">
                    <option value="today" {{ request('range') == 'today' ? 'selected' : '' }}>Astăzi</option>
                    <option value="yesterday" {{ request('range') == 'yesterday' ? 'selected' : '' }}>Ieri</option>
                    <option value="7days" {{ request('range') == '7days' ? 'selected' : '' }}>Ultimele 7 zile</option>
                    <option value="30days" {{ request('range') == '30days' ? 'selected' : '' }}>Ultimele 30 zile</option>
                    <option value="this_month" {{ request('range') == 'this_month' ? 'selected' : '' }}>Luna aceasta</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>
            
            <div class="w-px h-6 bg-slate-200"></div>

            <button type="button" onclick="window.location.reload();" 
                    class="p-2 text-slate-500 hover:text-blue-600 hover:bg-white rounded-md transition-all shadow-sm" 
                    title="Actualizează Datele">
                <i class="fas fa-sync-alt"></i>
            </button>
        </form>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wifi text-6xl text-white transform rotate-12"></i>
            </div>
            <div class="relative z-10 text-white">
                <div class="flex justify-between items-start">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Online Acum</p>
                        <h3 class="text-3xl font-bold mt-1 flex items-center gap-3">
                            {{ $onlineNow }}
                            <span class="relative flex h-3 w-3">
                              <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                              <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                            </span>
                        </h3>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-slate-700/50">
                    <p class="text-xs text-slate-300">
                        {{ $onlineNow == 1 ? 'Ești singurul pe site.' : 'Utilizatori activi (5 min)' }}
                    </p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 p-5">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vizite Totale</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalVisits) }}</h3>
                </div>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
            <div class="flex items-center text-xs font-medium">
                @if($todayVisits >= $yesterdayVisits)
                    <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ $todayVisits }} Azi
                    </span>
                @else
                    <span class="text-red-500 bg-red-50 px-2 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ $todayVisits }} Azi
                    </span>
                @endif
                <span class="text-slate-400 ml-2">vs Ieri ({{ $yesterdayVisits }})</span>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 p-5">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Unici</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($uniqueVisitors) }}</h3>
                </div>
                <div class="p-2 bg-purple-50 text-purple-600 rounded-lg">
                    <i class="fas fa-fingerprint text-lg"></i>
                </div>
            </div>
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                @php $ratio = $totalVisits > 0 ? ($uniqueVisitors / $totalVisits) * 100 : 0; @endphp
                <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $ratio }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-2">{{ round($ratio) }}% vizitatori noi</p>
        </div>

        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl shadow-lg p-5 text-white">
            <div class="flex justify-between items-center h-full">
                <div class="flex flex-col">
                    <span class="text-indigo-200 text-xs font-bold uppercase">Utilizatori</span>
                    <span class="text-2xl font-bold">{{ number_format($userCount) }}</span>
                </div>
                <div class="h-8 w-px bg-white/20"></div>
                <div class="flex flex-col text-right">
                    <span class="text-indigo-200 text-xs font-bold uppercase">Anunțuri</span>
                    <span class="text-2xl font-bold">{{ number_format($serviceCount) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 lg:col-span-2 overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="fas fa-globe-americas text-blue-500"></i> Distribuție Geografică
                </h3>
            </div>
            <div class="p-0 relative flex-1 min-h-[400px]">
                <div id="world-map" style="width: 100%; height: 100%; min-height: 400px;"></div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="fas fa-chart-area text-purple-500"></i> Trafic {{ request('range') == 'today' ? 'Orar' : 'Zilnic' }}
                </h3>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-end">
                <div class="h-64 w-full">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-700 uppercase">Top Pagini</h3>
                <i class="fas fa-link text-slate-300"></i>
            </div>
            <div class="flex-1 overflow-auto max-h-[350px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @foreach($topPages as $page)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">
                                <a href="{{ url($page->url) }}" target="_blank" class="text-blue-600 font-medium truncate block max-w-[220px] hover:underline" title="{{ $page->url }}">
                                    {{ Str::limit($page->url, 40) }}
                                </a>
                                <div class="w-full bg-slate-100 rounded-full h-1 mt-1.5">
                                    <div class="bg-blue-500 h-1 rounded-full" style="width: {{ ($page->total / $totalVisits) * 100 }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700">
                                {{ number_format($page->total) }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-700 uppercase">Surse Trafic</h3>
                <i class="fas fa-share-alt text-slate-300"></i>
            </div>
            <div class="flex-1 overflow-auto max-h-[350px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @foreach($trafficSources as $source)
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3">
                                <span class="font-medium text-slate-700 block mb-1">
                                    @if($source->source == 'Google Search') <i class="fab fa-google text-red-500 mr-1"></i>
                                    @elseif($source->source == 'Facebook') <i class="fab fa-facebook text-blue-600 mr-1"></i>
                                    @else <i class="fas fa-globe text-slate-400 mr-1"></i>
                                    @endif
                                    {{ $source->source }}
                                </span>
                                <div class="w-full bg-slate-100 rounded-full h-1">
                                    <div class="bg-green-500 h-1 rounded-full" style="width: {{ ($source->total / $totalVisits) * 100 }}%"></div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700">
                                {{ $source->total }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-700 uppercase">Browsere</h3>
                <i class="fas fa-laptop text-slate-300"></i>
            </div>
            <div class="flex-1 overflow-auto max-h-[350px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @foreach($browsers as $browser)
                        @php
                            $icon = match(strtolower($browser->browser)) {
                                'chrome' => 'fa-chrome', 'firefox' => 'fa-firefox', 'safari' => 'fa-safari', 'edge' => 'fa-edge', default => 'fa-globe'
                            };
                            $color = match(strtolower($browser->browser)) {
                                'chrome' => 'text-red-500', 'firefox' => 'text-orange-500', 'safari' => 'text-blue-400', 'edge' => 'text-blue-600', default => 'text-slate-400'
                            };
                        @endphp
                        <tr class="hover:bg-slate-50 transition">
                            <td class="px-4 py-3 flex items-center gap-3">
                                <i class="fab {{ $icon }} {{ $color }} text-lg w-5 text-center"></i>
                                <div>
                                    <span class="font-medium text-slate-700 block">{{ $browser->browser }}</span>
                                    <div class="w-24 bg-slate-100 rounded-full h-1 mt-1">
                                        <div class="bg-slate-400 h-1 rounded-full" style="width: {{ ($browser->total / $totalVisits) * 100 }}%"></div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right font-bold text-slate-700">
                                {{ $browser->total }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-10">
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
                <i class="far fa-clock text-slate-400"></i> Jurnal Vizite
            </h3>
            <span class="text-xs font-mono bg-white border border-slate-200 px-2 py-1 rounded text-slate-500">Live Feed</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Timp</th>
                        <th class="px-6 py-3">IP / Locație</th>
                        <th class="px-6 py-3">Pagină</th>
                        <th class="px-6 py-3">Referrer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @foreach($recentVisits as $visit)
                    <tr class="hover:bg-blue-50/30 transition">
                        <td class="px-6 py-3 whitespace-nowrap text-slate-500 font-mono">
                            {{ $visit->created_at->format('H:i:s') }}
                            <span class="text-slate-300 ml-1 text-[10px]">{{ $visit->created_at->format('d M') }}</span>
                        </td>
                        <td class="px-6 py-3">
                            <div class="flex items-center gap-2">
                                <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono border border-slate-200">{{ $visit->ip }}</span>
                                @if($visit->country)
                                    <span class="fi fi-{{ strtolower($visit->country) }} shadow-sm rounded-sm" title="{{ $visit->country }}"></span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-3">
                            <span class="text-slate-600 truncate max-w-[250px] block" title="{{ $visit->url }}">
                                {{ Str::limit($visit->url, 50) }}
                            </span>
                        </td>
                        <td class="px-6 py-3 text-slate-500 truncate max-w-[150px]">
                            {{ $visit->referer ? parse_url($visit->referer, PHP_URL_HOST) : '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/js/jsvectormap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jsvectormap/dist/maps/world.js"></script>

<script>
    // --- 1. HARTA ---
    const mapData = @json($mapData);
    const normalizedMapData = {};
    Object.keys(mapData).forEach(key => { normalizedMapData[key.toUpperCase()] = mapData[key]; });

    new jsVectorMap({
        selector: '#world-map',
        map: 'world',
        zoomButtons: true,
        zoomOnScroll: false,
        regionStyle: {
            initial: { fill: '#e2e8f0', stroke: '#cbd5e1', strokeWidth: 0.5, fillOpacity: 1 },
            hover: { fill: '#3b82f6' }
        },
        series: {
            regions: [{
                attribute: 'fill',
                legend: { title: 'Vizite' },
                scale: ['#bfdbfe', '#1e40af'],
                values: normalizedMapData,
            }]
        },
        onRegionTooltipShow(event, tooltip, code) {
            const count = normalizedMapData[code] || 0;
            tooltip.text(
                `<div class="text-center font-sans">
                    <strong class="block text-sm mb-1 text-slate-800">${tooltip.text()}</strong>
                    <span class="text-xs bg-blue-600 text-white px-2 py-0.5 rounded shadow">${count} vizite</span>
                 </div>`, true
            );
        }
    });

    // --- 2. GRAFIC (LINE) ---
    Chart.defaults.font.family = "'Inter', sans-serif";
    Chart.defaults.color = '#94a3b8';
    
    const ctx = document.getElementById('mainChart').getContext('2d');
    const gradient = ctx.createLinearGradient(0, 0, 0, 300);
    gradient.addColorStop(0, 'rgba(59, 130, 246, 0.2)');
    gradient.addColorStop(1, 'rgba(59, 130, 246, 0.0)');

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: @json($chartLabels),
            datasets: [{
                label: 'Vizite',
                data: @json($visitsData),
                borderColor: '#3b82f6',
                backgroundColor: gradient,
                borderWidth: 2,
                tension: 0.3,
                fill: true,
                pointRadius: 3,
                pointHoverRadius: 6
            }, {
                label: 'Unici',
                data: @json($uniqueData),
                borderColor: '#10b981',
                backgroundColor: 'transparent',
                borderWidth: 2,
                borderDash: [4, 4],
                tension: 0.3,
                pointRadius: 0
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode: 'index', intersect: false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(255, 255, 255, 0.95)',
                    titleColor: '#1e293b',
                    bodyColor: '#475569',
                    borderColor: '#e2e8f0',
                    borderWidth: 1,
                    padding: 10,
                    displayColors: true,
                    usePointStyle: true
                }
            },
            scales: {
                x: { grid: { display: false } },
                y: { border: { display: false }, grid: { borderDash: [4, 4] }, beginAtZero: true }
            }
        }
    });
</script>

<style>
/* Scrollbar Subțire pentru Tabele */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>
@endsection