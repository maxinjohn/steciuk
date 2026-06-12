<?php

namespace Tests\Unit;

use App\Support\SiteBrandingAssets;
use App\Support\SiteLogoProcessor;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SiteBrandingAssetsTest extends TestCase
{
    public function test_bundled_parish_logo_exists_in_public_images(): void
    {
        $this->assertTrue(SiteBrandingAssets::bundledLogoExists());
    }

    public function test_bundled_parish_mark_exists_in_public_images(): void
    {
        $this->assertTrue(SiteBrandingAssets::bundledMarkExists());
    }

    public function test_ensure_parish_logo_copies_into_uploads_disk(): void
    {
        Storage::fake('public');

        $path = SiteBrandingAssets::ensureParishLogoInUploads();

        $this->assertSame(SiteBrandingAssets::UPLOAD_LOGO_RELATIVE, $path);
        Storage::disk('public')->assertExists(SiteBrandingAssets::UPLOAD_LOGO_RELATIVE);
        Storage::disk('public')->assertExists(SiteBrandingAssets::UPLOAD_MARK_RELATIVE);
    }

    public function test_is_parish_logo_detects_upload_and_bundled_paths(): void
    {
        $this->assertTrue(SiteBrandingAssets::isParishLogo(SiteBrandingAssets::UPLOAD_LOGO_RELATIVE));
        $this->assertTrue(SiteBrandingAssets::isParishLogo('/'.SiteBrandingAssets::BUNDLED_LOGO_PUBLIC));
        $this->assertFalse(SiteBrandingAssets::isParishLogo('/images/steci-mark.svg'));
    }

    public function test_mark_path_is_derived_from_uploaded_logo_path(): void
    {
        $this->assertSame(
            'settings/branding/church-logo-mark.png',
            SiteLogoProcessor::markPathFor('settings/branding/church-logo.png'),
        );
    }

    public function test_custom_uploaded_logo_uses_header_lockup(): void
    {
        $this->assertTrue(SiteLogoProcessor::usesHeaderLockup('settings/branding/custom-logo.png'));
        $this->assertFalse(SiteLogoProcessor::usesHeaderLockup('/images/steci-mark.svg'));
    }

    public function test_logo_processor_creates_header_mark_for_upload(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required.');
        }

        Storage::fake('public');

        $logoPath = 'settings/branding/custom-logo.png';
        $source = UploadedFile::fake()->image('custom-logo.png', 320, 420);

        Storage::disk('public')->put($logoPath, file_get_contents($source->getRealPath()));

        $this->assertTrue(SiteLogoProcessor::process($logoPath));
        Storage::disk('public')->assertExists(SiteLogoProcessor::markPathFor($logoPath));
    }
}
