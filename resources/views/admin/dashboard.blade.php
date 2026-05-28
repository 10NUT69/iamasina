@extends('admin.layout')

@section('content')
@php
    $currentRange = $range ?? request('range', 'today');
    $rangeLabel = match($currentRange) {
        'today' => 'Astazi',
        'yesterday' => 'Ieri',
        '7days' => 'Ultimele 7 zile',
        '30days' => 'Ultimele 30 zile',
        'this_month' => 'Luna aceasta',
        default => 'Astazi',
    };

    $chartLabels = $dailyStats->pluck('date')->map(function ($value) {
        return str_contains($value, ':') ? $value : \Carbon\Carbon::parse($value)->format('d M');
    });

    $visitsData = $dailyStats->pluck('visits');
    $uniqueData = $dailyStats->pluck('unique_ips');
@endphp

<div class="max-w-[1536px] mx-auto py-6 px-4 sm:px-6 lg:px-8 bg-[#F8FAFC] min-h-screen font-sans text-slate-600">
    <div class="bg-white p-4 rounded-xl shadow-sm border border-slate-200 mb-6 flex flex-col md:flex-row justify-between items-center gap-4">
        <div class="flex items-center gap-4">
            <div class="h-12 w-12 rounded-xl bg-blue-600 text-white flex items-center justify-center shadow-lg shadow-blue-500/30">
                <i class="fas fa-gauge-high text-xl"></i>
            </div>
            <div>
                <h1 class="text-xl font-bold text-slate-800 leading-tight">Dashboard admin</h1>
                <p class="text-xs text-slate-500 font-medium">
                    Raport: <span class="text-blue-600 bg-blue-50 px-2 py-0.5 rounded">{{ $rangeLabel }}</span>
                </p>
            </div>
        </div>

        <form method="GET" action="{{ url()->current() }}" class="flex items-center gap-3 bg-slate-50 p-1 rounded-lg border border-slate-200">
            <div class="relative group">
                <select name="range" onchange="this.form.submit()"
                        class="appearance-none bg-transparent border-none text-slate-700 text-sm font-semibold py-2 pl-4 pr-8 rounded-md focus:ring-0 cursor-pointer hover:text-blue-600 transition-colors">
                    <option value="today" {{ $currentRange === 'today' ? 'selected' : '' }}>Astazi</option>
                    <option value="yesterday" {{ $currentRange === 'yesterday' ? 'selected' : '' }}>Ieri</option>
                    <option value="7days" {{ $currentRange === '7days' ? 'selected' : '' }}>Ultimele 7 zile</option>
                    <option value="30days" {{ $currentRange === '30days' ? 'selected' : '' }}>Ultimele 30 zile</option>
                    <option value="this_month" {{ $currentRange === 'this_month' ? 'selected' : '' }}>Luna aceasta</option>
                </select>
                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-slate-400">
                    <i class="fas fa-chevron-down text-[10px]"></i>
                </div>
            </div>

            <div class="w-px h-6 bg-slate-200"></div>

            <button type="button" onclick="window.location.reload();"
                    class="p-2 text-slate-500 hover:text-blue-600 hover:bg-white rounded-md transition-all shadow-sm"
                    title="Actualizeaza">
                <i class="fas fa-sync-alt"></i>
            </button>
        </form>
    </div>

    <div class="mb-6">
        <div class="mb-3 flex items-center justify-between">
            <h2 class="text-sm font-bold uppercase tracking-wider text-slate-500">De rezolvat</h2>
            <span class="text-xs text-slate-400">Snapshot operational</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-6 gap-4">
            <a href="{{ route('admin.services.index', ['status' => 'inactive']) }}"
               class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:border-amber-200 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Inactive</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($pendingServices) }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">Anunturi de verificat</p>
            </a>

            <a href="{{ route('admin.services.index', ['status' => 'inactive']) }}"
               class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:border-red-200 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Expirate</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($expiredServices) }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-red-50 text-red-600 flex items-center justify-center">
                        <i class="fas fa-calendar-times"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">Necesita decizie</p>
            </a>

            <a href="{{ route('admin.services.index') }}"
               class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:border-blue-200 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Fara poze</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($servicesWithoutImages) }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center">
                        <i class="fas fa-image"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">Calitate listari</p>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:border-emerald-200 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Useri noi</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($newUsersToday) }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                        <i class="fas fa-user-plus"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">Inregistrati azi</p>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 hover:border-indigo-200 hover:shadow-md transition">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Dealeri noi</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($newDealersToday) }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-indigo-50 text-indigo-600 flex items-center justify-center">
                        <i class="fas fa-store"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">Conturi dealer azi</p>
            </a>

            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase">Mesaje</p>
                        <p class="mt-2 text-2xl font-bold text-slate-800">{{ number_format($newMessagesToday) }}</p>
                    </div>
                    <div class="h-9 w-9 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center">
                        <i class="fas fa-comments"></i>
                    </div>
                </div>
                <p class="mt-3 text-xs text-slate-500">{{ number_format($newConversationsToday) }} conversatii noi</p>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        <div class="bg-slate-800 rounded-xl shadow-lg border border-slate-700 p-5 relative overflow-hidden group">
            <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
                <i class="fas fa-wifi text-6xl text-white transform rotate-12"></i>
            </div>
            <div class="relative z-10 text-white">
                <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Online acum</p>
                <h3 class="text-3xl font-bold mt-1 flex items-center gap-3">
                    {{ $onlineNow }}
                    <span class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                    </span>
                </h3>
                <div class="mt-4 pt-4 border-t border-slate-700/50">
                    <p class="text-xs text-slate-300">Utilizatori activi in ultimele 5 minute</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-[0_2px_10px_-3px_rgba(6,81,237,0.1)] border border-slate-100 p-5">
            <div class="flex justify-between items-start mb-4">
                <div>
                    <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Vizite totale</p>
                    <h3 class="text-2xl font-bold text-slate-800 mt-1">{{ number_format($totalVisits) }}</h3>
                </div>
                <div class="p-2 bg-blue-50 text-blue-600 rounded-lg">
                    <i class="fas fa-users text-lg"></i>
                </div>
            </div>
            <div class="flex items-center text-xs font-medium">
                @if($todayVisits >= $yesterdayVisits)
                    <span class="text-green-600 bg-green-50 px-2 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-up"></i> {{ number_format($todayVisits) }} azi
                    </span>
                @else
                    <span class="text-red-500 bg-red-50 px-2 py-0.5 rounded flex items-center gap-1">
                        <i class="fas fa-arrow-down"></i> {{ number_format($todayVisits) }} azi
                    </span>
                @endif
                <span class="text-slate-400 ml-2">vs ieri ({{ number_format($yesterdayVisits) }})</span>
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
            @php $ratio = $totalVisits > 0 ? ($uniqueVisitors / $totalVisits) * 100 : 0; @endphp
            <div class="w-full bg-slate-100 rounded-full h-1.5 mt-2">
                <div class="bg-purple-500 h-1.5 rounded-full" style="width: {{ $ratio }}%"></div>
            </div>
            <p class="text-xs text-slate-400 mt-2">{{ round($ratio) }}% din vizite</p>
        </div>

        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-xl shadow-lg p-5 text-white">
            <div class="flex justify-between items-center h-full">
                <div class="flex flex-col">
                    <span class="text-indigo-200 text-xs font-bold uppercase">Utilizatori</span>
                    <span class="text-2xl font-bold">{{ number_format($userCount) }}</span>
                </div>
                <div class="h-8 w-px bg-white/20"></div>
                <div class="flex flex-col text-right">
                    <span class="text-indigo-200 text-xs font-bold uppercase">Anunturi</span>
                    <span class="text-2xl font-bold">{{ number_format($serviceCount) }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 lg:col-span-2 overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50 flex justify-between items-center">
                <h3 class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="fas fa-chart-area text-purple-500"></i> Trafic {{ in_array($currentRange, ['today', 'yesterday'], true) ? 'orar' : 'zilnic' }}
                </h3>
            </div>
            <div class="p-4 flex-1 flex flex-col justify-end">
                <div class="h-80 w-full">
                    <canvas id="mainChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden flex flex-col">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                <h3 class="font-bold text-slate-700 flex items-center gap-2">
                    <i class="fas fa-location-dot text-blue-500"></i> Top locatii
                </h3>
            </div>
            <div class="flex-1 overflow-auto max-h-[360px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @forelse($topLocations as $location)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-700 block">{{ $location->label }}</span>
                                    <div class="w-full bg-slate-100 rounded-full h-1 mt-1.5">
                                        <div class="bg-blue-500 h-1 rounded-full" style="width: {{ $totalVisits > 0 ? ($location->total / $totalVisits) * 100 : 0 }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-700">
                                    {{ number_format($location->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-slate-400" colspan="2">
                                    Nu exista date de locatie salvate inca.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-700 uppercase">Top pagini</h3>
                <i class="fas fa-link text-slate-300"></i>
            </div>
            <div class="flex-1 overflow-auto max-h-[350px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @forelse($topPages as $page)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <a href="{{ url($page->url) }}" target="_blank" class="text-blue-600 font-medium truncate block max-w-[220px] hover:underline" title="{{ $page->url }}">
                                        {{ Str::limit($page->url, 40) }}
                                    </a>
                                    <div class="w-full bg-slate-100 rounded-full h-1 mt-1.5">
                                        <div class="bg-blue-500 h-1 rounded-full" style="width: {{ $totalVisits > 0 ? ($page->total / $totalVisits) * 100 : 0 }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-700">
                                    {{ number_format($page->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-slate-400" colspan="2">Fara date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-700 uppercase">Surse trafic</h3>
                <i class="fas fa-share-alt text-slate-300"></i>
            </div>
            <div class="flex-1 overflow-auto max-h-[350px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @forelse($trafficSources as $source)
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3">
                                    <span class="font-medium text-slate-700 block mb-1">
                                        @if($source->source === 'Google') <i class="fab fa-google text-red-500 mr-1"></i>
                                        @elseif($source->source === 'Facebook') <i class="fab fa-facebook text-blue-600 mr-1"></i>
                                        @elseif($source->source === 'Instagram') <i class="fab fa-instagram text-pink-500 mr-1"></i>
                                        @else <i class="fas fa-globe text-slate-400 mr-1"></i>
                                        @endif
                                        {{ $source->source }}
                                    </span>
                                    <div class="w-full bg-slate-100 rounded-full h-1">
                                        <div class="bg-green-500 h-1 rounded-full" style="width: {{ $totalVisits > 0 ? ($source->total / $totalVisits) * 100 : 0 }}%"></div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-700">
                                    {{ number_format($source->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-slate-400" colspan="2">Fara date.</td>
                            </tr>
                        @endforelse
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
                        @forelse($browsers as $browser)
                            @php
                                $browserIcon = match(strtolower($browser->browser)) {
                                    'chrome' => 'fa-chrome',
                                    'firefox' => 'fa-firefox',
                                    'safari' => 'fa-safari',
                                    'edge' => 'fa-edge',
                                    default => 'fa-globe'
                                };
                                $browserColor = match(strtolower($browser->browser)) {
                                    'chrome' => 'text-red-500',
                                    'firefox' => 'text-orange-500',
                                    'safari' => 'text-blue-400',
                                    'edge' => 'text-blue-600',
                                    default => 'text-slate-400'
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 flex items-center gap-3">
                                    <i class="fab {{ $browserIcon }} {{ $browserColor }} text-lg w-5 text-center"></i>
                                    <div>
                                        <span class="font-medium text-slate-700 block">{{ $browser->browser }}</span>
                                        <div class="w-24 bg-slate-100 rounded-full h-1 mt-1">
                                            <div class="bg-slate-400 h-1 rounded-full" style="width: {{ $totalVisits > 0 ? ($browser->total / $totalVisits) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-700">
                                    {{ number_format($browser->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-slate-400" colspan="2">Fara date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-200 flex flex-col h-full">
            <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h3 class="font-bold text-sm text-slate-700 uppercase">Dispozitive</h3>
                <i class="fas fa-mobile-screen text-slate-300"></i>
            </div>
            <div class="flex-1 overflow-auto max-h-[350px] custom-scrollbar">
                <table class="w-full text-xs text-left">
                    <tbody class="divide-y divide-slate-50">
                        @forelse($devices as $device)
                            @php
                                $deviceIcon = match(strtolower($device->device)) {
                                    'mobile' => 'fa-mobile-screen-button',
                                    'tablet' => 'fa-tablet-screen-button',
                                    default => 'fa-desktop'
                                };
                            @endphp
                            <tr class="hover:bg-slate-50 transition">
                                <td class="px-4 py-3 flex items-center gap-3">
                                    <i class="fas {{ $deviceIcon }} text-slate-400 text-lg w-5 text-center"></i>
                                    <div>
                                        <span class="font-medium text-slate-700 block">{{ ucfirst($device->device) }}</span>
                                        <div class="w-24 bg-slate-100 rounded-full h-1 mt-1">
                                            <div class="bg-indigo-400 h-1 rounded-full" style="width: {{ $totalVisits > 0 ? ($device->total / $totalVisits) * 100 : 0 }}%"></div>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-4 py-3 text-right font-bold text-slate-700">
                                    {{ number_format($device->total) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td class="px-4 py-8 text-center text-slate-400" colspan="2">Fara date.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mb-10">
        <div class="px-5 py-4 border-b border-slate-100 flex justify-between items-center bg-slate-50">
            <h3 class="font-bold text-slate-700 flex items-center gap-2">
                <i class="far fa-clock text-slate-400"></i> Jurnal vizite
            </h3>
            <span class="text-xs font-mono bg-white border border-slate-200 px-2 py-1 rounded text-slate-500">Sumar IP</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-xs text-left">
                <thead class="bg-slate-50 text-slate-500 uppercase font-bold border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-3">Ultima vizita</th>
                        <th class="px-6 py-3">IP / locatie</th>
                        <th class="px-6 py-3">Ultima pagina</th>
                        <th class="px-6 py-3 text-center">Pagini</th>
                        <th class="px-6 py-3">Referrer</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($recentVisits as $visit)
                        <tr class="hover:bg-blue-50/30 transition">
                            <td class="px-6 py-3 whitespace-nowrap text-slate-500 font-mono">
                                {{ $visit->created_at->format('H:i:s') }}
                                <span class="text-slate-300 ml-1 text-[10px]">{{ $visit->created_at->format('d M') }}</span>
                            </td>
                            <td class="px-6 py-3">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="bg-slate-100 text-slate-600 px-2 py-0.5 rounded font-mono border border-slate-200">{{ $visit->ip }}</span>
                                    <span class="text-slate-500 truncate max-w-[180px]" title="{{ $visit->location_label ?? 'Locatie necunoscuta' }}">
                                        {{ $visit->location_label ?? 'Locatie necunoscuta' }}
                                    </span>
                                </div>
                            </td>
                            <td class="px-6 py-3">
                                <span class="text-slate-600 truncate max-w-[250px] block" title="{{ $visit->url }}">
                                    {{ Str::limit($visit->url, 50) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-center">
                                <span class="inline-flex items-center justify-center min-w-8 rounded-full border border-blue-100 bg-blue-50 px-2 py-1 text-xs font-bold text-blue-700">
                                    {{ number_format($visit->page_views ?? 1) }}
                                </span>
                            </td>
                            <td class="px-6 py-3 text-slate-500 truncate max-w-[180px]" title="{{ $visit->referer ?? '' }}">
                                {{ $visit->referer_label ?? '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td class="px-6 py-8 text-center text-slate-400" colspan="5">Fara vizite in perioada aleasa.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/js/all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

<script>
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
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background-color: #cbd5e1; border-radius: 20px; }
</style>
@endsection
