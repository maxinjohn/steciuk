<div>
    @if ($sent)
        <div class="member-alert member-alert--success mb-5" role="status">
            If an account exists for that email address, we have sent a password reset link. Please check your inbox.
        </div>
    @endif

    <form wire:submit="sendResetLink" class="space-y-5" novalidate>
        @error('email')
            <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
        @enderror

        <p class="text-sm text-ink-muted">
            Enter the email address on your parish account. We will send a secure link to choose a new password.
        </p>

        <div>
            <label for="forgot-email" class="form-label">Email address <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="email" id="forgot-email" wire:model.blur="email" class="form-input" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled" @disabled($sent)>
                <span wire:loading.remove wire:target="sendResetLink">Send reset link</span>
                <span wire:loading wire:target="sendResetLink">Sending…</span>
            </button>
            <a href="{{ route('login') }}" class="text-sm text-brand hover:underline">Back to sign in</a>
        </div>
    </form>
</div>
