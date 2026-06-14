<?php

namespace App\Support;

use App\Models\Setting;

class AdminSecurityConfig
{
    /** @var list<int> */
    public const ALLOWED_SESSION_MINUTES = [60, 90, 120, 180];

    public static function sessionLifetimeMinutes(): int
    {
        $stored = (int) Setting::get('admin_session_lifetime_minutes', 0);

        if (in_array($stored, self::ALLOWED_SESSION_MINUTES, true)) {
            return $stored;
        }

        $fallback = (int) config('security.session_lifetime_admin', 120);

        if (in_array($fallback, self::ALLOWED_SESSION_MINUTES, true)) {
            return $fallback;
        }

        // Honour explicit env/config overrides outside the admin UI whitelist (tests, custom deploys).
        if ($fallback > 0) {
            return $fallback;
        }

        return 120;
    }

    public static function sessionLifetimeSeconds(): int
    {
        return self::sessionLifetimeMinutes() * 60;
    }

    /**
     * @return array<int, string>
     */
    public static function sessionLifetimeOptions(): array
    {
        return [
            60 => '1 hour',
            90 => '1 hour 30 minutes',
            120 => '2 hours (recommended)',
            180 => '3 hours',
        ];
    }
}
