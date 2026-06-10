<?php

namespace App\Support;

class ParishGender
{
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
