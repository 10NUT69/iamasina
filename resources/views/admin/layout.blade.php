<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - iaAuto.ro</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        @media (prefers-color-scheme: dark) {
            .admin-theme {
                background: #121212;
                color: #e5e7eb;
            }

            .admin-theme [class*="bg-[#F8FAFC]"] {
                background: #121212 !important;
            }

            .admin-theme .bg-white,
            .admin-theme [class*="bg-slate-50"] {
                background: #1e1e1e !important;
            }

            .admin-theme .bg-gray-100,
            .admin-theme .bg-gray-200 {
                background: #252525 !important;
            }

            .admin-theme [class*="text-slate-800"],
            .admin-theme [class*="text-slate-700"] {
                color: #f4f4f5 !important;
            }

            .admin-theme [class*="text-slate-600"],
            .admin-theme [class*="text-slate-500"],
            .admin-theme [class*="text-slate-400"] {
                color: #a1a1aa !important;
            }

            .admin-theme [class*="border-slate-"],
            .admin-theme [class*="border-gray-"] {
                border-color: #333333 !important;
            }

            .admin-theme input:not([type="checkbox"]),
            .admin-theme select,
            .admin-theme textarea {
                background: #252525 !important;
                border-color: #404040 !important;
                color: #f4f4f5 !important;
            }

            .admin-theme a:hover {
                background-color: rgba(255, 255, 255, 0.06);
            }
        }
    </style>
</head>

<body class="admin-theme bg-gray-100 dark:bg-[#121212] dark:text-gray-100">

@php
    $sidebarStats = $adminSidebarStats ?? [
        'pending_services' => 0,
        'active_services' => 0,
        'total_services' => 0,
        'new_services_today' => 0,
        'total_users' => 0,
        'new_users_today' => 0,
    ];
@endphp

<div class="flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-[#1E1E1E] shadow h-screen p-6 border-r border-transparent dark:border-[#333333]">
        <div class="text-2xl font-bold mb-6">Admin Panel</div>

        <nav class="space-y-3">
            <a href="{{ route('admin.dashboard') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-200 font-bold' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.services.index') }}"
               class="flex items-center justify-between p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.services.*') ? 'bg-gray-200 font-bold' : '' }}">
                <span>Anunțuri</span>
                <span class="inline-flex items-center gap-1">
                    <span class="rounded-full bg-emerald-50 px-2 py-0.5 text-[11px] font-bold text-emerald-700" title="Anunțuri totale">
                        {{ $sidebarStats['total_services'] ?? $sidebarStats['active_services'] ?? 0 }}
                    </span>
                    @if(($sidebarStats['new_services_today'] ?? 0) > 0)
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700" title="Anunțuri publicate azi">
                            +{{ $sidebarStats['new_services_today'] }}
                        </span>
                    @endif
                    @if(($sidebarStats['pending_services'] ?? 0) > 0)
                        <span class="rounded-full bg-amber-100 px-2 py-0.5 text-[11px] font-bold text-amber-700" title="Anunțuri în așteptare">
                            {{ $sidebarStats['pending_services'] }}
                        </span>
                    @endif
                </span>
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="flex items-center justify-between p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.users.*') ? 'bg-gray-200 font-bold' : '' }}">
                <span>Utilizatori</span>
                <span class="inline-flex items-center gap-1">
                    <span class="rounded-full bg-slate-100 px-2 py-0.5 text-[11px] font-bold text-slate-700" title="Utilizatori total">
                        {{ $sidebarStats['total_users'] ?? 0 }}
                    </span>
                    @if(($sidebarStats['new_users_today'] ?? 0) > 0)
                        <span class="rounded-full bg-blue-50 px-2 py-0.5 text-[11px] font-bold text-blue-700" title="Utilizatori noi azi">
                            +{{ $sidebarStats['new_users_today'] }}
                        </span>
                    @endif
                </span>
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-200 font-bold' : '' }}">
                Categorii
            </a>

            <a href="{{ route('admin.auto-catalog.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.auto-catalog.*') ? 'bg-gray-200 font-bold' : '' }}">
                Date auto
            </a>

            <a href="{{ route('admin.counties.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.counties.*') ? 'bg-gray-200 font-bold' : '' }}">
                Județe
            </a>

            <a href="{{ route('admin.backups.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.backups.*') ? 'bg-gray-200 font-bold' : '' }}">
                Backup și restaurare
            </a>

            <a href="/" class="block p-2 text-red-600 hover:bg-red-100 rounded">
                ← Înapoi la site
            </a>
        </nav>
    </aside>

    <!-- CONTENT -->
    <main class="flex-1 p-10">
        @yield('content')
    </main>

</div>

</body>
</html>
