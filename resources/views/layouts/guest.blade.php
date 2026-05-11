<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 dark:text-gray-100 antialiased">
        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-6 sm:pt-0 bg-gray-100 dark:bg-[#121212]">
            <div>
                <a href="/" class="inline-flex items-center justify-center">
                    <img src="{{ asset('images/iaauto-logo.svg') }}?v=modern-20260511c"
                         alt="iaAuto.ro"
                         width="240"
                         height="64"
                         class="h-12 w-auto max-w-[190px] object-contain sm:h-14">
                </a>
            </div>

            <div class="w-full sm:max-w-md mt-6 px-6 py-4 bg-white dark:bg-[#1E1E1E] shadow-md overflow-hidden sm:rounded-lg border border-transparent dark:border-[#333333]">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
