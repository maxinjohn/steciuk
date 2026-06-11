<?php

namespace App\Support;

use Illuminate\Validation\Rule;

class UserProfileAttributes
{
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        if (array_key_exists('pronouns', $data)) {
            $data['pronouns'] = filled($data['pronouns'] ?? null)
                ? trim((string) $data['pronouns'])
                : null;
        }

        if (array_key_exists('gender', $data)) {
            $data['gender'] = filled($data['gender'] ?? null)
                ? trim((string) $data['gender'])
                : null;
        }

        if (array_key_exists('phone', $data)) {
            $data['phone'] = filled($data['phone'] ?? null)
                ? trim((string) $data['phone'])
                : null;
        }

        if (array_key_exists('preferred_worship_location', $data)) {
            $data['preferred_worship_location'] = filled($data['preferred_worship_location'] ?? null)
                ? trim((string) $data['preferred_worship_location'])
                : null;
        }

        foreach (['address_line_1', 'address_line_2', 'city', 'county', 'postcode'] as $field) {
            if (array_key_exists($field, $data)) {
                $data[$field] = filled($data[$field] ?? null)
                    ? trim((string) $data[$field])
                    : null;
            }
        }

        return $data;
    }

    /**
     * @return array<string, mixed>
     */
    public static function pronounsRules(): array
    {
        return [
            'required',
            'string',
            'max:50',
            Rule::in(array_keys(ParishPronouns::requiredOptions())),
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function genderRules(): array
    {
        return [
            'required',
            'string',
            'max:30',
            Rule::in(array_keys(ParishGender::requiredOptions())),
        ];
    }
}
