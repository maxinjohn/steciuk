<?php

namespace App\Livewire\Concerns;

use App\Models\User;
use App\Rules\UkPostcode;
use App\Services\UkAddressLookup;
use App\Support\UkPostcode as UkPostcodeSupport;
use Livewire\Attributes\Validate;

trait HandlesUkAddress
{
    #[Validate('required|string|max:255')]
    public string $address_line_1 = '';

    #[Validate('nullable|string|max:255')]
    public string $address_line_2 = '';

    #[Validate('required|string|max:120')]
    public string $city = '';

    #[Validate('nullable|string|max:120')]
    public string $county = '';

    #[Validate('required|string|max:12')]
    public string $postcode = '';

    public string $postcodeLookupMessage = '';

    public string $postcodeLookupError = '';

    /** @var list<array{id: string, label: string, line_1: string, line_2: string, city: string, county: string}> */
    public array $postcodeAddressOptions = [];

    public ?string $selectedAddressId = null;

    /**
     * @return array<string, mixed>
     */
    protected function ukAddressValidationRules(bool $required = true): array
    {
        $requiredRule = $required ? 'required' : 'nullable';

        return [
            'address_line_1' => "{$requiredRule}|string|max:255",
            'address_line_2' => 'nullable|string|max:255',
            'city' => "{$requiredRule}|string|max:120",
            'county' => 'nullable|string|max:120',
            'postcode' => [$requiredRule, 'string', 'max:12', new UkPostcode],
        ];
    }

    /**
     * @return array<string, string>
     */
    protected function ukAddressFormData(): array
    {
        return [
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'city' => $this->city,
            'county' => $this->county,
            'postcode' => UkPostcodeSupport::normalize($this->postcode) ?? strtoupper(trim($this->postcode)),
        ];
    }

    protected function fillUkAddressFromUser(?User $user): void
    {
        if (! $user) {
            return;
        }

        $this->address_line_1 = (string) ($user->address_line_1 ?? '');
        $this->address_line_2 = (string) ($user->address_line_2 ?? '');
        $this->city = (string) ($user->city ?? '');
        $this->county = (string) ($user->county ?? '');
        $this->postcode = (string) ($user->postcode ?? '');
        $this->clearPostcodeLookupState();
    }

    public function updatedPostcode(): void
    {
        $this->clearPostcodeLookupState(keepErrors: true);
    }

    public function lookupPostcode(): void
    {
        $this->postcodeLookupMessage = '';
        $this->postcodeLookupError = '';
        $this->postcodeAddressOptions = [];
        $this->selectedAddressId = null;

        $this->validateOnly('postcode', [
            'postcode' => ['required', 'string', 'max:12', new UkPostcode],
        ]);

        $result = app(UkAddressLookup::class)->lookup($this->postcode);

        if ($result === null) {
            $this->postcodeLookupError = 'Postcode not found. Please enter your address manually.';

            return;
        }

        $this->postcode = $result['postcode'];
        $this->postcodeAddressOptions = $result['addresses'];

        if (count($this->postcodeAddressOptions) === 1) {
            $this->applyAddressOption($this->postcodeAddressOptions[0]);
            $this->selectedAddressId = $this->postcodeAddressOptions[0]['id'];
            $this->postcodeLookupMessage = 'Address filled automatically from your postcode.';

            return;
        }

        if (count($this->postcodeAddressOptions) > 1) {
            $this->postcodeLookupMessage = 'Select your address from the list below.';

            return;
        }

        if (blank($this->city) && filled($result['city'])) {
            $this->city = $result['city'];
        }

        if (blank($this->county) && filled($result['county'])) {
            $this->county = $result['county'];
        }

        $this->postcodeLookupMessage = 'Town and county filled from your postcode. OpenStreetMap has no listed properties for this postcode — please enter your house name or number and street below.';
    }

    public function selectAddress(string $addressId): void
    {
        $option = collect($this->postcodeAddressOptions)->firstWhere('id', $addressId);

        if (! is_array($option)) {
            return;
        }

        $this->applyAddressOption($option);
        $this->selectedAddressId = $addressId;
        $this->postcodeLookupMessage = 'Address filled from your selection.';
        $this->postcodeLookupError = '';
    }

    public function updatedSelectedAddressId(?string $value): void
    {
        if (blank($value)) {
            return;
        }

        $this->selectAddress($value);
    }

    /**
     * @param  array{id: string, label: string, line_1: string, line_2: string, city: string, county: string}  $option
     */
    protected function applyAddressOption(array $option): void
    {
        $this->address_line_1 = $option['line_1'];
        $this->address_line_2 = $option['line_2'];
        $this->city = $option['city'];
        $this->county = $option['county'];
    }

    protected function clearPostcodeLookupState(bool $keepErrors = false): void
    {
        $this->postcodeAddressOptions = [];
        $this->selectedAddressId = null;
        $this->postcodeLookupMessage = '';

        if (! $keepErrors) {
            $this->postcodeLookupError = '';
        }
    }
}
