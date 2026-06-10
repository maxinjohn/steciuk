<div>
    @if ($saved)
        <div class="member-alert member-alert--success mb-5" role="status">
            Password updated successfully.
        </div>
    @endif

    <form wire:submit="updatePassword" class="space-y-5" novalidate>
        <div>
            <label for="profile-current-password" class="form-label">Current password <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="password" id="profile-current-password" wire:model.blur="current_password" class="form-input" required autocomplete="current-password" @error('current_password') aria-invalid="true" @enderror>
            @error('current_password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="profile-new-password" class="form-label">New password <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="password" id="profile-new-password" wire:model.blur="password" class="form-input" required autocomplete="new-password" @error('password') aria-invalid="true" @enderror>
                @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="profile-new-password-confirmation" class="form-label">Confirm new password <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="password" id="profile-new-password-confirmation" wire:model.blur="password_confirmation" class="form-input" required autocomplete="new-password" @error('password_confirmation') aria-invalid="true" @enderror>
                @error('password_confirmation')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>

        <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="updatePassword">Update password</span>
            <span wire:loading wire:target="updatePassword">Updating…</span>
        </button>
    </form>
</div>
