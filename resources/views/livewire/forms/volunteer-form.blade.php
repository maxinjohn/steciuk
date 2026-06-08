<div>
    @if ($submitted)
        <div class="rounded-2xl border border-green-200 bg-green-50 px-6 py-8 text-center" role="status" aria-live="polite">
            <svg class="mx-auto h-12 w-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 font-bold text-xl font-semibold text-ink">Thank You</h3>
            <p class="mt-2 text-ink-muted">Your volunteer interest has been received. We will be in touch about serving opportunities.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <div class="hp-field" aria-hidden="true">
                <label for="volunteer-website">Website</label>
                <input type="text" id="volunteer-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @error('form')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label for="volunteer-name" class="form-label">Full Name <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="volunteer-name" wire:model.blur="name" class="form-input" required autocomplete="name" aria-required="true" @error('name') aria-invalid="true" aria-describedby="volunteer-name-error" @enderror>
                @error('name')<p id="volunteer-name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="volunteer-email" class="form-label">Email Address <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="email" id="volunteer-email" wire:model.blur="email" class="form-input" required autocomplete="email" aria-required="true" @error('email') aria-invalid="true" aria-describedby="volunteer-email-error" @enderror>
                @error('email')<p id="volunteer-email-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="volunteer-phone" class="form-label">Phone Number</label>
                <input type="tel" id="volunteer-phone" wire:model.blur="phone" class="form-input" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="volunteer-phone-error" @enderror>
                @error('phone')<p id="volunteer-phone-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="volunteer-ministry" class="form-label">Ministry Interest <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="volunteer-ministry" wire:model.blur="ministry_interest" class="form-input" required aria-required="true" placeholder="e.g. Choir, Sunday School, Youth" @error('ministry_interest') aria-invalid="true" aria-describedby="volunteer-ministry-error" @enderror>
                @error('ministry_interest')<p id="volunteer-ministry-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="volunteer-experience" class="form-label">Experience & Skills</label>
                <textarea id="volunteer-experience" wire:model.blur="experience" rows="4" class="form-input resize-y" placeholder="Tell us about any relevant experience…" @error('experience') aria-invalid="true" aria-describedby="volunteer-experience-error" @enderror></textarea>
                @error('experience')<p id="volunteer-experience-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Express Interest</span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            </div>
        </form>
    @endif
</div>
