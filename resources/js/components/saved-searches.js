const STORAGE_KEY = 'iaauto_guest_saved_searches';
const MAX_LOCAL_SEARCHES = 25;

function config() {
    return window.iaAutoConfig || {};
}

function csrfToken() {
    return config().csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function normalizePayload(payload) {
    if (!payload || typeof payload !== 'object') return null;

    const url = String(payload.url || '').trim();
    if (!url) return null;

    const filters = payload.filters && typeof payload.filters === 'object' && !Array.isArray(payload.filters)
        ? payload.filters
        : {};

    return {
        name: String(payload.name || '').trim().slice(0, 160),
        url,
        filters,
        saved_at: payload.saved_at || new Date().toISOString(),
    };
}

function searchKey(payload) {
    return `${payload.url}|${JSON.stringify(payload.filters || {})}`;
}

function readLocalSearches() {
    try {
        const value = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        if (!Array.isArray(value)) return [];

        return value
            .map(normalizePayload)
            .filter(Boolean);
    } catch {
        return [];
    }
}

function writeLocalSearches(searches) {
    const normalized = searches
        .map(normalizePayload)
        .filter(Boolean)
        .slice(0, MAX_LOCAL_SEARCHES);

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(normalized));
    } catch {
        return readLocalSearches();
    }

    return normalized;
}

function saveLocalSearch(payload) {
    const normalized = normalizePayload(payload);
    if (!normalized) return [];

    const nextKey = searchKey(normalized);
    const existing = readLocalSearches().filter((item) => searchKey(item) !== nextKey);

    return writeLocalSearches([normalized, ...existing]);
}

async function saveRemoteSearch(payload) {
    const response = await fetch(config().savedSearchStoreUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        throw new Error('Saved search failed');
    }

    return response.json();
}

async function syncLocalSavedSearches() {
    if (!config().isAuthenticated || !config().savedSearchImportUrl) return;

    const searches = readLocalSearches();
    if (searches.length === 0) return;

    try {
        const response = await fetch(config().savedSearchImportUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ saved_searches: searches }),
        });

        if (response.ok) {
            const data = await response.json().catch(() => ({}));
            writeLocalSearches([]);
            window.dispatchEvent(new CustomEvent('iaauto:saved-searches-imported', { detail: data }));

            const isAccountSavedSearches = window.location.pathname.includes('/contul-meu')
                && new URLSearchParams(window.location.search).get('tab') === 'cautari';

            if (isAccountSavedSearches && Number(data.processed || data.imported || 0) > 0) {
                window.location.reload();
            }
        }
    } catch (error) {
        console.error(error);
    }
}

window.iaAutoSavedSearches = {
    items: readLocalSearches,
    sync: syncLocalSavedSearches,

    async save(payload) {
        if (!config().isAuthenticated) {
            saveLocalSearch(payload);
            window.iaAutoToast?.('Am salvat. Pentru a putea reveni la cautarile favorite este nevoie de cont', { duration: 5000 });
            return { status: 'guest' };
        }

        try {
            const data = await saveRemoteSearch(payload);
            window.iaAutoToast?.(
                data.status === 'updated' ? 'Cautarea era deja salvata la favorite.' : 'Cautarea a fost salvata la favorite.',
                { duration: 5000 }
            );

            return data;
        } catch (error) {
            console.error(error);
            window.iaAutoToast?.('Nu am putut salva cautarea. Incearca din nou.', { duration: 5000 });
            return { status: 'error' };
        }
    },
};

document.addEventListener('DOMContentLoaded', () => {
    syncLocalSavedSearches();
});
