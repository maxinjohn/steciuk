<?php

namespace App\Rules;

use App\Support\SafeUrl;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SafeHttpUrl implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value === null || $value === '') {
            return;
        }

        if (! SafeUrl::isSafe((string) $value)) {
            $fail('The :attribute must be a safe http(s), mailto, tel, or relative URL.');
        }
    }
}
