<?php

namespace App\Support;

class ParishGender
{
    /**
     * @return array<string, string>
     */
    public static function requiredOptions(): array
    {
        return array_filter(
            self::options(),
            fn (string $label, string $value): bool => $value !== '',
            ARRAY_FILTER_USE_BOTH,
        );
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            '' => 'Prefer not to say',
            'female' => 'Female',
            'male' => 'Male',
            'non-binary' => 'Non-binary',
            'other' => 'Other',
        ];
    }

    public static function label(?string $value): ?string
    {
        if (! filled($value)) {
            return null;
        }

        return self::options()[$value] ?? $value;
    }
}
