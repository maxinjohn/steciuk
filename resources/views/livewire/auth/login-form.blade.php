<div>
    <form wire:submit="login" class="space-y-5" novalidate>
        @error('email')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror

        @if (session('password_reset'))
            <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                Your password has been updated. You can sign in now.
            </div>
        @endif

        <div>
            <label for="login-email" class="form-label">Email address <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="email" id="login-email" wire:model.blur="email" class="form-input" required autocomplete="email" aria-required="true" @error('email') aria-invalid="true" @enderror>
        </div>

        <div>
            <label for="login-password" class="form-label">Password <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="password" id="login-password" wire:model="password" class="form-input" required autocomplete="current-password" aria-required="true">
        </div>

        <label class="inline-flex items-center gap-2 text-sm text-ink-muted">
            <input type="checkbox" wire:model="remember" class="rounded border-[var(--site-border)] text-brand focus:ring-brand">
            Remember me on this device
        </label>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="login">Sign in</span>
                <span wire:loading wire:target="login">Signing in…</span>
            </button>
            <p class="text-sm text-ink-muted">
                <a href="{{ route('password.request') }}" class="text-brand hover:underline">Forgot password?</a>
            </p>
            <p class="text-sm text-ink-muted">New to the parish? <a href="{{ route('register') }}" class="text-brand hover:underline">Create an account</a></p>
        </div>
    </form>
</div>
