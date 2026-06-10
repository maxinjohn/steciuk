<div>
    @if ($saved)
        <div class="mb-5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
            Your profile has been updated.
        </div>
    @endif

    <form wire:submit="save" class="space-y-5" novalidate>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="profile-first-name" class="form-label">First name <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="text" id="profile-first-name" wire:model.blur="first_name" class="form-input" required autocomplete="given-name" @error('first_name') aria-invalid="true" @enderror>
                @error('first_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="profile-last-name" class="form-label">Last name</label>
                <input type="text" id="profile-last-name" wire:model.blur="last_name" class="form-input" autocomplete="family-name" @error('last_name') aria-invalid="true" @enderror>
                @error('last_name')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="profile-pronouns" class="form-label">Pronouns</label>
                <select id="profile-pronouns" wire:model.blur="pronouns" class="form-input">
                    @foreach ($pronounOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('pronouns')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="profile-gender" class="form-label">Gender</label>
                <select id="profile-gender" wire:model.blur="gender" class="form-input">
                    @foreach ($genderOptions as $value => $label)
                        <option value="{{ $value }}">{{ $label }}</option>
                    @endforeach
                </select>
                @error('gender')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="profile-email" class="form-label">Email address <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="email" id="profile-email" wire:model.blur="email" class="form-input" required autocomplete="email" @error('email') aria-invalid="true" @enderror>
                @error('email')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="profile-phone" class="form-label">Phone number <span class="text-red-600" aria-hidden="true">*</span></label>
                <input type="tel" id="profile-phone" wire:model.blur="phone" class="form-input" required autocomplete="tel" @error('phone') aria-invalid="true" @enderror>
                @error('phone')<p class="form-error" role="alert">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label for="profile-dob" class="form-label">Date of birth <span class="text-red-600" aria-hidden="true">*</span></label>
            <input type="date" id="profile-dob" wire:model.blur="date_of_birth" class="form-input" required @error('date_of_birth') aria-invalid="true" @enderror>
            @error('date_of_birth')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <x-uk-address-fields
            id-prefix="profile"
            :postcode-lookup-message="$postcodeLookupMessage"
            :postcode-lookup-error="$postcodeLookupError"
            :postcode-address-options="$postcodeAddressOptions"
            :selected-address-id="$selectedAddressId"
        />

        <div>
            <label for="profile-location" class="form-label">Preferred worship location</label>
            <select id="profile-location" wire:model.blur="preferred_worship_location" class="form-input">
                <option value="">Select a location</option>
                @foreach ($worshipLocations as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
            @error('preferred_worship_location')<p class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>

        <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
            <span wire:loading.remove wire:target="save">Save contact details</span>
            <span wire:loading wire:target="save">Saving…</span>
        </button>
    </form>
</div>
