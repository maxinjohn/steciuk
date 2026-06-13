<?php

namespace Tests\Unit;

use App\Models\Setting;
use Tests\TestCase;

class SettingAssetUrlTest extends TestCase
{
    public function test_asset_url_uses_root_relative_storage_path(): void
    {
        $this->assertSame(
            '/storage/settings/branding/steci-parish-logo.png',
            Setting::assetUrl('settings/branding/steci-parish-logo.png'),
        );
    }

    public function test_asset_url_preserves_root_relative_paths(): void
    {
        $this->assertSame(
            '/images/branding/steci-parish-logo.png',
            Setting::assetUrl('/images/branding/steci-parish-logo.png'),
        );
    }

    public function test_asset_url_preserves_external_urls(): void
    {
        $this->assertSame(
            'https://cdn.example.com/logo.png',
            Setting::assetUrl('https://cdn.example.com/logo.png'),
        );
    }
}
