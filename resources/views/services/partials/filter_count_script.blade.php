let filterCountTimer = null;
let filterCountController = null;
let filterCountRequestId = 0;

function updateFilterSubmitCount(total) {
    const numericTotal = Math.max(0, Number(total) || 0);
    const normalizedTotal = numericTotal.toLocaleString('ro-RO');
    const listingNoun = numericTotal === 1 ? 'anunț' : 'anunțuri';
    const submitLabel = numericTotal === 0
        ? 'Niciun anunț găsit'
        : `Caută ${normalizedTotal} ${listingNoun}`;

    document.querySelectorAll('[data-filter-submit-count]').forEach((label) => {
        label.textContent = submitLabel;
    });
}

function requestFilterCount() {
    window.clearTimeout(filterCountTimer);

    filterCountTimer = window.setTimeout(() => {
        filterCountController?.abort();

        const requestId = ++filterCountRequestId;
        const controller = new AbortController();
        const url = new URL(buildSearchUrl(), window.location.origin);
        url.searchParams.set('count_only', '1');
        filterCountController = controller;

        fetch(url.toString(), {
            credentials: 'omit',
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            signal: controller.signal,
        })
            .then((response) => {
                if (!response.ok) throw new Error(`Filter count request failed: ${response.status}`);
                return response.json();
            })
            .then((data) => {
                if (requestId !== filterCountRequestId) return;

                if (typeof data.total !== 'undefined') updateFilterSubmitCount(data.total);
                if (data.facets) window.updateFilterFacets?.(data.facets);
            })
            .catch((error) => {
                if (error.name !== 'AbortError') console.error(error);
            })
            .finally(() => {
                if (requestId === filterCountRequestId) filterCountController = null;
            });
    }, 250);
}
