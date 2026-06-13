// PWA + dark mode + scroll reveal + install prompt + mobile dock + gallery lightbox

const updateThemeColor = () => {
    const meta = document.querySelector('meta[name="theme-color"]');
    if (! meta) {
        return;
    }

    const root = document.documentElement;
    const light = root.dataset.themeColorLight || '#d4cabb';
    const dark = root.dataset.themeColorDark || '#131316';

    meta.setAttribute('content', root.classList.contains('dark') ? dark : light);
};

const initDarkMode = () => {
    const stored = localStorage.getItem('theme');

    if (stored === 'dark') {
        document.documentElement.classList.add('dark');
    } else {
        document.documentElement.classList.remove('dark');
    }

    updateThemeColor();
    syncDarkModeToggleState();
};

const toggleDarkMode = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeColor();

    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        const label = button.querySelector('[data-theme-label]');
        if (label) {
            label.textContent = isDark ? 'Dark' : 'Light';
        }
    });
};

const syncDarkModeToggleState = () => {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        const label = button.querySelector('[data-theme-label]');
        if (label) {
            label.textContent = isDark ? 'Dark' : 'Light';
        }
    });
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
        document.body.classList.add('gallery-lightbox-open');
    },
    close() {
        this.lightbox = false;
        document.body.classList.remove('gallery-lightbox-open');
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

const ensureDesktopNavEndVisible = (shell) => {
    if (window.matchMedia('(max-width: 1299px)').matches) {
        return;
    }

    shell.scrollLeft = 0;

    if (shell.scrollWidth <= shell.clientWidth + 1) {
        return;
    }

    const menuItems = [...shell.querySelectorAll('[data-menu-item]')];
    const lastItem = menuItems.at(-1);

    if (! lastItem) {
        return;
    }

    const shellRect = shell.getBoundingClientRect();
    const lastRect = lastItem.getBoundingClientRect();

    if (lastRect.right <= shellRect.right - 4) {
        return;
    }

    shell.scrollLeft += lastRect.right - shellRect.right + 8;
};

const bindDesktopNav = (shell) => {
    if (shell._navAbort) {
        shell._navAbort.abort();
    }

    shell._navAbort = new AbortController();
    const { signal } = shell._navAbort;

    const items = () => [...shell.querySelectorAll('[data-menu-item]')];
    const canHover = window.matchMedia('(hover: hover) and (pointer: fine)').matches;
    let closeTimer = null;

    const positionPanel = (item) => {
        const drop = item.querySelector('.desktop-nav-drop');
        const trigger = item.querySelector('[data-menu-trigger]');
        const panel = item.querySelector('[data-menu-panel]');

        if (! drop || ! panel) {
            return;
        }

        panel.style.left = '';
        panel.style.right = '';
        panel.style.maxWidth = '';

        if (panel.classList.contains('menu-mega') || panel.classList.contains('desktop-nav-flyout--grid')) {
            panel.style.left = '50%';
        }

        requestAnimationFrame(() => {
            const rect = panel.getBoundingClientRect();
            const viewportPadding = 12;

            if (rect.right > window.innerWidth - viewportPadding) {
                const shift = rect.right - (window.innerWidth - viewportPadding);
                panel.style.left = panel.classList.contains('desktop-nav-flyout--grid') || panel.classList.contains('menu-mega')
                    ? `calc(50% - ${Math.ceil(shift)}px)`
                    : 'auto';
                panel.style.right = panel.classList.contains('desktop-nav-flyout--grid') || panel.classList.contains('menu-mega')
                    ? 'auto'
                    : '0';
            }

            if (rect.left < viewportPadding) {
                panel.style.left = `${viewportPadding - drop.getBoundingClientRect().left}px`;
                panel.style.right = 'auto';
            }

            panel.style.maxWidth = `${window.innerWidth - (viewportPadding * 2)}px`;
        });
    };

    const resetPanel = (item) => {
        const panel = item.querySelector('[data-menu-panel]');

        if (! panel) {
            return;
        }

        panel.style.left = '';
        panel.style.right = '';
        panel.style.maxWidth = '';
    };

    const setOpen = (item, open) => {
        const trigger = item?.querySelector('[data-menu-trigger]');

        if (! item || ! trigger) {
            return;
        }

        item.classList.toggle('is-open', open);
        trigger.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (open) {
            positionPanel(item);
        } else {
            resetPanel(item);
        }
    };

    const closeAll = (except = null) => {
        items().forEach((item) => {
            if (item !== except) {
                setOpen(item, false);
            }
        });
    };

    const openItem = (item) => {
        if (! item?.querySelector('[data-menu-trigger]')) {
            return;
        }

        closeAll(item);
        setOpen(item, true);
    };

    const scheduleClose = () => {
        clearTimeout(closeTimer);
        closeTimer = window.setTimeout(() => closeAll(), 220);
    };

    const cancelClose = () => {
        clearTimeout(closeTimer);
    };

    items().forEach((item) => {
        const drop = item.querySelector('.desktop-nav-drop');
        const trigger = item.querySelector('[data-menu-trigger]');
        const panel = item.querySelector('[data-menu-panel]');

        if (! drop || ! trigger || ! panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (item.classList.contains('is-open')) {
                closeAll();
            } else {
                openItem(item);
            }
        }, { signal });

        if (canHover) {
            drop.addEventListener('mouseenter', () => {
                cancelClose();
                openItem(item);
            }, { signal });

            drop.addEventListener('mouseleave', (event) => {
                const next = event.relatedTarget;

                if (next instanceof Node && drop.contains(next)) {
                    return;
                }

                scheduleClose();
            }, { signal });
        }
    });

    document.addEventListener('click', (event) => {
        if (event.target instanceof Element && event.target.closest('.desktop-nav-dock, .desktop-nav-shell')) {
            return;
        }

        closeAll();
    }, { signal });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAll();
        }
    }, { signal });

    window.addEventListener('scroll', () => closeAll(), { passive: true, signal });

    window.addEventListener('resize', () => {
        const openItemEl = items().find((item) => item.classList.contains('is-open'));

        if (openItemEl) {
            positionPanel(openItemEl);
        }
    }, { signal });
};

