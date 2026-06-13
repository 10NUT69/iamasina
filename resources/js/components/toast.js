function ensureToastElement() {
    let toast = document.getElementById('iaauto-toast');
    const isNew = !toast;
    const isDarkMode = window.matchMedia?.('(prefers-color-scheme: dark)').matches;

    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'iaauto-toast';
        document.body.appendChild(toast);
    }

    toast.className = '';
    Object.assign(toast.style, {
        position: 'fixed',
        left: '50%',
        right: 'auto',
        top: 'auto',
        bottom: 'calc(env(safe-area-inset-bottom, 0px) + 1.5rem)',
        zIndex: '2147483640',
        width: 'calc(100% - 2rem)',
        maxWidth: '28rem',
        transform: 'translateX(-50%)',
        borderRadius: '1rem',
        backgroundColor: isDarkMode ? '#1E1E1E' : '#FFFFFF',
        color: isDarkMode ? '#F5F5F5' : '#1F2937',
        padding: '0.85rem 1rem 0.85rem 1.1rem',
        textAlign: 'center',
        fontSize: '0.875rem',
        lineHeight: '1.35rem',
        fontWeight: '700',
        boxShadow: isDarkMode
            ? '0 18px 45px rgba(0, 0, 0, 0.38)'
            : '0 18px 45px rgba(15, 23, 42, 0.14)',
        border: isDarkMode
            ? '1px solid rgba(200, 20, 36, 0.35)'
            : '1px solid rgba(200, 20, 36, 0.18)',
        borderLeft: '4px solid #C81424',
        opacity: toast.style.opacity || '0',
        transition: 'opacity 180ms ease',
        pointerEvents: 'none',
    });

    if (isNew) {
        toast.style.display = 'none';
    }

    return toast;
}

function runToastHideCallback(detail = {}) {
    const onHide = window.__iaAutoToastOnHide;
    window.__iaAutoToastOnHide = null;

    if (typeof onHide !== 'function') {
        return;
    }

    try {
        onHide(detail);
    } catch (error) {
        console.error(error);
    }
}

window.iaAutoToast = function iaAutoToast(message, options = {}) {
    const toast = ensureToastElement();
    const duration = Number(options.duration || 5000);

    clearTimeout(window.__iaAutoToastTimeout);
    clearTimeout(window.__iaAutoToastHideTimeout);
    runToastHideCallback({ reason: 'replaced' });
    window.__iaAutoToastOnHide = typeof options.onHide === 'function' ? options.onHide : null;

    toast.textContent = message;
    toast.style.display = 'block';
    toast.style.opacity = '1';

    window.__iaAutoToastTimeout = setTimeout(() => {
        toast.style.opacity = '0';

        window.__iaAutoToastHideTimeout = setTimeout(() => {
            toast.style.display = 'none';
            runToastHideCallback({ reason: 'timeout' });
        }, 180);
    }, duration);
};
