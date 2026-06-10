<?php

namespace App\Support;

class UserName
{
    /**
     * @return array{first_name: string, last_name: string}
     */
    public static function split(?string $fullName): array
    {
        $fullName = trim((string) $fullName);

        if ($fullName === '') {
            return ['first_name' => '', 'last_name' => ''];
        }

        $parts = preg_split('/\s+/u', $fullName) ?: [];

        if (count($parts) === 1) {
            return ['first_name' => $parts[0], 'last_name' => ''];
        }

        $lastName = (string) array_pop($parts);

        return [
            'first_name' => trim(implode(' ', $parts)),
            'last_name' => $lastName,
        ];
    }

    public static function fromParts(?string $firstName, ?string $lastName): string
    {
        return trim(trim((string) $firstName).' '.trim((string) $lastName));
    }

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalize(array $data): array
    {
        if (filled($data['first_name'] ?? null) || filled($data['last_name'] ?? null)) {
            $data['first_name'] = trim((string) ($data['first_name'] ?? ''));
            $data['last_name'] = trim((string) ($data['last_name'] ?? ''));
            $data['name'] = self::fromParts($data['first_name'], $data['last_name']);
        } elseif (filled($data['name'] ?? null)) {
            $parts = self::split((string) $data['name']);
            $data['first_name'] = $parts['first_name'];
            $data['last_name'] = $parts['last_name'];
            $data['name'] = self::fromParts($data['first_name'], $data['last_name']);
        }

        return $data;
    }
}
