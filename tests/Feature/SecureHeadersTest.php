<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SecureHeadersTest extends TestCase
{
    use RefreshDatabase;
    public function test_login_page_is_not_publicly_cached(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
        $this->assertNoStoreCacheControl($response->headers->get('Cache-Control'));
    }

    public function test_register_page_is_not_publicly_cached(): void
    {
        $response = $this->get(route('register'));

        $response->assertOk();
        $this->assertNoStoreCacheControl($response->headers->get('Cache-Control'));
    }

    public function test_account_redirect_is_not_publicly_cached(): void
    {
        $response = $this->get(route('account'));

        $response->assertRedirect(route('login'));
        $this->assertNoStoreCacheControl($response->headers->get('Cache-Control'));
    }

    public function test_authenticated_account_page_is_not_publicly_cached(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('account'));

        $response->assertOk();
        $this->assertNoStoreCacheControl($response->headers->get('Cache-Control'));
    }

    public function test_home_page_allows_short_public_cache(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
        $cacheControl = (string) $response->headers->get('Cache-Control');
        $this->assertStringContainsString('public', $cacheControl);
        $this->assertStringContainsString('max-age=600', $cacheControl);
    }

    public function test_service_worker_skips_member_portal_paths(): void
    {
        $response = $this->get(route('sw'));

        $response->assertOk();
        $body = $response->getContent();

        $this->assertStringContainsString('MEMBER_BYPASS_PREFIXES', $body);
        $this->assertStringContainsString('/login', $body);
        $this->assertStringContainsString('/account', $body);
        $this->assertStringContainsString('shouldBypassServiceWorker', $body);
    }

    private function assertNoStoreCacheControl(?string $cacheControl): void
    {
        $this->assertNotNull($cacheControl);
        $this->assertStringContainsString('no-store', $cacheControl);
        $this->assertStringContainsString('no-cache', $cacheControl);
        $this->assertStringContainsString('must-revalidate', $cacheControl);
    }
}
