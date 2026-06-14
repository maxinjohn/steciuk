<?php

namespace Tests\Unit;

use App\Models\Setting;
use App\Support\ContextScripture;
use App\Support\FutureSiteConfig;
use App\Support\PublicUiContent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class PublicUiContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_spark_strip_uses_admin_values(): void
    {
        Setting::set('public_ui_spark_strip', json_encode([
            'kicker' => 'Rooted in Christ',
            'items' => [
                ['label' => 'Hope', 'ref' => 'Rom 15:13', 'href' => '/our-church'],
            ],
        ]), 'public_ui');

        $strip = PublicUiContent::sparkStrip();

        $this->assertSame('Rooted in Christ', $strip['kicker']);
        $this->assertSame('Hope', $strip['items'][0]['label']);
    }

    public function test_experience_toggles_disable_future_layer(): void
    {
        Setting::set('public_ui_experience', json_encode([
            'enabled' => false,
            'speculation_rules' => true,
            'reading_progress' => true,
            'heavenly_atmosphere' => false,
        ]), 'public_ui');

        $this->assertFalse(FutureSiteConfig::enabled());
        $this->assertFalse(FutureSiteConfig::speculationEnabled());
        $this->assertFalse(PublicUiContent::heavenlyAtmosphereEnabled());
    }

    public function test_context_scripture_resolves_cms_slug(): void
    {
        Setting::set('public_ui_context_scripture', json_encode([
            [
                'route' => 'pages.show',
                'slug' => 'contact',
                'kicker' => 'Near',
                'text' => 'Custom contact verse.',
                'ref' => 'Test 1:1',
            ],
        ]), 'public_ui');

        $request = Request::create('/contact', 'GET');
        $route = new \Illuminate\Routing\Route('GET', '/{slug}', []);
        $route->name('pages.show');
        $route->bind($request);
        $route->setParameter('slug', 'contact');
        $request->setRouteResolver(fn () => $route);

        $resolved = PublicUiContent::contextScriptureForRequest($request);

        $this->assertNotNull($resolved);
        $this->assertSame('Custom contact verse.', $resolved['text']);
    }

    public function test_spark_strip_rejects_unsafe_href_schemes(): void
    {
        Setting::set('public_ui_spark_strip', json_encode([
            'kicker' => 'Test',
            'items' => [
                ['label' => 'Bad', 'ref' => 'X 1:1', 'href' => 'javascript:alert(1)'],
                ['label' => 'Good', 'ref' => 'Y 2:2', 'href' => '/contact'],
            ],
        ]), 'public_ui');

        $strip = PublicUiContent::sparkStrip();

        $this->assertSame(url('/our-church'), $strip['items'][0]['href']);
        $this->assertSame('Bad', $strip['items'][0]['label']);
        $this->assertSame('/contact', $strip['items'][1]['href']);
    }
}
