<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Servicii România</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

<div class="flex">

    <!-- SIDEBAR -->
    <aside class="w-64 bg-white shadow h-screen p-6">
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
