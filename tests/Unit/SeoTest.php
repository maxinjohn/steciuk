<?php

namespace Tests\Unit;

use App\Support\Seo;
use Tests\TestCase;

class SeoTest extends TestCase
{
    public function test_meta_text_decodes_html_entities(): void
    {
        $this->assertSame("Women's Fellowship", Seo::metaText('Women&#039;s Fellowship'));
        $this->assertSame('Tom & Jerry', Seo::metaText('Tom &amp; Jerry'));
    }

    public function test_document_title_uses_standard_site_suffix(): void
    {
        $site = 'STECI UK Parish';

        $this->assertSame(
            "Women's Fellowship | STECI UK Parish",
            Seo::documentTitle("Women's Fellowship", null, $site),
        );

        $this->assertSame(
            "Women's Fellowship | STECI UK Parish",
            Seo::documentTitle("Women's Fellowship | STECI UK Parish", null, 'St. Thomas Evangelical Church of India – UK Parish'),
        );

        $this->assertSame(
            "Sunday School | Ministries | STECI UK Parish",
            Seo::documentTitle('Sunday School', 'Ministries', $site),
        );
    }

    public function test_is_reserved_slug_blocks_app_routes(): void
    {
        $this->assertTrue(Seo::isReservedSlug('resources'));
        $this->assertTrue(Seo::isReservedSlug('ministries'));
        $this->assertFalse(Seo::isReservedSlug('womens-fellowship'));
    }

    public function test_truncate_description_decodes_entities(): void
    {
        $this->assertSame(
            "Women's prayer and fellowship.",
            Seo::truncateDescription("Women&#039;s prayer and fellowship."),
        );
    }
}