const initDesktopNav = () => {
    document.querySelectorAll('.desktop-nav-dock, .desktop-nav-shell').forEach((shell) => {
        bindDesktopNav(shell);
        ensureDesktopNavEndVisible(shell);
    });
};

const syncDesktopNavLayout = () => {
    document.querySelectorAll('.desktop-nav-dock, .desktop-nav-shell').forEach((shell) => {
        ensureDesktopNavEndVisible(shell);
    });
};

const bindMobileNav = (root) => {
    if (root._navAbort) {
        root._navAbort.abort();
    }

    root._navAbort = new AbortController();
    const { signal } = root._navAbort;

    const sections = () => [...root.querySelectorAll('[data-mobile-nav-section]')];

    const collapseSection = (section) => {
        const trigger = section.querySelector('[data-mobile-nav-trigger]');
        const panel = section.querySelector('[data-mobile-nav-panel]');

        trigger?.setAttribute('aria-expanded', 'false');
        panel?.setAttribute('hidden', '');
        section.classList.remove('is-expanded');
        trigger?.querySelector('.menu-link-mobile-chevron')?.classList.remove('rotate-180');
    };

    sections().forEach((section) => {
        const trigger = section.querySelector('[data-mobile-nav-trigger]');
        const panel = section.querySelector('[data-mobile-nav-panel]');

        if (! trigger || ! panel) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            const expanded = trigger.getAttribute('aria-expanded') === 'true';
            const nextExpanded = ! expanded;

            if (nextExpanded) {
                sections().forEach((other) => {
                    if (other !== section) {
                        collapseSection(other);
                    }
                });
            }

            trigger.setAttribute('aria-expanded', nextExpanded ? 'true' : 'false');
            panel.toggleAttribute('hidden', ! nextExpanded);
            section.classList.toggle('is-expanded', nextExpanded);
            trigger.querySelector('.menu-link-mobile-chevron')?.classList.toggle('rotate-180', nextExpanded);
        }, { signal });
    });
};

const initMobileNav = () => {
    const mobileMenu = document.getElementById('mobile-menu');

    if (mobileMenu) {
        bindMobileNav(mobileMenu);
    }
};

