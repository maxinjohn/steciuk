<?php

namespace Tests\Unit;

use App\Support\SiteLayoutData;
use PHPUnit\Framework\TestCase;

class SiteLayoutDataTest extends TestCase
{
    public function test_footer_about_tagline_strips_leading_motto(): void
    {
        $motto = 'For the Word of God and for the testimony of Jesus Christ';
        $tagline = $motto.' — Word, worship, and witness across the United Kingdom.';

        $this->assertSame(
            'Word, worship, and witness across the United Kingdom.',
            SiteLayoutData::footerAboutTagline($tagline, $motto),
        );
    }

    public function test_footer_about_tagline_uses_fallback_when_empty(): void
    {
        $this->assertSame(
            'Word, worship, and witness across the United Kingdom.',
            SiteLayoutData::footerAboutTagline(null, null),
        );
    }

    public function test_nav_menu_prefers_mobile_menu_when_present(): void
    {
        $mobile = collect([(object) ['label' => 'Mobile']]);
        $header = collect([(object) ['label' => 'Header']]);

        $this->assertSame('Mobile', SiteLayoutData::navMenu($mobile, $header)->first()->label);
        $this->assertSame('Header', SiteLayoutData::navMenu(collect(), $header)->first()->label);
    }

    public function test_mobile_drawer_menu_excludes_member_area_item(): void
    {
        $menu = collect([
            (object) ['seed_key' => 'home', 'label' => 'Home'],
            (object) ['seed_key' => 'member-area', 'label' => 'Member area'],
            (object) ['seed_key' => 'contact', 'label' => 'Contact'],
        ]);

        $drawerMenu = SiteLayoutData::mobileDrawerMenu($menu);

        $this->assertCount(2, $drawerMenu);
        $this->assertSame(['Home', 'Contact'], $drawerMenu->pluck('label')->all());
    }

    public function test_mobile_drawer_menu_excludes_member_area_by_label(): void
    {
        $menu = collect([
            (object) ['seed_key' => null, 'label' => 'Home'],
            (object) ['seed_key' => null, 'label' => 'Member area'],
        ]);

        $drawerMenu = SiteLayoutData::mobileDrawerMenu($menu);

        $this->assertSame(['Home'], $drawerMenu->pluck('label')->all());
    }

    public function test_header_menu_excludes_member_area_item(): void
    {
        $menu = collect([
            (object) ['seed_key' => 'home', 'label' => 'Home'],
            (object) ['seed_key' => 'member-area', 'label' => 'Member area'],
            (object) ['seed_key' => 'contact', 'label' => 'Contact'],
        ]);

        $headerMenu = SiteLayoutData::withoutMemberAreaMenu($menu);

        $this->assertSame(['Home', 'Contact'], $headerMenu->pluck('label')->all());
    }

    public function test_is_member_area_menu_item_matches_seed_keys(): void
    {
        $this->assertTrue(SiteLayoutData::isMemberAreaMenuItem((object) ['seed_key' => 'member-area', 'label' => 'Other']));
        $this->assertTrue(SiteLayoutData::isMemberAreaMenuItem((object) ['seed_key' => 'members.sign-in', 'label' => 'Sign in']));
        $this->assertFalse(SiteLayoutData::isMemberAreaMenuItem((object) ['seed_key' => 'contact', 'label' => 'Contact']));
    }
}
