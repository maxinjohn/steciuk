<div>
    @if ($submitted)
        <div class="rounded-2xl border border-green-200 bg-green-50 px-6 py-8 text-center" role="status" aria-live="polite">
            <svg class="mx-auto h-12 w-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 font-bold text-xl font-semibold text-ink">Thank You</h3>
            <p class="mt-2 text-ink-muted">Your message has been received. We will respond as soon as possible.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            {{-- Honeypot --}}
            <div class="hp-field" aria-hidden="true">
                <label for="contact-website">Website</label>
                <input type="text" id="contact-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @error('form')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label for="contact-name" class="form-label">Full Name <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="contact-name" wire:model.blur="name" class="form-input" required autocomplete="name" aria-required="true" @error('name') aria-invalid="true" aria-describedby="contact-name-error" @enderror>
                @error('name')<p id="contact-name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="contact-email" class="form-label">Email Address <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="email" id="contact-email" wire:model.blur="email" class="form-input" required autocomplete="email" aria-required="true" @error('email') aria-invalid="true" aria-describedby="contact-email-error" @enderror>
                @error('email')<p id="contact-email-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="contact-phone" class="form-label">Phone Number</label>
                <input type="tel" id="contact-phone" wire:model.blur="phone" class="form-input" autocomplete="tel" @error('phone') aria-invalid="true" aria-describedby="contact-phone-error" @enderror>
                @error('phone')<p id="contact-phone-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="contact-message" class="form-label">Message <span class="text-red-600" aria-hidden="true">*</span></label>
                <textarea id="contact-message" wire:model.blur="message" rows="5" class="form-input resize-y" required aria-required="true" @error('message') aria-invalid="true" aria-describedby="contact-message-error" @enderror></textarea>
                @error('message')<p id="contact-message-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Send Message</span>
                    <span wire:loading wire:target="submit">Sending…</span>
                </button>
            </div>
        </form>
    @endif
</div>
