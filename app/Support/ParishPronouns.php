<?php

namespace App\Support;

class ParishPronouns
{
    /**
     * Options for required admin / registration fields (no blank placeholder).
     *
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
            'she/her' => 'She / her',
            'he/him' => 'He / him',
            'they/them' => 'They / them',
            'she/they' => 'She / they',
            'he/they' => 'He / they',
            'ze/zir' => 'Ze / zir',
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
