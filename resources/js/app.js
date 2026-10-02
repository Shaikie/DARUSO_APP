import Alpine from 'alpinejs';
import * as bootstrap from 'bootstrap';

window.Alpine = Alpine;
window.bootstrap = bootstrap;

Alpine.start();

const sidebar = document.getElementById('sidebar-wrapper');
const toggle = document.getElementById('sidebar-toggle');
const closeButton = document.getElementById('sidebar-close');
const overlay = document.getElementById('daruso-mobile-overlay');

const openSidebar = () => {
    sidebar?.classList.add('is-open');
    overlay?.classList.add('is-visible');
    document.body.classList.add('daruso-nav-open');
};

const closeSidebar = () => {
    sidebar?.classList.remove('is-open');
    overlay?.classList.remove('is-visible');
    document.body.classList.remove('daruso-nav-open');
};

toggle?.addEventListener('click', openSidebar);
closeButton?.addEventListener('click', closeSidebar);
overlay?.addEventListener('click', closeSidebar);

document.querySelectorAll('#sidebar-wrapper a').forEach((link) => {
    link.addEventListener('click', () => {
        if (window.matchMedia('(max-width: 991.98px)').matches) closeSidebar();
    });
});

document.addEventListener('submit', (event) => {
    const form = event.target;
    if (form.dataset?.confirm && !window.confirm(form.dataset.confirm)) {
        event.preventDefault();
    }
});