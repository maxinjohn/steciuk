// PWA + dark mode + scroll reveal + install prompt + mobile dock + gallery lightbox
import './future-ready.js';

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

const themeToggleLabel = (isDark) => isDark ? 'Switch to light mode' : 'Switch to dark mode';

const syncDarkModeToggleState = () => {
    const isDark = document.documentElement.classList.contains('dark');
    document.querySelectorAll('[data-theme-toggle]').forEach((button) => {
        button.setAttribute('aria-pressed', isDark ? 'true' : 'false');
        button.setAttribute('aria-label', themeToggleLabel(isDark));
    });
};

const toggleDarkMode = () => {
    const isDark = document.documentElement.classList.toggle('dark');
    localStorage.setItem('theme', isDark ? 'dark' : 'light');
    updateThemeColor();
    syncDarkModeToggleState();
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

            if (nextExpanded) {
                requestAnimationFrame(() => {
                    panel.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                });
            }
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
    const isMobile = window.matchMedia('(max-width: 767px)').matches;

    const selector = isMobile
        ? '.hero-gen-z, .hero-modern .hero-badge, .hero-modern .hero-title, .hero-modern .hero-actions, .page-section--compact:first-of-type .section-head, .page-section--compact:first-of-type .feed-card:first-child, .page-section--compact:first-of-type .bento-grid > *:first-child, .heavenly-empty, .faith-spark-chip, .faith-whisper-card, .form-success-gen-z, .next-worship-chip, .page-band, .giving-method-card'
        : '.animate-fade-up, .glass-card, .bento-grid > *, .bento-tile, .feed-card, .gallery-tile, .sermon-card, .location-card, .resource-row, .past-event-chip, .quote-gen-z, .cta-gen-z, .form-gen-z, .faith-pillar, .faith-whisper-card, .scripture-ribbon, .parish-action-card, .worship-rhythm-card, .evangelical-trust-verse, .heavenly-comfort-card, .hero-gen-z, .heavenly-empty, .faith-spark-chip, .evangelical-trust-bar, .detail-share-row, .page-band, .giving-method-card, .faith-whispers';

    const staggerParents = '.feed-grid, .feed-grid--news, .sermon-stack, .gallery-mosaic, .bento-grid, .past-events-grid';

    const applyStagger = (element) => {
        const card = element.closest('.feed-card, .sermon-card, .gallery-tile, .bento-tile, .past-event-chip, .resource-row') ?? element;
        const parent = card.parentElement;

        if (! parent || ! parent.matches(staggerParents)) {
            return;
        }

        const index = [...parent.children].indexOf(card);

        if (index < 0) {
            return;
        }

        element.style.setProperty('--reveal-delay', `${Math.min(index * 55, 330)}ms`);
    };

    const elements = document.querySelectorAll(selector);

    if (! elements.length) return;

    if (prefersReducedMotion || !('IntersectionObserver' in window)) {
        document.querySelectorAll('.animate-fade-up, .glass-card, .feed-card, .hero-gen-z').forEach((el) => {
            el.classList.add('is-visible');
        });

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
        { threshold: 0.06, rootMargin: isMobile ? '0px 0px 0px 0px' : '0px 0px -24px 0px' }
    );

    elements.forEach((el) => {
        if (el.dataset.revealBound === 'true') {
            return;
        }

        el.dataset.revealBound = 'true';
        applyStagger(el);

        const rect = el.getBoundingClientRect();
        if (rect.top < window.innerHeight && rect.bottom > 0) {
            el.classList.add('is-visible');
            return;
        }

        observer.observe(el);
    });
};

const prefetchedUrls = new Set();

const prefetchUrl = (href) => {
    if (! href || prefetchedUrls.has(href)) {
        return;
    }

    try {
        const url = new URL(href, window.location.origin);

        if (url.origin !== window.location.origin || url.pathname === window.location.pathname) {
            return;
        }

        prefetchedUrls.add(href);

        const link = document.createElement('link');
        link.rel = 'prefetch';
        link.href = url.pathname + url.search;
        document.head.appendChild(link);
    } catch {
        // Ignore malformed URLs.
    }
};

const initLinkPrefetch = () => {
    document.querySelectorAll('[data-prefetch-link]').forEach((anchor) => {
        if (anchor.dataset.prefetchBound === 'true') {
            return;
        }

        anchor.dataset.prefetchBound = 'true';

        const href = anchor.getAttribute('href');

        if (! href) {
            return;
        }

        const warm = () => prefetchUrl(href);

        anchor.addEventListener('touchstart', warm, { passive: true, once: true });
        anchor.addEventListener('mouseenter', warm, { passive: true, once: true });
        anchor.addEventListener('focus', warm, { passive: true, once: true });
    });
};

const hapticTap = (duration = 8) => {
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    if (typeof navigator.vibrate === 'function') {
        navigator.vibrate(duration);
    }
};

