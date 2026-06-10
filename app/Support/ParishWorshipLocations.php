<?php

namespace App\Support;

class ParishWorshipLocations
{
    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            'Manchester',
            'Leicester',
            'Dartford',
            'Sunderland',
            'Bristol',
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return array_combine(static::all(), static::all());
    }
}
