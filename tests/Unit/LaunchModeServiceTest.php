<?php

namespace Tests\Unit;

use App\Services\LaunchModeService;
use Tests\TestCase;

class LaunchModeServiceTest extends TestCase
{
    public function test_path_prefix_matching(): void
    {
        $this->assertTrue(LaunchModeService::pathMatches('events', 'events', LaunchModeService::MATCH_PREFIX));
        $this->assertTrue(LaunchModeService::pathMatches('events/summer-fair', 'events', LaunchModeService::MATCH_PREFIX));
        $this->assertFalse(LaunchModeService::pathMatches('news', 'events', LaunchModeService::MATCH_PREFIX));
    }

    public function test_path_exact_matching(): void
    {
        $this->assertTrue(LaunchModeService::pathMatches('events', 'events', LaunchModeService::MATCH_EXACT));
        $this->assertFalse(LaunchModeService::pathMatches('events/summer-fair', 'events', LaunchModeService::MATCH_EXACT));
    }

    public function test_normalize_countdown_input_accepts_strings(): void
    {
        $parsed = LaunchModeService::normalizeCountdownInput('2026-12-25 18:30:00');

        $this->assertNotNull($parsed);
        $this->assertSame('2026-12-25 18:30:00', $parsed->format('Y-m-d H:i:s'));
    }

    public function test_launch_style_and_theme_normalization(): void
    {
        $this->assertSame(LaunchModeService::STYLE_COUNTDOWN, LaunchModeService::normalizeLaunchStyle('auto'));
        $this->assertSame(LaunchModeService::STYLE_RIBBON, LaunchModeService::normalizeLaunchStyle('event'));
        $this->assertSame(LaunchModeService::THEME_NEON, LaunchModeService::normalizeTheme('neon'));
        $this->assertSame(LaunchModeService::THEME_BOLD, LaunchModeService::normalizeTheme('genz'));
        $this->assertSame(LaunchModeService::THEME_PARISH, LaunchModeService::normalizeTheme('unknown'));
    }

    public function test_splash_data_for_site_and_path_gates(): void
    {
        $site = LaunchModeService::splashData(LaunchModeService::normalizeGate([
            'scope' => LaunchModeService::SCOPE_SITE,
        ]));

        $this->assertSame('site', $site['type']);
        $this->assertSame(url('/'), $site['launchUrl']);

        $path = LaunchModeService::splashData(LaunchModeService::normalizeGate([
            'scope' => LaunchModeService::SCOPE_PATH,
            'target_path' => 'liturgy',
        ]));

        $this->assertSame('page', $path['type']);
        $this->assertSame(url('/liturgy'), $path['launchUrl']);
        $this->assertSame('Liturgy', LaunchModeService::resolvePageTitleForPath('liturgy'));
        $this->assertNotSame('', $path['pageTitle']);
    }
}
