const STORAGE_KEY = 'iaauto_guest_favorites';

function config() {
    return window.iaAutoConfig || {};
}

function csrfToken() {
    return config().csrfToken || document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function readFavoriteIds() {
    try {
        const value = JSON.parse(localStorage.getItem(STORAGE_KEY) || '[]');
        return Array.isArray(value)
            ? value.map((id) => Number(id)).filter((id) => Number.isInteger(id) && id > 0)
            : [];
    } catch {
        return [];
    }
}

function writeFavoriteIds(ids) {
    const normalized = [...new Set(ids.map((id) => Number(id)).filter((id) => Number.isInteger(id) && id > 0))];

    try {
        localStorage.setItem(STORAGE_KEY, JSON.stringify(normalized));
    } catch {
        return readFavoriteIds();
    }

    return normalized;
}

function classListFrom(value, fallback) {
    return String(value || fallback || '')
        .split(/\s+/)
        .map((item) => item.trim())
        .filter(Boolean);
}

function updateLabel(button, isFavorite) {
    const label = button.getAttribute('aria-label') || '';
    const target = label.includes(':') ? label.split(':').slice(1).join(':').trim() : '';
    button.setAttribute('aria-label', `${isFavorite ? 'Scoate de la favorite' : 'Adauga la favorite'}${target ? ': ' + target : ''}`);
    button.setAttribute('aria-pressed', isFavorite ? 'true' : 'false');
}

function applyButtonState(button, isFavorite) {
    if (!button) return;

    const icon = button.querySelector('svg');
    if (!icon) return;

    const activeClasses = classListFrom(button.dataset.favoriteActiveClass, 'text-[#C81424] fill-[#C81424]');
    const emptyClasses = classListFrom(button.dataset.favoriteEmptyClass, 'text-gray-400 fill-none');
    const allManagedClasses = [...new Set([
        ...activeClasses,
        ...emptyClasses,
        'text-red-500',
        'fill-red-500',
        'text-white',
        'text-gray-600',
        'dark:text-gray-300',
        'fill-none',
        'scale-110',
        'scale-125',
    ])];

    icon.classList.remove(...allManagedClasses);
    icon.classList.add(...(isFavorite ? activeClasses : emptyClasses));
    updateLabel(button, isFavorite);
}

function applyStateForService(serviceId, isFavorite) {
    document
        .querySelectorAll(`[data-favorite-service-id="${serviceId}"]`)
        .forEach((button) => applyButtonState(button, isFavorite));
}

function refresh(root = document) {
    if (config().isAuthenticated) return;

    const ids = new Set(readFavoriteIds());
    root.querySelectorAll('[data-favorite-service-id]').forEach((button) => {
        applyButtonState(button, ids.has(Number(button.dataset.favoriteServiceId)));
    });
}

async function toggleRemote(button, serviceId) {
    const wasFavorite = button.getAttribute('aria-pressed') === 'true';
    const nextFavorite = !wasFavorite;

    applyStateForService(serviceId, nextFavorite);

    try {
        const response = await fetch(config().favoriteToggleUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ service_id: serviceId }),
        });

        if (!response.ok) {
            throw new Error('Favorite toggle failed');
        }

        const data = await response.json();
        const isFavorite = data.status === 'added';
        applyStateForService(serviceId, isFavorite);
        window.iaAutoToast?.(
            isFavorite ? 'Anunt adaugat la favorite.' : 'Anunt scos din favorite.',
            { duration: 3500 }
        );
    } catch (error) {
        applyStateForService(serviceId, wasFavorite);
        window.iaAutoToast?.('Nu am putut actualiza favoritele. Incearca din nou.', { duration: 5000 });
        console.error(error);
    }
}

function toggleLocal(button, serviceId) {
    const ids = readFavoriteIds();
    const isFavorite = ids.includes(serviceId);
    const nextIds = isFavorite ? ids.filter((id) => id !== serviceId) : [...ids, serviceId];

    writeFavoriteIds(nextIds);
    applyStateForService(serviceId, !isFavorite);

    window.iaAutoToast?.(
        !isFavorite
            ? 'Anunt salvat in favorite. Daca iti creezi cont, il importam automat.'
            : 'Anunt scos din favorite.',
        { duration: 5000 }
    );
}

async function syncLocalFavorites() {
    if (!config().isAuthenticated || !config().favoriteImportUrl) return;

    const ids = readFavoriteIds();
    if (ids.length === 0) return;

    try {
        const response = await fetch(config().favoriteImportUrl, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            body: JSON.stringify({ service_ids: ids }),
        });

        if (response.ok) {
            const data = await response.json().catch(() => ({}));
            writeFavoriteIds([]);
            window.dispatchEvent(new CustomEvent('iaauto:favorites-imported', { detail: data }));

            const isAccountFavorites = window.location.pathname.includes('/contul-meu')
                && new URLSearchParams(window.location.search).get('tab') === 'favorite';

            if (isAccountFavorites && Number(data.imported || 0) > 0) {
                window.location.reload();
            }
        }
    } catch (error) {
        console.error(error);
    }
}

window.iaAutoFavorites = {
    ids: readFavoriteIds,
    refresh,
    sync: syncLocalFavorites,
    toggle(button, serviceId) {
        const normalizedId = Number(serviceId);
        if (!Number.isInteger(normalizedId) || normalizedId <= 0) return;

        if (config().isAuthenticated) {
            toggleRemote(button, normalizedId);
            return;
        }

        toggleLocal(button, normalizedId);
    },
};

window.toggleHeart = function toggleHeart(button, serviceId) {
    window.iaAutoFavorites?.toggle(button, serviceId);
};

document.addEventListener('DOMContentLoaded', () => {
    refresh(document);
    syncLocalFavorites();
});
