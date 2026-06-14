/**
 * Keeps Filament admin Livewire actions recoverable after session expiry, locks, or blocked requests.
 */
(() => {
    const reloadStatuses = new Set([401, 403, 419, 429, 500, 503]);

    const refreshExpiredLoginPage = () => {
        const params = new URLSearchParams(window.location.search);

        if (!params.has('expired')) {
            sessionStorage.removeItem('admin_login_refresh');

            return;
        }

        if (sessionStorage.getItem('admin_login_refresh') === '1') {
            sessionStorage.removeItem('admin_login_refresh');
            params.delete('expired');
            const query = params.toString();
            history.replaceState({}, '', window.location.pathname + (query ? `?${query}` : ''));

            return;
        }

        sessionStorage.setItem('admin_login_refresh', '1');
        window.location.reload();
    };

    const reloadOnFailure = () => {
        window.location.reload();
    };

    const shouldReloadFromPayload = (payload) => payload?.reload !== false;

    const patchFetchForAdminFailures = () => {
        if (window.__adminSessionFetchPatched) {
            return;
        }

        window.__adminSessionFetchPatched = true;
        const originalFetch = window.fetch.bind(window);

        window.fetch = async (...args) => {
            const response = await originalFetch(...args);

            if (!reloadStatuses.has(response.status)) {
                return response;
            }

            try {
                const payload = await response.clone().json();

                if (shouldReloadFromPayload(payload)) {
                    reloadOnFailure();
                }
            } catch {
                reloadOnFailure();
            }

            return response;
        };
    };

    const hookLivewireFailures = () => {
        document.addEventListener('livewire:init', () => {
            if (!window.Livewire?.hook) {
                return;
            }

            window.Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (!reloadStatuses.has(status)) {
                        return;
                    }

                    preventDefault?.();
                    reloadOnFailure();
                });
            });
        });
    };

    const initAdminIdleWarning = () => {
        const meta = document.querySelector('meta[name="admin-session-timeout-minutes"]');

        if (!meta || window.__adminIdleWarningBound) {
            return;
        }

        window.__adminIdleWarningBound = true;

        const timeoutMinutes = Math.max(15, Number.parseInt(meta.content, 10) || 120);
        const timeoutMs = timeoutMinutes * 60 * 1000;
        const warnMs = Math.min(5 * 60 * 1000, Math.max(60 * 1000, timeoutMs / 6));
        let lastActivity = Date.now();
        let warningShown = false;

        const bumpActivity = () => {
            lastActivity = Date.now();

            if (warningShown) {
                warningShown = false;
                document.getElementById('admin-idle-warning')?.remove();
            }
        };

        ['mousemove', 'mousedown', 'keydown', 'touchstart', 'scroll'].forEach((eventName) => {
            document.addEventListener(eventName, bumpActivity, { passive: true });
        });

        const showWarning = () => {
            if (warningShown || document.getElementById('admin-idle-warning')) {
                return;
            }

            warningShown = true;
            const banner = document.createElement('div');
            banner.id = 'admin-idle-warning';
            banner.className = 'admin-idle-warning';
            banner.setAttribute('role', 'status');

            const title = document.createElement('p');
            title.className = 'admin-idle-warning__title';
            title.textContent = 'Session ending soon';

            const text = document.createElement('p');
            text.className = 'admin-idle-warning__text';
            text.textContent = 'Move your mouse or save your work — you will be signed out after inactivity.';

            banner.append(title, text);
            document.body.appendChild(banner);
        };

        window.setInterval(() => {
            const idleMs = Date.now() - lastActivity;

            if (idleMs >= timeoutMs - warnMs && idleMs < timeoutMs) {
                showWarning();
            }
        }, 30000);
    };

    refreshExpiredLoginPage();
    patchFetchForAdminFailures();
    hookLivewireFailures();
    initAdminIdleWarning();
})();

const bootAdminFormTabs = () => {
    if (! document.querySelector('.admin-form-tabs')) {
        return;
    }

    void import('./admin-form-tabs.js');
};

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', bootAdminFormTabs);
} else {
    bootAdminFormTabs();
}

document.addEventListener('livewire:navigated', bootAdminFormTabs);
