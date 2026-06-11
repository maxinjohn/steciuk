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

    refreshExpiredLoginPage();
    patchFetchForAdminFailures();
    hookLivewireFailures();
})();
