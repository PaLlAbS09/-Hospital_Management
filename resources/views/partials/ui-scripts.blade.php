{{-- Shared non-blocking UI behaviours: modal focus trap + auto-expanding textareas.
Included by layouts.app, layouts.guest and layouts.public. --}}
<script>
(function () {
    'use strict';

    /* Auto-expanding textareas: <textarea data-autosize> grows with content. */
    const fit = (el) => {
        el.style.height = 'auto';
        el.style.height = Math.min(el.scrollHeight, 420) + 'px';
    };

    document.querySelectorAll('textarea[data-autosize]').forEach((el) => {
        el.classList.add('hms-autosize');
        fit(el);
        el.addEventListener('input', () => fit(el));
    });

    /* Keyboard focus trap for every Bootstrap modal: Tab cycles inside,
       Escape dismisses, focus returns to the element that opened it. */
    let lastTrigger = null;

    document.addEventListener('click', (event) => {
        const opener = event.target.closest('[data-bs-toggle="modal"]');
        if (opener) {
            lastTrigger = opener;
        }
    });

    const focusableSelector = [
        'a[href]:not([disabled])',
        'button:not([disabled])',
        'textarea:not([disabled])',
        'input:not([disabled])',
        'select:not([disabled])',
        '[tabindex]:not([tabindex="-1"])',
    ].join(', ');

    document.querySelectorAll('[data-hms-modal], .modal').forEach((modal) => {
        modal.addEventListener('shown.bs.modal', () => {
            const focusables = Array.from(modal.querySelectorAll(focusableSelector))
                .filter((el) => el.offsetParent !== null);
            (focusables[0] || modal).focus?.();
            modal.setAttribute('aria-hidden', 'false');
        });

        modal.addEventListener('keydown', (event) => {
            if (event.key !== 'Tab') {
                return;
            }
            const focusables = Array.from(modal.querySelectorAll(focusableSelector))
                .filter((el) => el.offsetParent !== null && !el.hasAttribute('disabled'));
            if (focusables.length === 0) {
                event.preventDefault();
                return;
            }
            const first = focusables[0];
            const last = focusables[focusables.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });

        modal.addEventListener('hidden.bs.modal', () => {
            modal.setAttribute('aria-hidden', 'true');
            if (lastTrigger && document.contains(lastTrigger)) {
                lastTrigger.focus();
            }
            lastTrigger = null;
        });
    });
})();
</script>
