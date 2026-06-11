<?php

namespace Tests\Unit;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SettingTextTest extends TestCase
{
    use RefreshDatabase;

    public function test_text_returns_default_when_value_is_blank(): void
    {
        Setting::set('admin_dashboard_verse', '', 'admin');

        $this->assertSame(
            'Be still, and know that I am God.',
            Setting::text('admin_dashboard_verse', 'Be still, and know that I am God.'),
        );
    }

    public function test_text_returns_trimmed_stored_value(): void
    {
        Setting::set('admin_dashboard_verse', '  Custom verse.  ', 'admin');

        $this->assertSame('Custom verse.', Setting::text('admin_dashboard_verse', 'Default'));
    }
}
