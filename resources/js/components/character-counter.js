function formatNumber(value) {
    return new Intl.NumberFormat('ro-RO').format(value);
}

function updateCounter(field) {
    const maxLength = Number(field.getAttribute('maxlength'));
    const targetId = field.dataset.characterCounterTarget;
    const target = targetId ? document.getElementById(targetId) : null;

    if (!target || !Number.isFinite(maxLength) || maxLength <= 0) {
        return;
    }

    const remaining = Math.max(maxLength - field.value.length, 0);
    target.textContent = formatNumber(remaining);
    target.dataset.remaining = String(remaining);
    target.parentElement?.classList.toggle('text-red-600', remaining === 0);
    target.parentElement?.classList.toggle('dark:text-red-300', remaining === 0);
}

function initCharacterCounters(scope = document) {
    scope.querySelectorAll('[data-character-counter]').forEach((field) => {
        updateCounter(field);
        field.addEventListener('input', () => updateCounter(field));
    });
}

document.addEventListener('DOMContentLoaded', () => initCharacterCounters());

window.iaCharacterCounters = {
    init: initCharacterCounters,
};
