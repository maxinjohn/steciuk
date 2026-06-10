<div>
    @if ($submitted)
        <div class="form-success-gen-z" role="status" aria-live="polite">
            <svg class="mx-auto h-12 w-12 text-green-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="mt-4 font-bold text-xl font-semibold text-ink">Registration Received</h3>
            <p class="mt-2 text-ink-muted">Thank you for your interest in joining our parish. A member of our leadership team will be in touch soon.</p>
        </div>
    @else
        <form wire:submit="submit" class="space-y-5" novalidate>
            <div class="hp-field" aria-hidden="true">
                <label for="member-website">Website</label>
                <input type="text" id="member-website" wire:model="website" tabindex="-1" autocomplete="off">
            </div>

            @error('form')
                <div class="rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700" role="alert">{{ $message }}</div>
            @enderror

            @guest
                <p class="rounded-xl border border-[var(--site-border)] bg-[var(--site-surface)] px-4 py-3 text-sm text-ink-muted">
                    Already part of the parish online community?
                    <a href="{{ route('register') }}" class="text-brand hover:underline">Create a member account</a>
                    or <a href="{{ route('login') }}" class="text-brand hover:underline">sign in</a> to pre-fill this form.
                </p>
            @endguest

            <div>
                <label for="member-name" class="form-label">Full Name <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="member-name" wire:model.blur="name" class="form-input" required autocomplete="name" aria-required="true" @error('name') aria-invalid="true" aria-describedby="member-name-error" @enderror>
                @error('name')<p id="member-name-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-5 sm:grid-cols-2">
                <div>
                    <label for="member-email" class="form-label">Email Address <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input type="email" id="member-email" wire:model.blur="email" class="form-input" required autocomplete="email" aria-required="true" @error('email') aria-invalid="true" aria-describedby="member-email-error" @enderror>
                    @error('email')<p id="member-email-error" class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="member-phone" class="form-label">Phone Number <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input type="tel" id="member-phone" wire:model.blur="phone" class="form-input" required autocomplete="tel" aria-required="true" @error('phone') aria-invalid="true" aria-describedby="member-phone-error" @enderror>
                    @error('phone')<p id="member-phone-error" class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>
            </div>

            <div>
                <label for="member-dob" class="form-label">Date of birth</label>
                <input type="date" id="member-dob" wire:model.blur="date_of_birth" class="form-input" @error('date_of_birth') aria-invalid="true" aria-describedby="member-dob-error" @enderror>
                @error('date_of_birth')<p id="member-dob-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <x-uk-address-fields
                id-prefix="member"
                :postcode-lookup-message="$postcodeLookupMessage"
                :postcode-lookup-error="$postcodeLookupError"
                :postcode-address-options="$postcodeAddressOptions"
                :selected-address-id="$selectedAddressId"
            />

            <div>
                <label for="member-location" class="form-label">Preferred Worship Location</label>
                <select id="member-location" wire:model.blur="location" class="form-input" @error('location') aria-invalid="true" aria-describedby="member-location-error" @enderror>
                    <option value="">Select a location</option>
                    @foreach ($worshipLocations as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('location')<p id="member-location-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <label for="member-notes" class="form-label">Additional Notes</label>
                <textarea id="member-notes" wire:model.blur="notes" rows="4" class="form-input resize-y" placeholder="Tell us a little about yourself…" @error('notes') aria-invalid="true" aria-describedby="member-notes-error" @enderror></textarea>
                @error('notes')<p id="member-notes-error" class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>

            <div>
                <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                    <span wire:loading.remove wire:target="submit">Submit Registration</span>
                    <span wire:loading wire:target="submit">Submitting…</span>
                </button>
            </div>
        </form>
    @endif
</div>
