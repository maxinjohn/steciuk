@props([
    'elementId',
    'turnstileEnabled' => false,
    'turnstileSiteKey' => '',
    'label' => 'Security check',
])

@if ($turnstileEnabled && filled($turnstileSiteKey))
    <div {{ $attributes }}>
        <label class="form-label">{{ $label }} <span class="text-red-600" aria-hidden="true">*</span></label>
        <x-turnstile-widget
            :element-id="$elementId"
            :turnstile-enabled="$turnstileEnabled"
            :turnstile-site-key="$turnstileSiteKey"
        />
        @error('captchaToken')<p class="form-error" role="alert">{{ $message }}</p>@enderror
    </div>
@endif
