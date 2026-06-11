@props([
    'elementId',
    'wireModel' => 'captchaToken',
    'turnstileEnabled' => false,
    'turnstileSiteKey' => '',
])

@if ($turnstileEnabled)
    <div {{ $attributes->merge(['class' => 'turnstile-wrap']) }}>
        <div wire:ignore id="{{ $elementId }}" class="turnstile-mount"></div>
    </div>

    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
        @endpush
    @endonce

    @push('scripts')
        <script>
            window.__steciTurnstile = window.__steciTurnstile || {};

            (function () {
                const elementId = @js($elementId);
                const siteKey = @js($turnstileSiteKey);
                const wireModel = @js($wireModel);

                function setToken(token) {
                    if (window.Livewire && typeof @this !== 'undefined') {
                        @this.set(wireModel, token || '');
                    }
                }

                function renderWidget(force = false) {
                    const mount = document.getElementById(elementId);
                    if (!mount || !window.turnstile) {
                        return;
                    }

                    const existing = window.__steciTurnstile[elementId];
                    if (existing?.widgetId && mount.dataset.turnstileReady === '1' && !force) {
                        return;
                    }

                    if (existing?.widgetId) {
                        try { window.turnstile.remove(existing.widgetId); } catch (e) {}
                    }

                    mount.innerHTML = '';
                    mount.dataset.turnstileReady = '0';

                    const widgetId = window.turnstile.render(mount, {
                        sitekey: siteKey,
                        appearance: 'always',
                        'refresh-expired': 'auto',
                        callback: (token) => {
                            mount.dataset.turnstileReady = '1';
                            setToken(token);
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

                    window.__steciTurnstile[elementId] = { widgetId };
                }

                function boot() {
                    if (window.turnstile) {
                        renderWidget();
                        return;
                    }

                    const timer = setInterval(() => {
                        if (!window.turnstile) {
                            return;
                        }

                        clearInterval(timer);
                        renderWidget();
                    }, 120);
                }

                document.addEventListener('DOMContentLoaded', boot);
                if (document.readyState !== 'loading') {
                    boot();
                }
            })();
        </script>
    @endpush
@endif
