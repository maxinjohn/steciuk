<div>
    @if ($submitted)
        <div class="rounded-2xl border border-green-200 bg-green-50 px-6 py-8 text-center" role="status" aria-live="polite">
            <svg class="mx-auto h-12 w-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 font-bold text-xl font-semibold text-ink">Prayer Received</h3>
            <p class="mt-2 text-ink-muted">Thank you for sharing your prayer request. Our prayer team will uphold you in prayer.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <div class="hp-field" aria-hidden="true">
                <label for="prayer-website">Website</label>
                <input type="text" id="prayer-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @error('form')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label for="prayer-name" class="form-label">Your Name <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="prayer-name" wire:model.blur="name" class="form-input" required autocomplete="name" aria-required="true" @error('name') aria-invalid="true" aria-describedby="prayer-name-error" @enderror>
                @error('name')<p id="prayer-name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="prayer-email" class="form-label">Email Address <span class="text-sm font-normal text-ink-muted">(optional)</span></label>
                <input type="email" id="prayer-email" wire:model.blur="email" class="form-input" autocomplete="email" @error('email') aria-invalid="true" aria-describedby="prayer-email-error" @enderror>
                @error('email')<p id="prayer-email-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="prayer-request" class="form-label">Prayer Request <span class="text-red-600" aria-hidden="true">*</span></label>
                <textarea id="prayer-request" wire:model.blur="request" rows="6" class="form-input resize-y" required aria-required="true" placeholder="Share your prayer need…" @error('request') aria-invalid="true" aria-describedby="prayer-request-error" @enderror></textarea>
                @error('request')<p id="prayer-request-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="flex items-start gap-3">
                <input type="checkbox" id="prayer-confidential" wire:model="confidential" class="mt-1 h-5 w-5 rounded border-navy/20 text-brand focus:ring-royal/20">
                <label for="prayer-confidential" class="text-sm text-ink-muted">
                    Keep this request confidential (only shared with the prayer team)
                </label>
            </div>

            <div>
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Submit Prayer Request</span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            </div>
        </form>
    @endif
</div>
