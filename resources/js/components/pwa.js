// The worker only provides a public offline fallback; it never caches application pages.
if (import.meta.env.PROD && 'serviceWorker' in navigator && window.isSecureContext) {
    window.addEventListener('load', () => {
        navigator.serviceWorker.register('/sw.js', { updateViaCache: 'none' }).catch((error) => {
            console.warn('iaAuto offline support could not be enabled:', error);
        });
    });
}
