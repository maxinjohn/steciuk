/**
 * Forward-ready client layer: network adaptation, speculation fallback, reading progress, theme hints.
 * Designed to degrade gracefully on older browsers while using modern APIs when available.
 */
(() => {
    const root = document.documentElement;

    const initNetworkAdaptation = () => {
        const connection = navigator.connection || navigator.mozConnection || navigator.webkitConnection;

        if (navigator.onLine === false) {
            root.classList.add('offline-mode');
        }

        window.addEventListener('online', () => root.classList.remove('offline-mode'));
        window.addEventListener('offline', () => root.classList.add('offline-mode'));

        if (connection?.saveData) {
            root.classList.add('save-data-mode');
        }

        const effectiveType = connection?.effectiveType;

        if (effectiveType === 'slow-2g' || effectiveType === '2g') {
            root.classList.add('connection-slow');
        }

        connection?.addEventListener?.('change', () => {
            root.classList.toggle('save-data-mode', Boolean(connection.saveData));
            root.classList.toggle('connection-slow', connection.effectiveType === 'slow-2g' || connection.effectiveType === '2g');
        });
    };

    const initSpeculationFallback = () => {
        if (root.classList.contains('save-data-mode') || !('HTMLScriptElement' in window)) {
            return;
        }

        if (document.querySelector('script[type="speculationrules"]')) {
            return;
        }

        const paths = (root.dataset.speculationPrefetch || '')
            .split('|')
            .map((item) => item.trim())
            .filter(Boolean);

        paths.forEach((path) => {
            try {
                const link = document.createElement('link');
                link.rel = 'prefetch';
                link.href = path;
                document.head.appendChild(link);
            } catch {
                // Ignore malformed paths.
            }
        });
    };

    const initReadingProgress = () => {
        if (root.dataset.readingProgress !== '1') {
            return;
        }

        const target = document.querySelector('.page-section--article, .prose-church--page');

        if (! target) {
            return;
        }

        const bar = document.createElement('div');
        bar.className = 'reading-progress';
        bar.setAttribute('role', 'presentation');
        bar.setAttribute('aria-hidden', 'true');
        document.body.appendChild(bar);
        bar.classList.add('is-active');

        const update = () => {
            const rect = target.getBoundingClientRect();
            const total = target.scrollHeight - window.innerHeight;

            if (total <= 0) {
                bar.style.setProperty('--reading-progress', '0');

                return;
            }

            const scrolled = window.scrollY - (target.offsetTop - 80);
            const progress = Math.min(1, Math.max(0, scrolled / total));
            bar.style.setProperty('--reading-progress', progress.toFixed(4));
        };

        update();
        window.addEventListener('scroll', update, { passive: true });
        window.addEventListener('resize', update, { passive: true });
    };

    const initSystemThemeHint = () => {
        const media = window.matchMedia('(prefers-color-scheme: dark)');

        const syncHint = () => {
            root.dataset.systemTheme = media.matches ? 'dark' : 'light';
        };

        syncHint();
        media.addEventListener('change', syncHint);
    };

    const initHighContrastHint = () => {
        const media = window.matchMedia('(prefers-contrast: more)');

        const sync = () => {
            root.classList.toggle('high-contrast-mode', media.matches);
        };

        sync();
        media.addEventListener('change', sync);
    };

    const boot = () => {
        initNetworkAdaptation();
        initSpeculationFallback();
        initReadingProgress();
        initSystemThemeHint();
        initHighContrastHint();
    };

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', boot);
    } else {
        boot();
    }

    document.addEventListener('livewire:navigated', () => {
        initReadingProgress();
    });
})();
