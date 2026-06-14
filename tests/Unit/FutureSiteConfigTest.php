<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\FutureSiteConfig;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FutureSiteConfigTest extends TestCase
{
    use RefreshDatabase;
    public function test_speculation_paths_are_normalized(): void
    {
        Config::set('site.future.speculation_paths', [
            'service-times',
            '/give',
            '',
            '/',
        ]);

        $this->assertSame([
            '/service-times',
            '/give',
        ], FutureSiteConfig::speculationPrefetchPaths());
    }

    public function test_reading_progress_is_limited_to_article_routes(): void
    {
        Config::set('site.future.enabled', true);
        Config::set('site.future.reading_progress', true);

        $request = Request::create('/news/a-story', 'GET');
        $request->setRouteResolver(fn () => new \Illuminate\Routing\Route('GET', '/news/{slug}', [])->name('news.show'));

        $this->assertTrue(FutureSiteConfig::readingProgressForRequest($request));

        $home = Request::create('/', 'GET');
        $home->setRouteResolver(fn () => new \Illuminate\Routing\Route('GET', '/', [])->name('home'));

        $this->assertFalse(FutureSiteConfig::readingProgressForRequest($home));
    }

    public function test_speculation_paths_exclude_current_request(): void
    {
        Config::set('site.future.speculation_paths', [
            '/service-times',
            '/events',
        ]);
        Config::set('site.future.speculation_prerender_paths', [
            '/service-times',
        ]);

        $request = Request::create('/service-times', 'GET');

        $this->assertSame(
            ['/events'],
            FutureSiteConfig::speculationPrefetchPathsForRequest($request),
        );
        $this->assertSame(
            [],
            FutureSiteConfig::speculationPrerenderPathsForRequest($request),
        );
    }

    public function test_speculation_is_disabled_by_default(): void
    {
        Config::set('site.future.speculation_rules', false);

        $this->assertFalse(FutureSiteConfig::speculationEnabled());
    }

    public function test_speculation_rules_payload_is_empty_when_disabled(): void
    {
        Config::set('site.future.speculation_rules', true);
        Config::set('site.future.speculation_paths', [
            '/service-times',
            '/events',
        ]);

        Setting::set('public_ui_experience', json_encode([
            'enabled' => true,
            'speculation_rules' => true,
            'reading_progress' => true,
            'heavenly_atmosphere' => true,
        ]), 'public_ui');

        $request = Request::create('/prayer-request', 'GET');

        $this->assertFalse(FutureSiteConfig::speculationEnabled());
        $this->assertSame([], FutureSiteConfig::speculationRulesPayload($request));
    }

    public function test_future_features_can_be_disabled(): void
    {
        Setting::set('public_ui_experience', json_encode([
            'enabled' => false,
            'speculation_rules' => true,
            'reading_progress' => true,
            'heavenly_atmosphere' => true,
        ]), 'public_ui');

        $this->assertFalse(FutureSiteConfig::speculationEnabled());
    }
}