const initHapticFeedback = () => {
    document.querySelectorAll('.mobile-dock-item:not(.mobile-dock-item--menu)').forEach((item) => {
        if (item.dataset.hapticBound === 'true') {
            return;
        }

        item.dataset.hapticBound = 'true';
        item.addEventListener('click', () => hapticTap(6), { passive: true });
    });

    document.querySelectorAll('.hero-actions .btn-primary, .parish-action-strip .btn-primary').forEach((button) => {
        if (button.dataset.hapticBound === 'true') {
            return;
        }

        button.dataset.hapticBound = 'true';
        button.addEventListener('click', () => hapticTap(10), { passive: true });
    });

    document.querySelectorAll('.prayer-fab').forEach((fab) => {
        if (fab.dataset.hapticBound === 'true') {
            return;
        }

        fab.dataset.hapticBound = 'true';
        fab.addEventListener('click', () => hapticTap(8), { passive: true });
    });
};

const suppressSkippedViewTransitionErrors = () => {
    if (window.__viewTransitionErrorBound) {
        return;
    }

    window.__viewTransitionErrorBound = true;

    window.addEventListener('unhandledrejection', (event) => {
        const reason = event.reason;

        if (reason?.name !== 'AbortError') {
            return;
        }

        const message = String(reason?.message ?? '');

        if (message.includes('Transition') || message.includes('transition')) {
            event.preventDefault();
        }
    });
};

const showShareToast = (message) => {
    let toast = document.getElementById('share-toast');

    if (! toast) {
        toast = document.createElement('div');
        toast.id = 'share-toast';
        toast.className = 'share-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }

    toast.textContent = message;
    toast.classList.add('is-visible');

    window.clearTimeout(showShareToast._timer);
    showShareToast._timer = window.setTimeout(() => {
        toast.classList.remove('is-visible');
    }, 2200);
};

const initShareButtons = () => {
    document.querySelectorAll('[data-share]').forEach((button) => {
        if (button.dataset.shareBound === 'true') {
            return;
        }

        button.dataset.shareBound = 'true';

        button.addEventListener('click', async (event) => {
            event.preventDefault();
            event.stopPropagation();

            const url = button.dataset.shareUrl || window.location.href;
            const title = button.dataset.shareTitle || document.title;
            const label = button.querySelector('[data-share-label]');
            const payload = { title, text: title, url };

            if (navigator.share) {
                try {
                    await navigator.share(payload);
                    hapticTap(10);
                    return;
                } catch (error) {
                    if (error?.name === 'AbortError') {
                        return;
                    }
                }
            }

            try {
                await navigator.clipboard.writeText(url);
                button.classList.add('is-copied');
                if (label) {
                    label.textContent = 'Copied';
                }
                hapticTap(12);
                showShareToast('Link copied');
                window.setTimeout(() => {
                    button.classList.remove('is-copied');
                    if (label) {
                        label.textContent = button.dataset.shareDefaultLabel || 'Share';
                    }
                }, 1800);
            } catch {
                showShareToast('Copy the link from your browser bar');
            }
        });

        const label = button.querySelector('[data-share-label]');
        if (label) {
            button.dataset.shareDefaultLabel = label.textContent.trim();
        }
    });
};

const initCardMediaSkeletons = () => {
    document.querySelectorAll('.feed-card-media, .resource-row-media, .gallery-tile-media').forEach((media) => {
        if (media.dataset.skeletonBound === 'true') {
            return;
        }

        media.dataset.skeletonBound = 'true';

        const image = media.querySelector('img');

        if (! image) {
            media.classList.add('is-loaded');
            return;
        }

        const markLoaded = () => media.classList.add('is-loaded');

        if (image.classList.contains('card-media-image--topic') || image.classList.contains('card-media-image--dynamic')) {
            markLoaded();
            return;
        }

        if (image.complete) {
            markLoaded();
            return;
        }

        image.addEventListener('load', markLoaded, { once: true });
        image.addEventListener('error', markLoaded, { once: true });
    });
};

const showBlessingToast = (message) => {
    let toast = document.getElementById('blessing-toast');

    if (! toast) {
        toast = document.createElement('div');
        toast.id = 'blessing-toast';
        toast.className = 'blessing-toast';
        toast.setAttribute('role', 'status');
        toast.setAttribute('aria-live', 'polite');
        document.body.appendChild(toast);
    }

    toast.replaceChildren();
    const icon = document.createElement('span');
    icon.className = 'blessing-toast__icon';
    icon.setAttribute('aria-hidden', 'true');
    icon.textContent = '✝';
    const copy = document.createElement('span');
    copy.textContent = message;
    toast.append(icon, copy);
    toast.classList.add('is-visible');
    hapticTap(14);

    window.clearTimeout(showBlessingToast._timer);
    showBlessingToast._timer = window.setTimeout(() => {
        toast.classList.remove('is-visible');
    }, 2600);
};

const initDivineWhisperBar = () => {
    const bar = document.querySelector('[data-divine-whisper-bar]');

    if (! bar || bar.dataset.whisperBound === 'true') {
        return;
    }

    bar.dataset.whisperBound = 'true';

    const lines = [...bar.querySelectorAll('[data-divine-whisper-line]')];

    if (lines.length <= 1 || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        return;
    }

    let index = lines.findIndex((line) => line.classList.contains('is-active'));

    if (index < 0) {
        index = 0;
        lines[0]?.classList.add('is-active');
    }

    if (window.__divineWhisperInterval) {
        window.clearInterval(window.__divineWhisperInterval);
    }

    window.__divineWhisperInterval = window.setInterval(() => {
        lines[index]?.classList.remove('is-active');
        index = (index + 1) % lines.length;
        lines[index]?.classList.add('is-active');
    }, 7000);
};

const initFormBlessings = () => {
    const celebrate = (node) => {
        if (! node || node.dataset.blessed === 'true') {
            return;
        }

        node.dataset.blessed = 'true';
        node.classList.add('is-blessed');
        showBlessingToast('Prayers received — grace & peace');
    };

    document.querySelectorAll('.form-success-gen-z').forEach(celebrate);

    if (! window.__formBlessingObserver) {
        window.__formBlessingObserver = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                mutation.addedNodes.forEach((node) => {
                    if (!(node instanceof Element)) {
                        return;
                    }

                    if (node.matches('.form-success-gen-z')) {
                        celebrate(node);
                    }

                    node.querySelectorAll?.('.form-success-gen-z').forEach(celebrate);
                });
            });
        });

        document.querySelectorAll('.form-gen-z, .contact-form-card, .member-portal-card, main').forEach((root) => {
            window.__formBlessingObserver.observe(root, { childList: true, subtree: true });
        });
    }
};

