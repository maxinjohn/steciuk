<div>
    <form wire:submit="resetPassword" class="space-y-5" novalidate>
        @error('email')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror

        <p class="text-sm text-ink-muted">
            Choose a new password for <strong>{{ $email ?: 'your account' }}</strong>.
        </p>

        <div>
            <label for="reset-email" class="form-label">Email address <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="email" id="reset-email" wire:model.blur="email" class="form-input" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
        </div>

        <div>
            <label for="reset-password" class="form-label">New password <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="password" id="reset-password" wire:model="password" class="form-input" required autocomplete="new-password" @error('password') aria-invalid="true" @enderror>
            @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="reset-password-confirmation" class="form-label">Confirm new password <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="password" id="reset-password-confirmation" wire:model="password_confirmation" class="form-input" required autocomplete="new-password">
        </div>

        <x-turnstile-field
            element-id="turnstile-reset-password"
            :turnstile-enabled="$turnstileEnabled ?? false"
            :turnstile-site-key="$turnstileSiteKey ?? ''"
        />

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="resetPassword">Save new password</span>
                <span wire:loading wire:target="resetPassword">Saving…</span>
            </button>
            <a href="{{ route('login') }}" class="text-sm text-brand hover:underline">Back to sign in</a>
        </div>
    </form>
</div>
