// PWA + dark mode + scroll reveal + install prompt + mobile dock + gallery lightbox

const updateThemeColor = () => {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) return;
    meta.setAttribute('content', document.documentElement.classList.contains('dark') ? '#131316' : '#1a2332');
};

const initDarkMode = () => {
    const stored = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;

    if (stored === 'dark' || (!stored && prefersDark)) {
        document.documentElement.classList.add('dark');
    }

    updateThemeColor();
};

const toggleDarkMode = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeColor();
};

window.toggleDarkMode = toggleDarkMode;

window.galleryLightbox = () => ({
    lightbox: false,
    current: 0,
    photos: [],
    init() {
        this.photos = [...this.$el.querySelectorAll('[data-gallery-photo]')].map((el) => ({
            src: el.dataset.src,
            title: el.dataset.title || '',
            caption: el.dataset.caption || '',
            alt: el.dataset.alt || '',
        }));
    },
    open(index) {
        this.current = index;
        this.lightbox = true;
    },
    next() {
        if (! this.photos.length) return;
        this.current = (this.current + 1) % this.photos.length;
    },
    prev() {
        if (! this.photos.length) return;
        this.current = (this.current - 1 + this.photos.length) % this.photos.length;
    },
});

const initScrollReveal = () => {
    const elements = document.querySelectorAll('.animate-fade-up, .glass-card, .bento-grid > *, .bento-tile, .page-section, .feed-card, .gallery-tile, .sermon-card, .location-card, .resource-row, .past-event-chip, .quote-gen-z, .cta-gen-z, .form-gen-z');

    if (!('IntersectionObserver' in window) || ! elements.length) return;

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.08, rootMargin: '0px 0px -32px 0px' }
    );

    elements.forEach((el) => {
        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            el.classList.add('is-visible');
            return;
        }

        observer.observe(el);
    });
};

const initMobileDock = () => {
    const toggle = document.getElementById('mobile-menu-toggle');
    if (!toggle) return;

    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        window.dispatchEvent(new CustomEvent('toggle-mobile-menu'));
    });
};

const initPWA = () => {
    if (!('serviceWorker' in navigator)) return;

    const registerWorker = () => {
        navigator.serviceWorker.register('/sw.js', { scope: '/' }).catch(() => {});
    };

    if ('requestIdleCallback' in window) {
        requestIdleCallback(registerWorker, { timeout: 3000 });
    } else {
        window.addEventListener('load', registerWorker, { once: true });
    }

    let deferredPrompt = null;
    const banner = document.getElementById('pwa-install-banner');
    const installBtn = document.getElementById('pwa-install-btn');
    const dismissBtn = document.getElementById('pwa-dismiss-btn');

    const showBanner = () => {
        if (!banner) return;
        banner.hidden = false;
        banner.setAttribute('aria-hidden', 'false');
        requestAnimationFrame(() => banner.classList.add('show'));
    };

    const hideBanner = () => {
        if (!banner) return;
        banner.classList.remove('show');
        banner.setAttribute('aria-hidden', 'true');
        banner.hidden = true;
    };

    window.addEventListener('beforeinstallprompt', (e) => {
        e.preventDefault();
        deferredPrompt = e;

        if (!localStorage.getItem('pwa-dismissed')) {
            showBanner();
        }
    });

    installBtn?.addEventListener('click', async () => {
        if (!deferredPrompt) return;
        deferredPrompt.prompt();
        await deferredPrompt.userChoice;
        deferredPrompt = null;
        hideBanner();
    });

    dismissBtn?.addEventListener('click', () => {
        localStorage.setItem('pwa-dismissed', '1');
        hideBanner();
    });
};

initDarkMode();
document.addEventListener('DOMContentLoaded', () => {
    initScrollReveal();
    initMobileDock();
    initPWA();
});
