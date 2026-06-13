<div>
    <form wire:submit="register" class="member-register-form space-y-6" novalidate>
        <div class="hp-field" aria-hidden="true">
            <label for="register-website">Website</label>
            <input type="text" id="register-website" wire:model="website" tabindex="-1" autocomplete="off">
        </div>

        @error('form')
            <div class="member-alert member-alert--error" role="alert">{{ $message }}</div>
        @enderror

        <div class="member-register-section">
            <h3 class="member-register-section-title">Your details</h3>
            <p class="member-register-section-intro">Register for your own parish member account. After approval, you can add household members from your account — each person gets their own profile.</p>

            <div class="space-y-5">
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="register-first-name" class="form-label">First name <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input type="text" id="register-first-name" wire:model.blur="first_name" class="form-input" required autocomplete="given-name" @error('first_name') aria-invalid="true" @enderror>
                        @error('first_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                        @error('name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="register-last-name" class="form-label">Last name</label>
                        <input type="text" id="register-last-name" wire:model.blur="last_name" class="form-input" autocomplete="family-name" @error('last_name') aria-invalid="true" @enderror>
                        @error('last_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="register-pronouns" class="form-label">Pronouns <span class="text-red-600" aria-hidden="true">*</span></label>
                        <select id="register-pronouns" wire:model.blur="pronouns" class="form-input" required aria-required="true" @error('pronouns') aria-invalid="true" @enderror>
                            @foreach ($pronounOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('pronouns')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="register-gender" class="form-label">Gender <span class="text-red-600" aria-hidden="true">*</span></label>
                        <select id="register-gender" wire:model.blur="gender" class="form-input" required aria-required="true" @error('gender') aria-invalid="true" @enderror>
                            @foreach ($genderOptions as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('gender')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="register-email" class="form-label">Email address <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input type="email" id="register-email" wire:model.blur="email" class="form-input" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
                        @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="register-phone" class="form-label">Phone number <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input type="tel" id="register-phone" wire:model.blur="phone" class="form-input" required autocomplete="tel" placeholder="e.g. 07700 900123" @error('phone') aria-invalid="true" @enderror>
                        @error('phone')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div>
                    <label for="register-dob" class="form-label">Date of birth <span class="text-red-600" aria-hidden="true">*</span></label>
                    <input type="date" id="register-dob" wire:model.blur="date_of_birth" class="form-input" required @error('date_of_birth') aria-invalid="true" @enderror>
                    @error('date_of_birth')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                </div>

                <x-uk-address-fields
                    id-prefix="register"
                    :postcode-lookup-message="$postcodeLookupMessage"
                    :postcode-lookup-error="$postcodeLookupError"
                    :postcode-address-options="$postcodeAddressOptions"
                    :selected-address-id="$selectedAddressId"
                />

                <div>
                    <label for="register-location" class="form-label">Preferred worship location</label>
                    <select id="register-location" wire:model.blur="preferred_worship_location" class="form-input">
                        <option value="">Select a location</option>
                        @foreach ($worshipLocations as $value => $label)
                            <option value="{{ $value }}">{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="register-password" class="form-label">Password <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input type="password" id="register-password" wire:model="password" class="form-input" required autocomplete="new-password" @error('password') aria-invalid="true" @enderror>
                        @error('password')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="register-password-confirmation" class="form-label">Confirm password <span class="text-red-600" aria-hidden="true">*</span></label>
                        <input type="password" id="register-password-confirmation" wire:model="password_confirmation" class="form-input" required autocomplete="new-password">
                    </div>
                </div>
            </div>
        </div>

        <x-turnstile-field
            element-id="turnstile-register"
            :turnstile-enabled="$turnstileEnabled ?? false"
            :turnstile-site-key="$turnstileSiteKey ?? ''"
        />

        <div class="member-register-section">
            <h3 class="member-register-section-title">Privacy & consent</h3>
            <div class="space-y-4">
                <label class="member-consent-label">
                    <input type="checkbox" wire:model="accept_privacy" class="member-consent-checkbox" @error('accept_privacy') aria-invalid="true" @enderror>
                    <span>I have read and accept the <a href="{{ $privacyPolicyUrl }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline">Privacy Policy</a> <span class="text-red-600">*</span></span>
                </label>
                @error('accept_privacy')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <label class="member-consent-label">
                    <input type="checkbox" wire:model="accept_terms" class="member-consent-checkbox" @error('accept_terms') aria-invalid="true" @enderror>
                    <span>I agree to the <a href="{{ $termsUrl }}" target="_blank" rel="noopener noreferrer" class="text-brand hover:underline">Terms of Use</a> for this website <span class="text-red-600">*</span></span>
                </label>
                @error('accept_terms')<p class="form-error" role="alert">{{ $message }}</p>@enderror

                <label class="member-consent-label">
                    <input type="checkbox" wire:model="marketing_consent" class="member-consent-checkbox">
                    <span>I would like to receive occasional parish news and event updates by email (optional — you can change this later in your account)</span>
                </label>
            </div>
        </div>

        <div class="member-alert member-alert--info">
            Registrations are reviewed by the parish leadership team before your account is activated. You will not be able to sign in until approval is complete.
        </div>

        <div class="flex flex-wrap items-center gap-4">
            <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="register">Submit registration</span>
                <span wire:loading wire:target="register">Submitting…</span>
            </button>
            <p class="text-sm text-ink-muted">Already registered? <a href="{{ route('login') }}" class="text-brand hover:underline">Sign in</a></p>
        </div>
    </form>
</div>
