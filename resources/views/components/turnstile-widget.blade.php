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
        @push('scripts')
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
                        const script = document.createElement('script');
                        script.src = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit';
                        script.async = true;
                        script.defer = true;
                        script.onload = () => {
                            if (window.turnstile?.ready) {
                                window.turnstile.ready(() => resolve(window.turnstile));
                                return;
                            }

                            resolve(window.turnstile || null);
                        };
                        script.onerror = () => reject(new Error('Turnstile script failed to load'));
                        document.head.appendChild(script);
                    });

                    return window.__steciTurnstile.loadPromise;
                };

                window.__steciTurnstileRegister = window.__steciTurnstileRegister || function (elementId, siteKey, setToken) {
                    function renderWidget(force = false) {
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

                        window.__steciTurnstile.widgets[elementId] = { widgetId };
                    }

                    window.__steciTurnstileLoad()
                        .then(() => renderWidget())
                        .catch(() => setToken(''));
                };
            </script>
        @endpush
    @endonce

    @push('scripts')
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

                if (typeof window.__steciTurnstileRegister === 'function') {
                    window.__steciTurnstileRegister(elementId, siteKey, setToken);
                }
            })();
        </script>
    @endpush
@endif
