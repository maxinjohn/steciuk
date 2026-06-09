<?php

namespace Tests\Feature;

use App\Support\AdminPanelConfig;
use App\Support\Seo;
use Tests\TestCase;

class AdminPanelConfigTest extends TestCase
{
    public function test_default_admin_path_is_admin(): void
    {
        $this->assertSame('admin', AdminPanelConfig::path());
        $this->assertSame('/admin', AdminPanelConfig::url());
        $this->assertSame('/admin/pages', AdminPanelConfig::url('pages'));
    }

    public function test_custom_admin_path_from_env(): void
    {
        config([
            'site.admin.path' => 'parish-office',
            'site.admin.name' => 'Parish Control Panel',
            'site.admin.short_name' => 'Control Panel',
        ]);

        $this->assertSame('parish-office', AdminPanelConfig::path());
        $this->assertSame('/parish-office/church-settings', AdminPanelConfig::url('church-settings'));
        $this->assertSame('Parish Control Panel', AdminPanelConfig::name());
        $this->assertTrue(Seo::isReservedSlug('parish-office'));
        $this->assertFalse(Seo::isReservedSlug('welcome'));
    }
}
