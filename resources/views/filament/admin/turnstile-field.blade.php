@if ($turnstileEnabled && filled($turnstileSiteKey))
    <div class="turnstile-wrap">
        <x-turnstile-widget
            element-id="turnstile-admin-login"
            :turnstile-enabled="$turnstileEnabled"
            :turnstile-site-key="$turnstileSiteKey"
        />
        @error('captchaToken')
            <p class="mt-2 text-sm text-danger-600 dark:text-danger-400" role="alert">{{ $message }}</p>
        @enderror
    </div>
@endif
