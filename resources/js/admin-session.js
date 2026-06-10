/**
 * Keeps Filament admin sign-in reliable after session expiry (CSRF / Livewire stale state).
 */
(() => {
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

    const reloadOnSessionExpiry = () => {
        window.location.reload();
    };

    const patchFetchForSessionExpiry = () => {
        if (window.__adminSessionFetchPatched) {
            return;
        }

        window.__adminSessionFetchPatched = true;
        const originalFetch = window.fetch.bind(window);

        window.fetch = async (...args) => {
            const response = await originalFetch(...args);

            if (response.status !== 419) {
                return response;
            }

            try {
                const payload = await response.clone().json();

                if (payload?.reload) {
                    reloadOnSessionExpiry();
                }
            } catch {
                reloadOnSessionExpiry();
            }

            return response;
        };
    };

    const hookLivewireSessionExpiry = () => {
        document.addEventListener('livewire:init', () => {
            if (!window.Livewire?.hook) {
                return;
            }

            window.Livewire.hook('request', ({ fail }) => {
                fail(({ status, preventDefault }) => {
                    if (status === 419) {
                        preventDefault?.();
                        reloadOnSessionExpiry();
                    }
                });
            });
        });
    };

    refreshExpiredLoginPage();
    patchFetchForSessionExpiry();
    hookLivewireSessionExpiry();
})();
