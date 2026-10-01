import Alpine from 'alpinejs';
import * as bootstrap from 'bootstrap';

window.Alpine = Alpine;
window.bootstrap = bootstrap;

Alpine.start();

/* ---------------------------------------------------------------------------
 | Sidebar visibility
 |---------------------------------------------------------------------------
 | The sidebar is sticky on desktop and off-canvas on small screens. Its state is
 | persisted per device so the preference survives navigation.
 |---------------------------------------------------------------------------*/

const sidebar = document.getElementById('sidebar-wrapper');
const toggle = document.getElementById('sidebar-toggle');

if (sidebar && toggle) {
    const storageKey = 'daruso.sidebar.hidden';
    const isMobile = () => window.matchMedia('(max-width: 991.98px)').matches;

    const apply = (hidden) => sidebar.classList.toggle('d-none', hidden);

    // Default: visible on desktop, hidden on mobile.
    const stored = window.localStorage.getItem(storageKey);
    apply(stored === null ? isMobile() : stored === '1');

    toggle.addEventListener('click', () => {
        const hidden = sidebar.classList.toggle('d-none');
        window.localStorage.setItem(storageKey, hidden ? '1' : '0');
    });

    // Tapping the content area dismisses the off-canvas sidebar on mobile.
    document.getElementById('page-content-wrapper')?.addEventListener('click', (event) => {
        if (!isMobile() || sidebar.classList.contains('d-none')) {
            return;
        }

        if (!event.target.closest('#sidebar-toggle')) {
            sidebar.classList.add('d-none');
            window.localStorage.setItem(storageKey, '1');
        }
    });
}

/* ---------------------------------------------------------------------------
 | Confirm-before-submit
 |---------------------------------------------------------------------------
 | Progressive enhancement only: the markup already sets onsubmit, so this
 | keeps the behaviour when a form is re-rendered by a partial update.
 |---------------------------------------------------------------------------*/

document.addEventListener('submit', (event) => {
    const form = event.target;

    if (form.dataset?.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});