import './bootstrap';
import { initFlowbite } from 'flowbite';

const activeTabId = new URLSearchParams(window.location.search).get('_tab');

if (activeTabId) {
    const appendTabInput = (form) => {
        if (!form || form.querySelector('input[name="_tab"]')) {
            return;
        }
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = '_tab';
        input.value = activeTabId;
        form.appendChild(input);
    };

    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('form').forEach(appendTabInput);
    });

    document.addEventListener('submit', (event) => {
        if (event.target instanceof HTMLFormElement) {
            appendTabInput(event.target);
        }
    }, true);

    const originalFetch = window.fetch.bind(window);
    window.fetch = (input, init = {}) => {
        if (typeof input === 'string' && input.startsWith('/')) {
            const url = new URL(input, window.location.origin);
            if (!url.searchParams.has('_tab')) {
                url.searchParams.set('_tab', activeTabId);
            }
            return originalFetch(url.toString(), init);
        }

        if (input instanceof URL && input.origin === window.location.origin && !input.searchParams.has('_tab')) {
            input.searchParams.set('_tab', activeTabId);
        }

        return originalFetch(input, init);
    };
}

// Initialize Flowbite components
document.addEventListener('DOMContentLoaded', () => {
    initFlowbite();
});

// Also initialize on dynamic content loads
const observer = new MutationObserver(() => {
    initFlowbite();
});

observer.observe(document.body, {
    childList: true,
    subtree: true
});

const loadFrontDeskScanner = () => {
    if (!document.getElementById('frontdesk-page')) {
        return;
    }

    import('./frontdesk-scanner.js')
        .then(({ initializeFrontDeskScanner }) => initializeFrontDeskScanner())
        .catch((error) => {
            console.error('Gagal memuat pemindai QR:', error);
            const status = document.getElementById('scanner-status');

            if (status) {
                status.textContent = 'Scanner gagal dimuat. Gunakan input kode reservasi manual.';
                status.classList.remove('text-white/70', 'text-emerald-300', 'text-amber-300');
                status.classList.add('text-red-300');
            }
        });
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', loadFrontDeskScanner, { once: true });
} else {
    loadFrontDeskScanner();
}

// Mobile and tablet sidebar drawer. Keeps navigation usable below desktop width.

const setMobileSidebar = (open) => {
    const sidebar = document.getElementById('app-sidebar');
    const backdrop = document.querySelector('.app-sidebar-backdrop');

    if (open && window.matchMedia('(max-width: 1279px)').matches) {
        document.body.classList.remove('sidebar-collapsed');
    }

    document.body.classList.toggle('mobile-sidebar-open', open);
    document.querySelectorAll('[data-mobile-sidebar-open]').forEach((button) => {
        button.setAttribute('aria-expanded', String(open));
    });
    sidebar?.setAttribute('aria-hidden', String(!open));
    backdrop?.setAttribute('aria-hidden', String(!open));
};

const handleMobileSidebarClick = (event) => {
    if (!(event.target instanceof Element)) {
        return;
    }

    const openButton = event.target.closest('[data-mobile-sidebar-open]');
    const closeButton = event.target.closest('[data-mobile-sidebar-close]');
    const sidebarLink = event.target.closest('#app-sidebar a');

    if (openButton) {
        event.preventDefault();
        setMobileSidebar(true);
        return;
    }

    if (closeButton || sidebarLink) {
        setMobileSidebar(false);
    }
};

document.addEventListener('click', handleMobileSidebarClick);

document.addEventListener('keydown', (event) => {
    if (event.key === 'Escape') {
        setMobileSidebar(false);
    }
});

window.addEventListener('resize', () => {
    if (window.matchMedia('(min-width: 1280px)').matches) {
        setMobileSidebar(false);
    }
});
