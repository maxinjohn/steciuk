<?php

namespace Tests\Unit;

use App\Support\AdminSecurityConfig;
use PHPUnit\Framework\TestCase;

class AdminSecurityConfigTest extends TestCase
{
    public function test_allowed_session_minutes_include_one_and_two_hours(): void
    {
        $this->assertContains(60, AdminSecurityConfig::ALLOWED_SESSION_MINUTES);
        $this->assertContains(120, AdminSecurityConfig::ALLOWED_SESSION_MINUTES);
    }

    public function test_session_lifetime_options_cover_each_allowed_value(): void
    {
        foreach (AdminSecurityConfig::ALLOWED_SESSION_MINUTES as $minutes) {
            $this->assertArrayHasKey($minutes, AdminSecurityConfig::sessionLifetimeOptions());
        }
    }
}
