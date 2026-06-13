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
}
