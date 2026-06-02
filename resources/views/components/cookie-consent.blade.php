@once
    <div id="cookie-consent-banner" class="hidden fixed inset-x-0 bottom-0 z-[80] px-3 pb-2 sm:px-4 sm:pb-6">
        <div class="mx-auto max-w-5xl rounded-2xl border border-gray-200 bg-white/95 p-3 shadow-2xl shadow-black/10 backdrop-blur dark:border-[#333333] dark:bg-[#1E1E1E]/95 sm:p-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <p class="text-sm leading-relaxed text-gray-700 dark:text-gray-200 md:flex-1">
                    Folosim cookie-uri pentru funcționarea corectă a site-ului și îmbunătățirea experienței tale.
                </p>

                <div class="grid grid-cols-2 gap-2 md:flex md:flex-row md:shrink-0">
                    <button type="button"
                            id="cookie-consent-accept"
                            class="inline-flex h-11 min-w-0 items-center justify-center rounded-xl bg-[#C81424] px-4 text-sm font-bold text-white transition hover:bg-[#a6101d]">
                        Acceptă
                    </button>
                    <button type="button"
                            id="cookie-consent-settings"
                            class="inline-flex h-11 min-w-0 items-center justify-center rounded-xl border border-[#C81424] bg-white px-4 text-sm font-bold text-[#C81424] transition hover:bg-red-50 dark:border-red-700 dark:bg-[#1E1E1E] dark:text-red-200 dark:hover:bg-[#2a1013]">
                        Setări
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div id="cookie-consent-panel" class="hidden fixed inset-x-0 bottom-0 z-[90] px-4 pb-4 sm:pb-6" role="dialog" aria-modal="true" aria-labelledby="cookie-consent-title">
        <div class="mx-auto max-w-2xl rounded-2xl border border-gray-200 bg-white p-5 shadow-2xl shadow-black/20 dark:border-[#333333] dark:bg-[#1E1E1E] sm:p-6">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <h2 id="cookie-consent-title" class="text-lg font-black text-gray-900 dark:text-white">
                        Setări cookies
                    </h2>
                    <p class="mt-1 text-sm leading-relaxed text-gray-600 dark:text-gray-300">
                        Alege ce tipuri de cookie-uri opționale permiți. Cookie-urile necesare rămân active pentru funcționarea site-ului.
                    </p>
                </div>
                <button type="button"
                        id="cookie-consent-close"
                        class="rounded-full p-2 text-gray-400 transition hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-[#2C2C2C] dark:hover:text-gray-100"
                        aria-label="Închide setările cookies">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <div class="mt-5 space-y-3">
                <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 bg-gray-50 p-4 dark:border-[#333333] dark:bg-[#18181B]">
                    <span>
                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Cookie-uri necesare</span>
                        <span class="mt-1 block text-xs leading-relaxed text-gray-500 dark:text-gray-400">Active mereu pentru securitate, sesiune și funcționalități de bază.</span>
                    </span>
                    <input type="checkbox" checked disabled class="h-5 w-5 rounded border-gray-300 text-[#C81424] opacity-70">
                </label>

                <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 dark:border-[#333333]">
                    <span>
                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Analytics</span>
                        <span class="mt-1 block text-xs leading-relaxed text-gray-500 dark:text-gray-400">Ne ajută să înțelegem cum este folosit site-ul și ce putem îmbunătăți.</span>
                    </span>
                    <input id="cookie-consent-analytics" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-[#C81424]">
                </label>

                <label class="flex items-center justify-between gap-4 rounded-xl border border-gray-200 p-4 dark:border-[#333333]">
                    <span>
                        <span class="block text-sm font-bold text-gray-900 dark:text-white">Marketing</span>
                        <span class="mt-1 block text-xs leading-relaxed text-gray-500 dark:text-gray-400">Permite folosirea unor coduri pentru reclame sau măsurarea campaniilor.</span>
                    </span>
                    <input id="cookie-consent-marketing" type="checkbox" class="h-5 w-5 rounded border-gray-300 text-[#C81424]">
                </label>
            </div>

            <div class="mt-5 flex flex-col gap-2 sm:flex-row sm:justify-end">
                <button type="button"
                        id="cookie-consent-save"
                        class="inline-flex items-center justify-center rounded-xl bg-[#C81424] px-5 py-2.5 text-sm font-bold text-white transition hover:bg-[#a6101d]">
                    Salvează preferințele
                </button>
            </div>
        </div>
    </div>

    <script>
        (function () {
            const storageKey = 'iaauto_cookie_consent';
            const analyticsId = @json(config('services.google.analytics_id'));
            let analyticsLoaded = false;
            let marketingLoaded = false;

            function getStoredConsent() {
                try {
                    const stored = localStorage.getItem(storageKey);
                    return stored ? JSON.parse(stored) : null;
                } catch (error) {
                    return null;
                }
            }

            function storeConsent(consent) {
                try {
                    localStorage.setItem(storageKey, JSON.stringify(consent));
                } catch (error) {
                    // Site-ul ramane functional si daca localStorage nu este disponibil.
                }
            }

            function buildConsent(analytics, marketing) {
                return {
                    necessary: true,
                    analytics: Boolean(analytics),
                    marketing: Boolean(marketing),
                    acceptedAt: new Date().toISOString(),
                };
            }

            function showElement(element) {
                if (element) {
                    element.classList.remove('hidden');
                }
            }

            function hideElement(element) {
                if (element) {
                    element.classList.add('hidden');
                }
            }

            window.loadAnalyticsScripts = function () {
                if (analyticsLoaded || !analyticsId) {
                    return;
                }

                analyticsLoaded = true;
                window.dataLayer = window.dataLayer || [];
                window.gtag = window.gtag || function () {
                    window.dataLayer.push(arguments);
                };

                const script = document.createElement('script');
                script.async = true;
                script.src = 'https://www.googletagmanager.com/gtag/js?id=' + encodeURIComponent(analyticsId);
                script.onload = function () {
                    window.gtag('js', new Date());
                    window.gtag('config', analyticsId);
                };
                document.head.appendChild(script);
            };

            window.loadMarketingScripts = function () {
                if (marketingLoaded) {
                    return;
                }

                marketingLoaded = true;
                // Adauga aici ulterior coduri precum Google Ads sau Facebook Pixel.
            };

            function applyConsent(consent) {
                if (!consent) {
                    return;
                }

                if (consent.analytics) {
                    window.loadAnalyticsScripts();
                }

                if (consent.marketing) {
                    window.loadMarketingScripts();
                }
            }

            window.openCookieSettings = function () {
                const consent = getStoredConsent();
                const analyticsInput = document.getElementById('cookie-consent-analytics');
                const marketingInput = document.getElementById('cookie-consent-marketing');

                if (analyticsInput) {
                    analyticsInput.checked = Boolean(consent && consent.analytics);
                }

                if (marketingInput) {
                    marketingInput.checked = Boolean(consent && consent.marketing);
                }

                hideElement(document.getElementById('cookie-consent-banner'));
                showElement(document.getElementById('cookie-consent-panel'));
            };

            function initCookieConsent() {
                const banner = document.getElementById('cookie-consent-banner');
                const panel = document.getElementById('cookie-consent-panel');
                const acceptButton = document.getElementById('cookie-consent-accept');
                const settingsButton = document.getElementById('cookie-consent-settings');
                const closeButton = document.getElementById('cookie-consent-close');
                const saveButton = document.getElementById('cookie-consent-save');
                const analyticsInput = document.getElementById('cookie-consent-analytics');
                const marketingInput = document.getElementById('cookie-consent-marketing');
                const storedConsent = getStoredConsent();

                if (storedConsent) {
                    applyConsent(storedConsent);
                } else {
                    showElement(banner);
                }

                acceptButton?.addEventListener('click', function () {
                    const consent = buildConsent(true, true);
                    storeConsent(consent);
                    applyConsent(consent);
                    hideElement(banner);
                    hideElement(panel);
                });

                settingsButton?.addEventListener('click', window.openCookieSettings);

                closeButton?.addEventListener('click', function () {
                    hideElement(panel);

                    if (!getStoredConsent()) {
                        showElement(banner);
                    }
                });

                saveButton?.addEventListener('click', function () {
                    const consent = buildConsent(analyticsInput?.checked, marketingInput?.checked);
                    storeConsent(consent);
                    applyConsent(consent);
                    hideElement(banner);
                    hideElement(panel);
                });
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initCookieConsent);
            } else {
                initCookieConsent();
            }
        })();
    </script>
@endonce
