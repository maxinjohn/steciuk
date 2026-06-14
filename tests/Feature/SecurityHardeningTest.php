<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Forms\ContactForm;
use App\Models\Page;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\CustomAssetSanitizer;
use App\Support\SafeUrl;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class SecurityHardeningTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_url_blocks_javascript_scheme(): void
    {
        $this->assertFalse(SafeUrl::isSafe('javascript:alert(1)'));
        $this->assertSame('#', SafeUrl::forHref('javascript:alert(1)'));
    }

    public function test_safe_url_allows_relative_paths(): void
    {
        $this->assertTrue(SafeUrl::isSafe('/contact'));
        $this->assertSame('/contact', SafeUrl::forHref('/contact'));
    }

    public function test_custom_css_strips_script_tags_and_imports(): void
    {
        $css = CustomAssetSanitizer::css('<script>alert(1)</script>@import url("evil.css"); body{color:red}');

        $this->assertStringNotContainsString('script', strtolower($css ?? ''));
        $this->assertStringNotContainsString('@import', strtolower($css ?? ''));
        $this->assertStringContainsString('body{color:red}', $css ?? '');
    }

    public function test_custom_js_is_stripped_by_default(): void
    {
        config(['security.allow_page_custom_js' => false]);

        $this->assertNull(CustomAssetSanitizer::js('alert("xss")'));
    }

    public function test_page_saves_sanitized_custom_css(): void
    {
        $page = Page::factory()->create([
            'custom_css' => '@import url("x"); body{color:blue}',
        ]);

        $this->assertStringNotContainsString('@import', strtolower($page->custom_css ?? ''));
        $this->assertStringContainsString('body{color:blue}', $page->custom_css ?? '');
    }

    public function test_public_pages_include_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('X-Frame-Options', 'SAMEORIGIN');
        $response->assertHeader('Content-Security-Policy');
        $csp = (string) $response->headers->get('Content-Security-Policy');

        $this->assertStringContainsString('https://static.cloudflareinsights.com', $csp);
        $this->assertStringContainsString('https://fonts.bunny.net', $csp);
        $this->assertStringContainsString('https://challenges.cloudflare.com', $csp);
        $this->assertStringContainsString('trusted-types * goog#html', $csp);
    }

    public function test_admin_pages_allow_bunny_fonts_in_csp(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($admin)->get(AdminPanelConfig::url('church-settings'));

        $response->assertOk();
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('https://fonts.bunny.net', $csp);
        $this->assertStringContainsString('https://static.cloudflareinsights.com', $csp);
        $this->assertStringContainsString('https://challenges.cloudflare.com', $csp);
    }

    public function test_admin_login_csp_allows_turnstile(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $response = $this->get(AdminPanelConfig::url('login'));

        $response->assertOk();
        $csp = (string) $response->headers->get('Content-Security-Policy');
        $this->assertStringContainsString('https://challenges.cloudflare.com', $csp);
        $this->assertStringContainsString('trusted-types * goog#html', $csp);
    }

    public function test_honeypot_submission_is_logged(): void
    {
        Livewire::test(ContactForm::class)
            ->set('website', 'http://bot.test')
            ->set('name', 'Bot')
            ->set('email', 'bot@example.com')
            ->set('message', 'Spam')
            ->call('submit')
            ->assertSet('submitted', false);

        $this->assertDatabaseCount('security_audit_logs', 1);
        $this->assertDatabaseHas('security_audit_logs', [
            'action' => 'honeypot_triggered',
        ]);
    }
}
