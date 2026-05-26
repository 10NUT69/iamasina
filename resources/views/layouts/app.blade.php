<!DOCTYPE html>
<html lang="ro">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('meta_title', view()->hasSection('title') ? view()->getSection('title') . ' - iaAuto.ro' : 'Anunțuri auto de vânzare - iaAuto.ro')</title>
    <meta name="description" content="@yield('meta_description', 'Găsește rapid mașina potrivită pe iaAuto.ro. Anunțuri auto curate de la proprietari și parcuri auto din toată țara.')">

    @hasSection('canonical')
        @yield('canonical')
    @else
        <link rel="canonical" href="{{ url()->current() }}">
    @endif

    <meta property="og:type" content="website">
    <meta property="og:site_name" content="ia Auto">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="@yield('meta_title', view()->hasSection('title') ? view()->getSection('title') : 'Anunțuri auto de vânzare')">
    <meta property="og:description" content="@yield('meta_description', 'Găsește anunțuri auto curate din zona ta.')">
    <meta property="og:image" content="@yield('meta_image', asset('images/social-share.webp'))">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="@yield('meta_title', view()->hasSection('title') ? view()->getSection('title') : 'Anunțuri auto de vânzare')">
    <meta name="twitter:description" content="@yield('meta_description', 'Găsește anunțuri auto curate din zona ta.')">
    <meta name="twitter:image" content="@yield('meta_image', asset('images/social-share.webp'))">

