const OFFLINE_CACHE = 'iaauto-offline-v1';
const OFFLINE_URL = '/offline.html';

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(OFFLINE_CACHE)
            .then((cache) => cache.add(OFFLINE_URL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys
                    .filter((key) => key.startsWith('iaauto-offline-') && key !== OFFLINE_CACHE)
                    .map((key) => caches.delete(key))
            ))
            .then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET' || request.mode !== 'navigate') return;

    const url = new URL(request.url);
    if (url.origin !== self.location.origin) return;

    // Only public browsing receives the offline page. Forms, account and admin
    // navigations remain entirely outside the worker's fetch handling.
    const isPublicBrowse = url.pathname === '/'
        || url.pathname === '/anunturi-auto-de-vanzare'
        || (url.pathname.startsWith('/anunturi-auto-de-vanzare/')
            && url.pathname !== '/anunturi-auto-de-vanzare/adauga-anunt'
            && !url.pathname.endsWith('/edit'));

    if (!isPublicBrowse) return;

    event.respondWith(
        fetch(request).catch(async () => {
            return (await caches.match(OFFLINE_URL)) || Response.error();
        })
    );
});
