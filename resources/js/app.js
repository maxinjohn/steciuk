// PWA + dark mode + scroll reveal + install prompt + mobile dock + gallery lightbox

const updateThemeColor = () => {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (!meta) return;
    meta.setAttribute('content', document.documentElement.classList.contains('dark') ? '#131316' : '#d4cabb');
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

const initLocationTabs = () => {
    document.querySelectorAll('[data-location-tabs]').forEach((root) => {
        const tabs = [...root.querySelectorAll('[data-location-tab]')];
        const panels = [...root.querySelectorAll('[data-location-panel]')];

        if (! tabs.length || ! panels.length) {
            return;
        }

        const activate = (index) => {
            tabs.forEach((tab) => {
                const isActive = Number(tab.dataset.locationIndex) === index;
                tab.classList.toggle('is-active', isActive);
                tab.setAttribute('aria-selected', isActive ? 'true' : 'false');
            });

            panels.forEach((panel) => {
                const isActive = Number(panel.dataset.locationIndex) === index;
                panel.hidden = ! isActive;
            });
        };

        tabs.forEach((tab) => {
            tab.addEventListener('click', () => {
                activate(Number(tab.dataset.locationIndex));
            });
        });

        activate(Number(tabs.find((tab) => tab.classList.contains('is-active'))?.dataset.locationIndex ?? 0));
    });
};

const initDesktopNav = () => {
    const items = document.querySelectorAll('[data-menu-item]');

    if (! items.length) {
        return;
    }

    const closeAll = (except = null) => {
        items.forEach((item) => {
            if (item === except) {
                return;
            }

            item.classList.remove('is-open');
            item.querySelector('[data-menu-trigger]')?.setAttribute('aria-expanded', 'false');
        });
    };

    items.forEach((item) => {
        const trigger = item.querySelector('[data-menu-trigger]');
        const panel = item.querySelector('[data-menu-panel]');

        if (! trigger || ! panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const willOpen = ! item.classList.contains('is-open');
            closeAll(willOpen ? item : null);

            item.classList.toggle('is-open', willOpen);
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });

        panel.addEventListener('click', (event) => {
            event.stopPropagation();
        });
    });

    document.addEventListener('click', () => closeAll());
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    });
};

const initScrollReveal = () => {
    const elements = document.querySelectorAll('.animate-fade-up, .glass-card, .bento-grid > *, .bento-tile, .page-section, .feed-card, .gallery-tile, .sermon-card, .location-card, .resource-row, .past-event-chip, .quote-gen-z, .cta-gen-z, .form-gen-z, .faith-pillar, .scripture-ribbon, .parish-action-card, .worship-rhythm-card, .evangelical-trust-chip');

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
    const closeButton = document.getElementById('mobile-menu-close');
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');

    if (! toggle || ! menu) {
        return;
    }

    const setOpen = (open) => {
        menu.classList.toggle('is-open', open);
        overlay?.classList.toggle('is-open', open);
        toggle.classList.toggle('is-active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        menu.setAttribute('aria-hidden', open ? 'false' : 'true');
        overlay?.setAttribute('aria-hidden', open ? 'false' : 'true');
        document.body.classList.toggle('mobile-menu-open', open);
    };

    toggle.addEventListener('click', (event) => {
        event.preventDefault();
        setOpen(! menu.classList.contains('is-open'));
    });

    closeButton?.addEventListener('click', () => setOpen(false));
    overlay?.addEventListener('click', () => setOpen(false));

    document.querySelectorAll('[data-close-mobile-menu]').forEach((element) => {
        element.addEventListener('click', () => setOpen(false));
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            setOpen(false);
        }
    });
};

const initMobileNav = () => {
    document.querySelectorAll('[data-mobile-nav-trigger]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const section = trigger.closest('[data-mobile-nav-section]');
            const panel = section?.querySelector('[data-mobile-nav-panel]');
            const expanded = trigger.getAttribute('aria-expanded') === 'true';
            const nextExpanded = ! expanded;

            trigger.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
            panel?.toggleAttribute('hidden', ! nextExpanded);
            trigger.querySelector('.menu-link-mobile-chevron')?.classList.toggle('rotate-180', nextExpanded);
        });
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
    initDesktopNav();
    initLocationTabs();
    initScrollReveal();
    initMobileDock();
    initMobileNav();
    initPWA();
});
