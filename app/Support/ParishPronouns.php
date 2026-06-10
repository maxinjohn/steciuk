<?php

namespace App\Support;

class ParishPronouns
{
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