const initScrollReveal = () => {
    const prefersReducedMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    const selector = '.animate-fade-up, .glass-card, .bento-grid > *, .bento-tile, .feed-card, .gallery-tile, .sermon-card, .location-card, .resource-row, .past-event-chip, .quote-gen-z, .cta-gen-z, .form-gen-z, .faith-pillar, .faith-whisper-card, .scripture-ribbon, .parish-action-card, .worship-rhythm-card, .evangelical-trust-chip, .heavenly-comfort-card, .hero-gen-z';

    const elements = document.querySelectorAll(selector);

    if (! elements.length) return;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        elements.forEach((el) => el.classList.add('is-visible'));

        return;
    }

    const observer = new IntersectionObserver(
        (entries) => {
            entries.forEach((entry) => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('is-visible');
                    observer.unobserve(entry.target);
                }
            });
        },
        { threshold: 0.06, rootMargin: '0px 0px -24px 0px' }
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

    const collapseMobileSections = () => {
        document.querySelectorAll('[data-mobile-nav-section]').forEach((section) => {
            const trigger = section.querySelector('[data-mobile-nav-trigger]');
            const panel = section.querySelector('[data-mobile-nav-panel]');

            trigger?.setAttribute('aria-expanded', 'false');
            panel?.setAttribute('hidden', '');
            section.classList.remove('is-expanded');
            trigger?.querySelector('.menu-link-mobile-chevron')?.classList.remove('rotate-180');
        });
    };

    const siteShell = document.getElementById('site-shell');
    let scrollLockY = 0;

    const lockPageScroll = () => {
        scrollLockY = window.scrollY;
        document.body.style.position = 'fixed';
        document.body.style.top = `-${scrollLockY}px`;
        document.body.style.left = '0';
        document.body.style.right = '0';
        document.body.style.width = '100%';
    };

    const unlockPageScroll = () => {
        document.body.style.position = '';
        document.body.style.top = '';
        document.body.style.left = '';
        document.body.style.right = '';
        document.body.style.width = '';
        window.scrollTo(0, scrollLockY);
    };

    const setOpen = (open) => {
        menu.classList.toggle('is-open', open);
        overlay?.classList.toggle('is-open', open);
        toggle.classList.toggle('is-active', open);
        toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        document.body.classList.toggle('mobile-menu-open', open);
        siteShell?.toggleAttribute('inert', open);

        if (open) {
            siteShell?.setAttribute('aria-hidden', 'true');
        } else {
            siteShell?.removeAttribute('aria-hidden');
        }

        if (open) {
            lockPageScroll();
            menu.setAttribute('aria-hidden', 'false');
            overlay?.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(() => closeButton?.focus());
            return;
        }

        unlockPageScroll();
        collapseMobileSections();
        toggle.focus({ preventScroll: true });
        menu.setAttribute('aria-hidden', 'true');
        overlay?.setAttribute('aria-hidden', 'true');
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
        if (event.key === 'Escape' && menu.classList.contains('is-open')) {
            setOpen(false);
        }
    });
};

const initMemberChip = () => {
    document.querySelectorAll('[data-member-chip]').forEach((root) => {
        const trigger = root.querySelector('[data-member-chip-trigger]');
        const panel = root.querySelector('[data-member-chip-panel]');

        if (! trigger || ! panel) {
            return;
        }

        const setOpen = (open) => {
            trigger.classList.toggle('is-open', open);
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            panel.hidden = ! open;
        };

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            setOpen(panel.hidden);
        });

        panel.querySelectorAll('[data-member-chip-link]').forEach((link) => {
            link.addEventListener('click', () => setOpen(false));
        });

        document.addEventListener('click', (event) => {
            if (! root.contains(event.target)) {
                setOpen(false);
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                setOpen(false);
            }
        });
    });
};

const initHeaderScroll = () => {
    const header = document.getElementById('site-header');

    if (! header) {
        return;
    }

    const sync = () => {
        header.classList.toggle('site-header--elevated', window.scrollY > 12);
    };

    sync();
    window.addEventListener('scroll', sync, { passive: true });
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

const initPublicSiteUi = () => {
    initHeaderScroll();
    initDesktopNav();
    initLocationTabs();
    initMobileDock();
    initMobileNav();
    initMemberChip();
    initScrollReveal();

    if ('requestIdleCallback' in window) {
        requestIdleCallback(() => initPWA(), { timeout: 3000 });
    } else {
        initPWA();
    }
};

initDarkMode();
document.addEventListener('DOMContentLoaded', initPublicSiteUi);
window.addEventListener('load', () => {
    initDesktopNav();
    initMobileNav();
    syncDesktopNavLayout();
});
window.addEventListener('resize', syncDesktopNavLayout, { passive: true });
document.addEventListener('livewire:navigated', () => {
    initDesktopNav();
    initMobileNav();
    initScrollReveal();
});
