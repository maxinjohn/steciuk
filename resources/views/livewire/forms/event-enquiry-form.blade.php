<div>
    @if ($submitted)
        <div class="rounded-2xl border border-green-200 bg-green-50 px-6 py-8 text-center" role="status" aria-live="polite">
            <svg class="mx-auto h-12 w-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 font-bold text-xl font-semibold text-ink">Enquiry Sent</h3>
            <p class="mt-2 text-ink-muted">Thank you for your enquiry. We will get back to you shortly.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <div class="hp-field" aria-hidden="true">
                <label for="event-website">Website</label>
                <input type="text" id="event-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @error('form')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
            @enderror

            <div>
                <label for="event-name-field" class="form-label">Full Name <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="event-name-field" wire:model.blur="name" class="form-input" required autocomplete="name" aria-required="true" @error('name') aria-invalid="true" aria-describedby="event-name-error" @enderror>
                @error('name')<p id="event-name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="event-email" class="form-label">Email Address <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="email" id="event-email" wire:model.blur="email" class="form-input" required autocomplete="email" aria-required="true" @error('email') aria-invalid="true" aria-describedby="event-email-error" @enderror>
                @error('email')<p id="event-email-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="event-event-name" class="form-label">Event Name</label>
                <input type="text" id="event-event-name" wire:model.blur="event_name" class="form-input" @error('event_name') aria-invalid="true" aria-describedby="event-event-name-error" @enderror>
                @error('event_name')<p id="event-event-name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="event-message" class="form-label">Message <span class="text-red-600" aria-hidden="true">*</span></label>
                <textarea id="event-message" wire:model.blur="message" rows="4" class="form-input resize-y" required aria-required="true" @error('message') aria-invalid="true" aria-describedby="event-message-error" @enderror></textarea>
                @error('message')<p id="event-message-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Send Enquiry</span>
                    <span wire:loading wire:target="submit">Sending…</span>
                </button>
            </div>
        </form>
    @endif
</div>
