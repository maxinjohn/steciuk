@props([
    'postcodeWireModel' => 'postcode',
    'lookupAction' => 'lookupPostcode',
    'postcodeLookupMessage' => '',
    'postcodeLookupError' => '',
    'postcodeAddressOptions' => [],
    'selectedAddressId' => null,
    'idPrefix' => 'address',
])

<fieldset class="uk-address-fields space-y-5">
    <legend class="form-label mb-1 block text-base font-semibold text-ink">UK Address</legend>
    <p class="text-sm text-ink-muted">Enter your postcode and click Find address to choose your property from the list.</p>

    <div class="grid gap-5 sm:grid-cols-[1fr_auto] sm:items-end">
        <div>
            <label for="{{ $idPrefix }}-postcode" class="form-label">Postcode <span class="text-red-600" aria-hidden="true">*</span></label>
            <input
                type="text"
                id="{{ $idPrefix }}-postcode"
                wire:model.blur="{{ $postcodeWireModel }}"
                class="form-input uppercase"
                autocomplete="postal-code"
                placeholder="e.g. M1 1AE"
                aria-required="true"
                @error('postcode') aria-invalid="true" aria-describedby="{{ $idPrefix }}-postcode-error" @enderror
            >
            @error('postcode')<p id="{{ $idPrefix }}-postcode-error" class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <button type="button" class="btn btn-outline w-full sm:w-auto" wire:click="{{ $lookupAction }}" wire:loading.attr="disabled" wire:target="{{ $lookupAction }}">
                <span wire:loading.remove wire:target="{{ $lookupAction }}">Find address</span>
                <span wire:loading wire:target="{{ $lookupAction }}">Looking up…</span>
            </button>
        </div>
    </div>

    @if ($postcodeLookupMessage !== '')
        <p class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800 dark:border-emerald-900/40 dark:bg-emerald-950/40 dark:text-emerald-100" role="status">{{ $postcodeLookupMessage }}</p>
    @endif

    @if ($postcodeLookupError !== '')
        <p class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900 dark:border-amber-900/40 dark:bg-amber-950/40 dark:text-amber-100" role="status">{{ $postcodeLookupError }}</p>
    @endif

    @if (count($postcodeAddressOptions) > 1)
        <div>
            <label for="{{ $idPrefix }}-picker" class="form-label">Select your address <span class="text-red-600" aria-hidden="true">*</span></label>
            <select
                id="{{ $idPrefix }}-picker"
                class="form-input"
                wire:model.live="selectedAddressId"
            >
                <option value="" @selected($selectedAddressId === null)>Choose an address…</option>
                @foreach ($postcodeAddressOptions as $option)
                    <option value="{{ $option['id'] }}" @selected($selectedAddressId === $option['id'])>{{ $option['label'] }}</option>
                @endforeach
            </select>
        </div>
    @endif

    <div>
        <label for="{{ $idPrefix }}-line-1" class="form-label">Address line 1 <span class="text-red-600" aria-hidden="true">*</span></label>
        <input
            type="text"
            id="{{ $idPrefix }}-line-1"
            wire:model.blur="address_line_1"
            class="form-input"
            autocomplete="address-line1"
            placeholder="House name or number and street"
            aria-required="true"
            @error('address_line_1') aria-invalid="true" aria-describedby="{{ $idPrefix }}-line-1-error" @enderror
        >
        @error('address_line_1')<p id="{{ $idPrefix }}-line-1-error" class="form-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="{{ $idPrefix }}-line-2" class="form-label">Address line 2 <span class="text-ink-muted">(optional)</span></label>
        <input
            type="text"
            id="{{ $idPrefix }}-line-2"
            wire:model.blur="address_line_2"
            class="form-input"
            autocomplete="address-line2"
            placeholder="Flat, locality, or additional detail"
            @error('address_line_2') aria-invalid="true" aria-describedby="{{ $idPrefix }}-line-2-error" @enderror
        >
        @error('address_line_2')<p id="{{ $idPrefix }}-line-2-error" class="form-error" role="alert">{{ $message }}</p>@enderror
    </div>

    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="{{ $idPrefix }}-city" class="form-label">Town or city <span class="text-red-600" aria-hidden="true">*</span></label>
            <input
                type="text"
                id="{{ $idPrefix }}-city"
                wire:model.blur="city"
                class="form-input"
                autocomplete="address-level2"
                aria-required="true"
                @error('city') aria-invalid="true" aria-describedby="{{ $idPrefix }}-city-error" @enderror
            >
            @error('city')<p id="{{ $idPrefix }}-city-error" class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>
        <div>
            <label for="{{ $idPrefix }}-county" class="form-label">County <span class="text-ink-muted">(optional)</span></label>
            <input
                type="text"
                id="{{ $idPrefix }}-county"
                wire:model.blur="county"
                class="form-input"
                autocomplete="address-level1"
                @error('county') aria-invalid="true" aria-describedby="{{ $idPrefix }}-county-error" @enderror
            >
            @error('county')<p id="{{ $idPrefix }}-county-error" class="form-error" role="alert">{{ $message }}</p>@enderror
        </div>
    </div>
</fieldset>
