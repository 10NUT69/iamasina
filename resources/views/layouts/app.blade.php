<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('meta_title', view()->hasSection('title') ? view()->getSection('title') . ' - MeseriasBun.ro' : 'Servicii România - MeseriasBun.ro')</title>
    <meta name="description" content="@yield('meta_description', 'Găsește rapid meseriașul potrivit în zona ta. Electricieni, instalatori, constructori și multe altele pe MeseriasBun.ro')">

    @hasSection('canonical')
        @yield('canonical')
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="MeseriasBun.ro">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('meta_title', view()->hasSection('title') ? view()->getSection('title') : 'Servicii România')">
    <meta property="og:description" content="@yield('meta_description', 'Găsește meseriași verificați în zona ta.')">
    <meta property="og:image" content="@yield('meta_image', asset('images/logo.webp'))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', view()->hasSection('title') ? view()->getSection('title') : 'Servicii România')">
    <meta name="twitter:description" content="@yield('meta_description', 'Găsește meseriași verificați în zona ta.')">
    <meta name="twitter:image" content="@yield('meta_image', asset('images/logo.webp'))">

    @yield('schema')

    @if(app()->environment('production') && config('services.google.analytics_id'))
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ config('services.google.analytics_id') }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ config('services.google.analytics_id') }}');
        </script>
    @endif

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f6f7fb] dark:bg-[#121212] text-gray-900 dark:text-[#E5E5E5] font-inter antialiased min-h-screen flex flex-col">

    {{-- HEADER FIX --}}
    <nav id="main-nav" class="fixed top-0 left-0 w-full z-50 h-14 md:h-[72px] transition-all duration-500 ease-in-out
                            emag-gradient text-white border-b border-transparent dark:border-gray-800 flex items-center shadow-md will-change-transform">
        
        <div class="w-full max-w-7xl mx-auto px-3 sm:px-4 flex items-center justify-between">

            {{-- 1. LOGO --}}
            <a href="{{ route('services.index') }}" class="flex items-center shrink-0 gap-1 group decoration-0">
                <img src="/images/logo.webp" 
                     alt="Logo MeseriasBun"
                     width="150" 
                     height="44"
                     id="logo-img"
                     class="max-h-8 md:max-h-11 w-auto object-contain select-none transition-all duration-500">
            </a>

            {{-- 2. MENIU DREAPTA --}}
            <div class="flex items-center gap-2 sm:gap-5">

                {{-- Favorite --}}
                <button onclick="goToFavorites()" 
                        aria-label="Vezi favorite"
                        class="transition-all duration-300 flex items-center justify-center h-9 w-9 rounded-full hover:bg-white/10 shrink-0 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>

                @auth
                    {{-- LOGAT --}}
                    <a href="{{ route('account.index') }}" 
                       aria-label="Contul meu"
                       class="md:hidden flex items-center justify-center w-8 h-8 rounded-full bg-white/20 border border-white/30 text-sm font-bold text-white">
                        {{ substr(auth()->user()->name, 0, 1) }}
                    </a>

                    <div class="hidden md:flex items-center gap-4 text-white transition-all duration-300" id="auth-links">
                        <a href="{{ route('account.index') }}" class="font-bold hover:opacity-80 transition text-sm md:text-base">
                            {{ auth()->user()->name }}
                        </a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button class="font-medium hover:underline transition text-xs md:text-sm bg-white/10 px-3 py-1.5 rounded">
                                DELOGARE
                            </button>
                        </form>
                    </div>

                @else
                    {{-- NE-LOGAT --}}
                    <a href="{{ route('login') }}" 
                       aria-label="Autentificare"
                       class="md:hidden p-2 hover:bg-white/10 rounded-full transition text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-7 h-7">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>

                    <div class="hidden md:flex items-center gap-4 font-bold text-white transition-all duration-300 text-sm md:text-base" id="guest-links">
                        <a href="{{ route('login') }}" class="hover:underline transition">Intră în cont</a>
                        <span class="opacity-50">|</span>
                        <a href="{{ route('register') }}" class="hover:underline transition">Creează cont</a>
                    </div>
                @endauth

                {{-- BUTON ADAUGĂ --}}
                <a href="{{ route('services.create') }}" 
                   class="ml-1 bg-white text-gray-800 font-bold rounded-lg shadow hover:bg-gray-100 transition-all duration-500 active:scale-95 flex items-center gap-1
                          px-3 py-2 text-sm md:text-base md:px-4 md:py-2"
                   id="add-btn">
                    <span class="text-lg md:text-xl leading-none font-black">+</span>
                    <span class="hidden xs:inline">Adaugă</span>
                </a>

            </div>
        </div>
    </nav>

    {{-- HERO INJECTION POINT --}}
    @yield('hero')

    {{-- MAIN CONTENT --}}
    {{-- Condiția care protejează paginile interne să nu intre sub header --}}
    <main class="max-w-7xl mx-auto px-4 py-6 w-full flex-grow relative z-0 {{ !View::hasSection('hero') ? 'pt-20 md:pt-24' : '' }}">
        @yield('content')
    </main>


    {{-- FOOTER --}}
    <footer class="mt-auto border-t border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-[#050505]/95 backdrop-blur">
        <div class="max-w-7xl mx-auto px-4 md:px-0 py-4 md:py-5 text-xs text-gray-600 dark:text-gray-400">

            {{-- Rând 1: Brand + slogan --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="text-center md:text-left space-y-1">
                    <span class="text-sm font-extrabold tracking-tight text-[#CC2E2E]">
                        MeseriasBun.ro
                    </span>
                    <p class="text-[11px] text-gray-500 dark:text-gray-500 leading-tight">
                        Găsești rapid meseriași și servicii în toată țara – simplu și fără bătăi de cap.
                    </p>
                </div>

                {{-- Navigație utilă --}}
                <nav class="flex flex-wrap justify-center md:justify-end gap-2 md:gap-3">
                    <a href="{{ route('page.about') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#CC2E2E] hover:text-white transition">
                        Despre noi
                    </a>
                    <a href="{{ route('page.contact') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#CC2E2E] hover:text-white transition">
                        Contact
                    </a>
                    <a href="{{ route('page.terms') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#CC2E2E] hover:text-white transition">
                        Termeni &amp; condiții
                    </a>
                    <a href="{{ route('page.privacy') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#CC2E2E] hover:text-white transition">
                        Confidențialitate
                    </a>
                </nav>
            </div>

            {{-- Rând 2: Linie fină + text mic --}}
            <div class="mt-3 pt-3 border-t border-dashed border-gray-200 dark:border-gray-800 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-[10px] text-gray-400 dark:text-gray-500">
                <p class="text-center md:text-left">
                    &copy; {{ date('Y') }} MeseriasBun.ro – Toate drepturile rezervate.
                </p>
                <p class="text-center md:text-right">
                    Platformă de anunțuri pentru servicii. Verifică întotdeauna meseriașul înainte de colaborare.
                </p>
            </div>
        </div>
    </footer>


    {{-- SCRIPTURI --}}
    <script>
       function goToFavorites() {
        @if(auth()->check())
            window.location.href = "{{ route('account.index', ['tab' => 'favorite']) }}"; 
        @else
            alert("Trebuie să fii autentificat.");
        @endif
    }
    
    // ============================================================
    // LOGICA HEADER SCROLL & MOBILE HIDE
    // ============================================================
    
    const isHomepage = {{ request()->routeIs('services.index') ? 'true' : 'false' }};
    let lastScrollY = window.scrollY;
    const nav = document.getElementById('main-nav');
    const logo = document.getElementById('logo-img');
    const threshold = 15; 

    window.addEventListener('scroll', function() {
        const currentScrollY = window.scrollY;
        const isMobile = window.innerWidth < 768; 

        // --- 1. LOGICA DE DIMENSIUNE (SHRINK: Afectează doar Desktop) ---
        if (!isMobile) {
            if (currentScrollY > 20) {
                // Desktop Scroll Jos -> Compact (h-14)
                nav.classList.remove('md:h-[72px]');
                nav.classList.add('md:h-14', 'shadow-xl');
                
                // Logo devine și mai mic (md:max-h-9)
                if (logo) {
                    logo.classList.remove('md:max-h-11');
                    logo.classList.add('md:max-h-9');
                }
            } else {
                // Desktop Sus -> Mare (h-18)
                nav.classList.add('md:h-[72px]');
                nav.classList.remove('md:h-14', 'shadow-xl');

                // Logo revine la mărimea medie (md:max-h-11)
                if (logo) {
                    logo.classList.remove('md:max-h-9');
                    logo.classList.add('md:max-h-11');
                }
            }
        }

        // --- 2. LOGICA OLX (ASCUNDE/ARATĂ PE MOBIL) ---
        if (isHomepage && isMobile) {
            
            if (Math.abs(currentScrollY - lastScrollY) < threshold) return;

            if (currentScrollY < 10) {
                nav.style.transform = 'translateY(0)';
                lastScrollY = currentScrollY;
                return;
            }

            if (currentScrollY > lastScrollY) {
                nav.style.transform = 'translateY(-100%)';
            } else {
                nav.style.transform = 'translateY(0)';
            }
        } else if (isHomepage && !isMobile) {
            nav.style.transform = 'translateY(0)';
        }

        lastScrollY = currentScrollY;
    });
    </script>

    <style>
    @media (min-width: 360px) {
        .xs\:inline { display: inline !important; }
    }
    html, body {
        overscroll-behavior-y: none; 
    }
    </style>

</body>
</html>