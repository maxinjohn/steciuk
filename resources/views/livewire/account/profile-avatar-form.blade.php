<div>
    @if ($saved)
        <div class="member-alert member-alert--success mb-5" role="status">
            Profile photo updated.
        </div>
    @endif

    @if ($removed)
        <div class="member-alert member-alert--success mb-5" role="status">
            Profile photo removed. We will use Gravatar or your initials when no photo is uploaded.
        </div>
    @endif

    <div class="member-profile-photo">
        <div class="member-profile-photo-preview">
            <x-member-avatar :user="auth()->user()" size="xl" class="member-profile-photo-avatar" />
            <div class="member-profile-photo-meta">
                <p class="member-profile-photo-title">Profile photo</p>
                <p class="member-profile-photo-copy">
                    Upload a photo up to 3 MB (JPEG, PNG, WebP, or GIF).
                    @if (auth()->user()?->email)
                        Without an upload we try your Gravatar for {{ auth()->user()->email }}.
                    @else
                        Without an upload we show your initials.
                    @endif
                </p>
            </div>
        </div>

        <div class="member-profile-photo-actions">
            <label class="btn btn-outline !text-sm member-profile-photo-upload">
                <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp,image/gif" class="sr-only">
                <span wire:loading.remove wire:target="photo">Choose photo</span>
                <span wire:loading wire:target="photo">Reading file…</span>
            </label>

            @if ($photo)
                <button type="button" class="btn btn-primary !text-sm" wire:click="uploadPhoto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="uploadPhoto">Save photo</span>
                    <span wire:loading wire:target="uploadPhoto">Uploading…</span>
                </button>
            @endif

            @if (auth()->user()?->hasUploadedProfilePhoto())
                <button type="button" class="btn btn-outline !text-sm member-profile-photo-delete" wire:click="removePhoto" wire:confirm="Remove your uploaded profile photo?" wire:loading.attr="disabled">
                    Remove photo
                </button>
            @endif
        </div>

        @error('photo')
            <p class="form-error mt-3" role="alert">{{ $message }}</p>
        @enderror

        @if ($photo && ! $errors->has('photo'))
            <p class="member-profile-photo-selected mt-3 text-sm text-ink-muted">
                Selected: {{ $photo->getClientOriginalName() }}
            </p>
        @endif
    </div>
</div>
