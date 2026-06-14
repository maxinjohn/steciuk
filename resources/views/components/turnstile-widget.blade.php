@props([
    'elementId',
    'wireModel' => 'captchaToken',
    'turnstileEnabled' => false,
    'turnstileSiteKey' => '',
])

@if ($turnstileEnabled && filled($turnstileSiteKey))
    <div {{ $attributes->merge(['class' => 'turnstile-wrap']) }}>
        <div wire:ignore id="{{ $elementId }}" class="turnstile-mount"></div>
    </div>

    @once
        <script>
            window.__steciTurnstile = window.__steciTurnstile || { widgets: {}, loadPromise: null };

            window.__steciTurnstileLoad = window.__steciTurnstileLoad || function () {
                if (window.turnstile) {
                    return Promise.resolve(window.turnstile);
                }

                if (window.__steciTurnstile.loadPromise) {
                    return window.__steciTurnstile.loadPromise;
                }

                window.__steciTurnstile.loadPromise = new Promise((resolve, reject) => {
                    window.__steciTurnstileApiReady = () => {
                        delete window.__steciTurnstileApiReady;
                        resolve(window.turnstile || null);
                    };

                    const script = document.createElement('script');
                    script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit&onload=__steciTurnstileApiReady';
                    script.async = true;
                    script.defer = true;
                    script.onerror = () => {
                        delete window.__steciTurnstileApiReady;
                        window.__steciTurnstile.loadPromise = null;
                        reject(new Error('Turnstile script failed to load'));
                    };
                    document.head.appendChild(script);
                });

                return window.__steciTurnstile.loadPromise;
            };

            window.__steciTurnstileRegister = window.__steciTurnstileRegister || function (elementId, siteKey, setToken, force = false) {
                function renderWidget() {
                    const mount = document.getElementById(elementId);
                    if (!mount || !window.turnstile || !siteKey) {
                        return;
                    }

                    const existing = window.__steciTurnstile.widgets[elementId];
                    if (existing?.widgetId && mount.dataset.turnstileReady === '1' && !force) {
                        return;
                    }

                    if (existing?.widgetId) {
                        try {
                            window.turnstile.remove(existing.widgetId);
                        } catch (e) {}
                    }

                    mount.innerHTML = '';
                    mount.dataset.turnstileReady = '0';

                    const widgetId = window.turnstile.render(mount, {
                        sitekey: siteKey,
                        appearance: 'always',
                        'refresh-expired': 'auto',
                        callback: (token) => {
                            mount.dataset.turnstileReady = '1';
                            setToken(token || '');
                        },
                        'expired-callback': () => {
                            mount.dataset.turnstileReady = '0';
                            setToken('');
                        },
                        'error-callback': () => {
                            mount.dataset.turnstileReady = '0';
                            setToken('');
                        },
                    });

                    window.__steciTurnstile.widgets[elementId] = { widgetId, siteKey, setToken };
                }

                window.__steciTurnstileLoad()
                    .then(() => renderWidget())
                    .catch(() => setToken(''));
            };

            window.__steciTurnstileReset = window.__steciTurnstileReset || function (elementId) {
                const entry = window.__steciTurnstile.widgets[elementId];
                const mount = document.getElementById(elementId);

                if (mount) {
                    mount.dataset.turnstileReady = '0';
                }

                if (entry?.setToken) {
                    entry.setToken('');
                }

                if (entry?.widgetId && window.turnstile) {
                    try {
                        window.turnstile.reset(entry.widgetId);
                        return;
                    } catch (e) {}
                }

                if (entry?.siteKey && entry?.setToken) {
                    window.__steciTurnstileRegister(elementId, entry.siteKey, entry.setToken, true);
                }
            };

            document.addEventListener('livewire:init', () => {
                Livewire.on('turnstile-reset', ({ elementId }) => {
                    if (elementId) {
                        window.__steciTurnstileReset(elementId);
                    }
                });
            });
        </script>
    @endonce

    <script>
        (function () {
            const elementId = @js($elementId);
            const siteKey = @js($turnstileSiteKey);
            const wireModel = @js($wireModel);

            function setToken(token) {
                if (window.Livewire && typeof @this !== 'undefined') {
                    @this.set(wireModel, token || '');
                }
            }

            function bootTurnstile() {
                const mount = document.getElementById(elementId);

                if (! mount) {
                    return;
                }

                const load = () => {
                    if (typeof window.__steciTurnstileRegister === 'function') {
                        window.__steciTurnstileRegister(elementId, siteKey, setToken);
                    }
                };

                const wrap = mount.closest('.turnstile-wrap') || mount;

                if (! ('IntersectionObserver' in window)) {
                    load();

                    return;
                }

                const observer = new IntersectionObserver((entries) => {
                    if (! entries.some((entry) => entry.isIntersecting)) {
                        return;
                    }

                    observer.disconnect();
                    load();
                }, { rootMargin: '160px 0px' });

                observer.observe(wrap);
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootTurnstile, { once: true });
            } else {
                bootTurnstile();
            }
        })();
    </script>
@endif