const unlockMobileMenuScrollIfNeeded = () => {
    if (! document.body.classList.contains('mobile-menu-open')) {
        return;
    }

    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');
    const toggle = document.getElementById('mobile-menu-toggle');
    const siteShell = document.getElementById('site-shell');

    menu?.classList.remove('is-open');
    overlay?.classList.remove('is-open');
    toggle?.classList.remove('is-active');
    toggle?.setAttribute('aria-expanded', 'false');
    document.body.classList.remove('mobile-menu-open');
    siteShell?.removeAttribute('inert');
    siteShell?.removeAttribute('aria-hidden');
    document.body.style.position = '';
    document.body.style.top = '';
    document.body.style.left = '';
    document.body.style.right = '';
    document.body.style.width = '';
    menu?.setAttribute('aria-hidden', 'true');
    overlay?.setAttribute('aria-hidden', 'true');
};

const initFeedRailScrollHint = () => {
    document.querySelectorAll('.home-showcase-section .feed-grid, .home-showcase-section .feed-grid--news, .home-showcase-section .sermon-stack, .feed-rail.feed-grid, .feed-rail.feed-grid--news').forEach((rail) => {
        if (rail.dataset.scrollHintBound === 'true' || rail.querySelector('.feed-empty--heavenly, .feed-empty--rich')) {
            return;
        }

        rail.dataset.scrollHintBound = 'true';
        rail.classList.add('feed-rail--hint');

        rail.addEventListener('scroll', () => {
            rail.classList.remove('feed-rail--hint');
            rail.classList.add('feed-rail--scrolled');
        }, { passive: true, once: true });
    });
};

const initMobileDock = () => {
    const toggle = document.getElementById('mobile-menu-toggle');
    const closeButton = document.getElementById('mobile-menu-close');
    const menu = document.getElementById('mobile-menu');
    const overlay = document.getElementById('mobile-menu-overlay');

    if (! toggle || ! menu || toggle.dataset.dockBound === 'true') {
        return;
    }

    toggle.dataset.dockBound = 'true';

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
        if (root.dataset.memberChipBound === 'true') {
            return;
        }

        root.dataset.memberChipBound = 'true';

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

    const canShowCustomInstallBanner = () => {
        if (! banner || ! installBtn) {
            return false;
        }

        if (localStorage.getItem('pwa-dismissed')) {
            return false;
        }

        if (window.matchMedia('(display-mode: standalone)').matches) {
            return false;
        }

        return true;
    };

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
        if (! canShowCustomInstallBanner()) {
            return;
        }

        e.preventDefault();
        deferredPrompt = e;
        showBanner();
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
    suppressSkippedViewTransitionErrors();
    initHeaderScroll();
    initDesktopNav();
    initLocationTabs();
    initMobileDock();
    initMobileNav();
    initMemberChip();
    initLinkPrefetch();
    initHapticFeedback();
    initShareButtons();
    initCardMediaSkeletons();
    initDivineWhisperBar();
    initFormBlessings();
    initFeedRailScrollHint();
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
    unlockMobileMenuScrollIfNeeded();
    initDesktopNav();
    initMobileNav();
    initLinkPrefetch();
    initHapticFeedback();
    initShareButtons();
    initCardMediaSkeletons();
    initDivineWhisperBar();
    initFormBlessings();
    initFeedRailScrollHint();
    initScrollReveal();
});