@verbatim
<script type="application/ld+json">
{
    "@context": "https://schema.org",
    "@type": "WebSite",
    "name": "ia Auto",
    "alternateName": "iaAuto.ro",
    "url": "https://iaauto.ro"
}
</script>
@endverbatim

    @yield('schema')
    @yield('head')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-[#f6f7fb] dark:bg-[#121212] text-gray-900 dark:text-[#E5E5E5] font-inter antialiased min-h-screen flex flex-col">

    {{-- HEADER FIX --}}
    <nav id="main-nav" class="fixed top-0 left-0 w-full z-50 h-14 md:h-[72px] transition-all duration-500 ease-in-out
                            emag-gradient text-white border-b border-transparent dark:border-gray-800 flex items-center shadow-md will-change-transform">

        <div class="w-full max-w-[1536px] mx-auto px-3 sm:px-4 lg:px-8 flex items-center justify-between">

            {{-- 1. LOGO --}}
            <a href="{{ route('services.index') }}" class="flex items-center shrink-0 gap-1 group decoration-0">
                <picture>
                    <source media="(max-width: 639px)" srcset="{{ asset('images/iaauto-logo-nav-mobile.svg') }}?v=modern-20260511g">
                    <img src="{{ asset('images/iaauto-logo-nav.svg') }}?v=modern-20260511c"
                         alt="iaAuto.ro"
                         width="240"
                         height="64"
                         id="logo-img"
                         class="h-8 max-w-[124px] min-[375px]:max-w-[140px] sm:h-9 sm:max-w-[166px] md:h-11 md:max-w-[210px] w-auto object-contain select-none transition-all duration-500">
                </picture>
            </a>

            {{-- 2. MENIU DREAPTA --}}
            <div class="flex items-center gap-1.5 sm:gap-4">

                <a href="{{ route('page.blog') }}"
                   class="hidden md:inline-flex items-center rounded-full px-3.5 py-2 text-sm font-bold text-white/95 transition hover:bg-white/10 hover:text-white">
                    Blog
                </a>

                {{-- Favorite --}}
                <button onclick="goToFavorites()"
                        aria-label="Vezi favorite"
                        class="transition-all duration-300 flex items-center justify-center h-8 w-8 md:h-9 md:w-9 rounded-full hover:bg-white/10 shrink-0 text-white">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5 md:w-6 md:h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 8.25c0-2.485-2.099-4.5-4.688-4.5-1.935 0-3.597 1.126-4.312 2.733C11.285 4.876 9.623 3.75 7.688 3.75 5.099 3.75 3 5.765 3 8.25c0 7.22 9 12 9 12s9-4.78 9-12z" />
                    </svg>
                </button>

                @auth
                    @php
                        $isServiceShowPage = request()->routeIs('service.show.car');
                        $unreadMessagesCount = !$isServiceShowPage && \Illuminate\Support\Facades\Schema::hasTable('messages')
                            ? \App\Models\Message::query()
                                ->where('sender_id', '!=', auth()->id())
                                ->whereNull('read_at')
                                ->whereHas('conversation', fn ($query) => $query
                                    ->where(fn ($participantQuery) => $participantQuery
                                        ->where('buyer_id', auth()->id())
                                        ->whereNull('buyer_deleted_at'))
                                    ->orWhere(fn ($participantQuery) => $participantQuery
                                        ->where('seller_id', auth()->id())
                                        ->whereNull('seller_deleted_at')))
                                ->count()
                            : 0;
                    @endphp
                    {{-- LOGAT --}}
                    <div class="relative" id="account-menu-wrap">
                        <button type="button"
                                onclick="toggleAccountMenu()"
                                aria-label="Contul meu"
                                aria-expanded="false"
                                id="account-menu-button"
                                class="relative flex items-center justify-center gap-1.5 rounded-full bg-white/15 px-2 py-1 text-xs font-bold text-white transition hover:bg-white/25 md:px-2.5">
                            <span class="flex h-6 w-6 items-center justify-center rounded-full bg-white/20 border border-white/30 text-xs font-bold text-white">
                                {{ substr(auth()->user()->name, 0, 1) }}
                            </span>
                            <span class="hidden md:inline max-w-[96px] truncate">{{ auth()->user()->name }}</span>
                            <span data-unread-badge class="absolute -right-1 -top-1 {{ $unreadMessagesCount > 0 ? 'inline-flex' : 'hidden' }} min-w-4 items-center justify-center rounded-full bg-white px-1 text-[10px] font-black leading-4 text-[#C81424]">
                                {{ $unreadMessagesCount > 99 ? '99+' : ($unreadMessagesCount ?: '') }}
                            </span>
                        </button>

                        <div id="account-menu"
                             class="absolute left-0 top-full z-[80] mt-2 hidden w-max max-w-[calc(100vw-1rem)] overflow-hidden rounded-xl border border-gray-200 bg-white py-1 text-gray-800 shadow-xl dark:border-[#333] dark:bg-[#1E1E1E] dark:text-gray-100">
                            <a href="{{ route('account.index', ['tab' => 'anunturi']) }}" class="block whitespace-nowrap px-3 py-2 text-[13px] font-bold hover:bg-gray-50 dark:hover:bg-[#252525]">Anunțurile mele</a>
                            <a href="{{ route('account.index', ['tab' => 'mesaje']) }}" class="flex items-center justify-between gap-4 whitespace-nowrap px-3 py-2 text-[13px] font-bold hover:bg-gray-50 dark:hover:bg-[#252525]">
                                <span>Mesaje</span>
                                <span data-unread-badge class="{{ $unreadMessagesCount > 0 ? 'inline-flex' : 'hidden' }} min-w-5 items-center justify-center rounded-full bg-[#C81424] px-1.5 text-[10px] font-black leading-5 text-white">
                                    {{ $unreadMessagesCount > 99 ? '99+' : ($unreadMessagesCount ?: '') }}
                                </span>
                            </a>
                            <a href="{{ route('account.index', ['tab' => 'favorite']) }}" class="block whitespace-nowrap px-3 py-2 text-[13px] font-bold hover:bg-gray-50 dark:hover:bg-[#252525]">Favorite</a>
                            <a href="{{ route('account.index', ['tab' => 'profil']) }}" class="block whitespace-nowrap px-3 py-2 text-[13px] font-bold hover:bg-gray-50 dark:hover:bg-[#252525]">Setări</a>
                            <div class="my-1 border-t border-gray-100 dark:border-[#333]"></div>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button class="block w-full whitespace-nowrap px-3 py-2 text-left text-[13px] font-bold text-[#C81424] hover:bg-red-50 dark:hover:bg-[#2a1013]">
                                    Delogare
                                </button>
                            </form>
                        </div>
                    </div>

                @else
                    {{-- NE-LOGAT --}}
                    <a href="{{ route('login') }}"
                       aria-label="Autentificare"
                       class="md:hidden p-1.5 hover:bg-white/10 rounded-full transition text-white">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
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
                   class="ml-0.5 sm:ml-1 bg-white text-[#8f111a] font-bold rounded-lg shadow hover:bg-[#fff4f5] transition-all duration-500 active:scale-95 flex items-center justify-center gap-1 whitespace-nowrap
                          px-2 py-2 text-[11px] leading-none min-[375px]:px-2.5 min-[375px]:text-xs sm:px-3 sm:text-sm md:px-4 md:py-2 md:text-base"
                   id="add-btn">
                    <span class="hidden min-[390px]:inline text-base md:text-xl leading-none font-black">+</span>
                    <span>Publică anunț</span>
                </a>

            </div>
        </div>
    </nav>

    {{-- HERO INJECTION POINT --}}
    @yield('hero')

    {{-- MAIN CONTENT --}}
    {{-- Condiția care protejează paginile interne să nu intre sub header --}}
    <main class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-6 w-full flex-grow relative z-0 {{ !View::hasSection('hero') ? 'pt-20 md:pt-24' : '' }}">
        @if(session('success'))
            <div class="mb-4 rounded-lg border border-green-200 bg-green-50 px-4 py-3 text-sm font-semibold text-green-800 shadow-sm dark:border-green-900/50 dark:bg-green-950/40 dark:text-green-200">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-800 shadow-sm dark:border-red-900/50 dark:bg-red-950/40 dark:text-red-200">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
    </main>


    {{-- FOOTER --}}
    <footer class="mt-auto border-t border-gray-200 dark:border-gray-800 bg-white/95 dark:bg-[#050505]/95 backdrop-blur">
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8 py-4 md:py-5 text-xs text-gray-600 dark:text-gray-400">

            {{-- Rând 1: Brand + slogan --}}
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3">
                <div class="text-center md:text-left space-y-1">
                    <a href="{{ route('services.index') }}" class="inline-flex justify-center md:justify-start">
                        <img src="{{ asset('images/iaauto-logo.svg') }}?v=modern-20260511c"
                             alt="iaAuto.ro"
                             width="240"
                             height="64"
                             class="h-8 w-auto object-contain dark:hidden">
                        <img src="{{ asset('images/iaauto-logo-nav.svg') }}?v=modern-20260511c"
                             alt="iaAuto.ro"
                             width="240"
                             height="64"
                             class="hidden h-8 w-auto object-contain dark:block">
                    </a>
                    <p class="text-[11px] text-gray-500 dark:text-gray-500 leading-tight">
                        Anunțuri auto curate, ușor de filtrat, de la proprietari și parcuri auto din toată țara.
                    </p>
                </div>

                {{-- Navigație utilă --}}
                <nav class="flex flex-wrap justify-center md:justify-end gap-2 md:gap-3">
                    <a href="{{ route('page.blog') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Blog
                    </a>
                    <a href="{{ route('page.about') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Despre noi
                    </a>
                    <a href="{{ route('page.contact') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Contact
                    </a>
                    <a href="{{ route('page.terms') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Termeni &amp; condiții
                    </a>
                    <a href="{{ route('page.cookies') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Politica cookies
                    </a>
                    <button type="button"
                            onclick="window.openCookieSettings && window.openCookieSettings()"
                            class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Setari cookies
                    </button>
                    <a href="{{ route('page.privacy') }}"
                       class="px-3 py-1 rounded-full bg-gray-100/80 dark:bg-[#18181B] text-[11px] hover:bg-[#C81424] hover:text-white transition">
                        Confidențialitate
                    </a>
                </nav>
            </div>

            {{-- Rând 2: Linie fină + text mic --}}
            <div class="mt-3 pt-3 border-t border-dashed border-gray-200 dark:border-gray-800 flex flex-col md:flex-row md:items-center md:justify-between gap-2 text-[10px] text-gray-400 dark:text-gray-500">
                <p class="text-center md:text-left">
                    &copy; {{ date('Y') }} iaAuto.ro – Toate drepturile rezervate.
                </p>
                <p class="text-center md:text-right">
                    Platformă de anunțuri auto. Verifică actele, istoricul și vânzătorul înainte de cumpărare.
                </p>
            </div>
        </div>
    </footer>

    <x-cookie-consent />

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
    let lastScrollY = 0;
    const nav = document.getElementById('main-nav');
    const logo = document.getElementById('logo-img');
    const mobileViewportQuery = window.matchMedia('(max-width: 1023px)');
    let isMobileViewport = mobileViewportQuery.matches;
    let navScrollTicking = false;
    const shouldAutoHideMobileNav = isHomepage || !!document.getElementById('listing-actions-bar');
    let mobileNavHiddenOffset = 0;
    let mobileNavHeight = nav ? Math.ceil(nav.offsetHeight) : 56;

    function setMobileNavOffset(hiddenOffset) {
        mobileNavHeight = nav ? Math.ceil(nav.offsetHeight) || mobileNavHeight : mobileNavHeight;
        mobileNavHiddenOffset = Math.max(0, Math.min(hiddenOffset, mobileNavHeight));
        const visibleHeight = Math.max(0, mobileNavHeight - mobileNavHiddenOffset);
        const hidden = mobileNavHiddenOffset >= mobileNavHeight - 1;

        nav.dataset.mobileHidden = hidden ? 'true' : 'false';
        nav.dataset.mobileVisibleHeight = String(visibleHeight);
        nav.style.transform = `translateY(-${mobileNavHiddenOffset}px)`;
        window.dispatchEvent(new CustomEvent('main-nav-visibility-change', {
            detail: { hidden, visibleHeight, hiddenOffset: mobileNavHiddenOffset }
        }));
    }

    function updateMobileNavTransitionMode() {
        if (shouldAutoHideMobileNav && isMobileViewport) {
            nav.style.transitionProperty = 'height, box-shadow, border-color, background-color';
        } else {
            nav.style.transitionProperty = '';
        }
    }

    function handleNavScroll() {
        const currentScrollY = window.scrollY;

        // --- 1. LOGICA DE DIMENSIUNE (SHRINK: Afectează doar Desktop) ---
        if (!isMobileViewport) {
            if (currentScrollY > 20) {
                // Desktop Scroll Jos -> Compact (h-14)
                nav.classList.remove('md:h-[72px]');
                nav.classList.add('md:h-14', 'shadow-xl');

                // Logo devine mai compact pe desktop.
                if (logo) {
                    logo.classList.remove('md:h-11');
                    logo.classList.add('md:h-9');
                }
            } else {
                // Desktop Sus -> Mare (h-18)
                nav.classList.add('md:h-[72px]');
                nav.classList.remove('md:h-14', 'shadow-xl');

                // Logo revine la marimea normala.
                if (logo) {
                    logo.classList.remove('md:h-9');
                    logo.classList.add('md:h-11');
                }
            }
        }

        // --- 2. LOGICA OLX (ASCUNDE/ARATĂ PE MOBIL) ---
        if (shouldAutoHideMobileNav && isMobileViewport) {

            const scrollDelta = currentScrollY - lastScrollY;

            if (currentScrollY < 10) {
                setMobileNavOffset(0);
                lastScrollY = currentScrollY;
                navScrollTicking = false;
                return;
            }

            if (scrollDelta !== 0) {
                setMobileNavOffset(mobileNavHiddenOffset + scrollDelta);
            }
        } else {
            setMobileNavOffset(0);
        }

        lastScrollY = currentScrollY;
        navScrollTicking = false;
    }

    window.addEventListener('resize', function() {
        isMobileViewport = mobileViewportQuery.matches;
        mobileNavHeight = nav ? Math.ceil(nav.offsetHeight) || mobileNavHeight : mobileNavHeight;
        updateMobileNavTransitionMode();
        if (!isMobileViewport || !shouldAutoHideMobileNav) {
            setMobileNavOffset(0);
        }
    }, { passive: true });

    updateMobileNavTransitionMode();

    window.addEventListener('scroll', function() {
        if (navScrollTicking) return;
        navScrollTicking = true;
        window.requestAnimationFrame(handleNavScroll);
    }, { passive: true });

    @auth
    function toggleAccountMenu() {
        const menu = document.getElementById('account-menu');
        const button = document.getElementById('account-menu-button');
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden');
        menu.classList.toggle('hidden', !isHidden);
        button?.setAttribute('aria-expanded', isHidden ? 'true' : 'false');
    }

    document.addEventListener('click', function(event) {
        const wrap = document.getElementById('account-menu-wrap');
        const menu = document.getElementById('account-menu');
        const button = document.getElementById('account-menu-button');

        if (!wrap || !menu || wrap.contains(event.target)) return;
        menu.classList.add('hidden');
        button?.setAttribute('aria-expanded', 'false');
    });

    function updateGlobalUnreadBadges(count) {
        document.querySelectorAll('[data-unread-badge]').forEach((badge) => {
            if (!badge) return;
            if (count > 0) {
                badge.textContent = count > 99 ? '99+' : count;
                badge.classList.remove('hidden');
                if (!badge.classList.contains('inline-flex')) {
                    badge.classList.add('inline-flex');
                }
            } else {
                badge.textContent = '';
                badge.classList.add('hidden');
                badge.classList.remove('inline-flex');
            }
        });
    }

    let unreadMessagesCountRequestInFlight = false;

    function refreshUnreadMessagesCount() {
        if (unreadMessagesCountRequestInFlight) return;

        unreadMessagesCountRequestInFlight = true;

        fetch("{{ route('messages.unreadCount') }}", {
            headers: { 'Accept': 'application/json' }
        })
            .then((response) => response.ok ? response.json() : null)
            .then((data) => {
                if (!data) return;
                updateGlobalUnreadBadges(data.unread_count || 0);
            })
            .catch(() => {})
            .then(() => {
                unreadMessagesCountRequestInFlight = false;
            });
    }

    const shouldRefreshUnreadMessagesOnce = @json(request()->routeIs('account.index'));
    if (shouldRefreshUnreadMessagesOnce) {
        refreshUnreadMessagesCount();
    }
    @endauth
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
