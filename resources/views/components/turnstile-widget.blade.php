@props([
    'elementId',
    'wireModel' => 'captchaToken',
    'turnstileEnabled' => false,
    'turnstileSiteKey' => '',
])

@if ($turnstileEnabled)
    <div {{ $attributes->merge(['class' => 'turnstile-wrap']) }} wire:ignore id="{{ $elementId }}"></div>

    @once
        @push('scripts')
            <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
        @endpush
    @endonce

    @push('scripts')
        <script>
            (function () {
                const elementId = @js($elementId);
                const siteKey = @js($turnstileSiteKey);
                let widgetId = null;

                function renderWidget() {
                    const element = document.getElementById(elementId);
                    if (!window.turnstile || !element) {
                        return;
                    }

                    if (widgetId !== null) {
                        try { window.turnstile.remove(widgetId); } catch (e) {}
                        widgetId = null;
                    }

                    widgetId = window.turnstile.render(element, {
                        sitekey: siteKey,
                        callback: (token) => @this.set(@js($wireModel), token),
                        'expired-callback': () => @this.set(@js($wireModel), ''),
                        'error-callback': () => @this.set(@js($wireModel), ''),
                    });
                }

                document.addEventListener('livewire:navigated', renderWidget);
                document.addEventListener('DOMContentLoaded', renderWidget);
                if (document.readyState !== 'loading') {
                    renderWidget();
                }
            })();
        </script>
    @endpush
@endif
