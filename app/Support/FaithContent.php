<?php

namespace App\Support;

use App\Models\Setting;

class FaithContent
{
    /**
     * @return array<int, array{text: string, ref: string}>
     */
    public static function sanctuaryVerses(): array
    {
        $stored = Setting::get('faith_sanctuary_verses');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : [];
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    public static function comfortCards(): array
    {
        $stored = Setting::get('faith_comfort_cards');
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : [];
    }
}
