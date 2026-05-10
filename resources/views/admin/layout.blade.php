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

<div class="flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white dark:bg-[#1E1E1E] shadow h-screen p-6 border-r border-transparent dark:border-[#333333]">
        <h1 class="text-2xl font-bold mb-6">Admin Panel</h1>

        <nav class="space-y-3">
            <a href="{{ route('admin.dashboard') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.dashboard') ? 'bg-gray-200 font-bold' : '' }}">
                Dashboard
            </a>

            <a href="{{ route('admin.services.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.services.*') ? 'bg-gray-200 font-bold' : '' }}">
                Anunțuri
            </a>

            <a href="{{ route('admin.users.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.users.*') ? 'bg-gray-200 font-bold' : '' }}">
                Utilizatori
            </a>

            <a href="{{ route('admin.categories.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.categories.*') ? 'bg-gray-200 font-bold' : '' }}">
                Categorii
            </a>

            <a href="{{ route('admin.counties.index') }}"
               class="block p-2 rounded hover:bg-gray-200 {{ request()->routeIs('admin.counties.*') ? 'bg-gray-200 font-bold' : '' }}">
                Județe
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
