<?php

namespace App\Rules;

use App\Support\UkPostcode as UkPostcodeSupport;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UkPostcode implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! UkPostcodeSupport::isValid(is_string($value) ? $value : null)) {
            $fail('Please enter a valid UK postcode (for example, SW1A 1AA).');
        }
    }
}
