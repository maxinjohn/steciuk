<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException;
use Tests\TestCase;

class ErrorPagesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.expose_exception_details' => false]);
    }

    public function test_404_uses_fancy_error_page(): void
    {
        $response = $this->get('/definitely-not-a-real-page-'.uniqid());

        $response->assertNotFound();
        $response->assertSee('Page not found', false);
        $response->assertSee('error-card', false);
        $response->assertSee('viewport', false);
        $response->assertSee('Proverbs 3:5', false);
    }

    public function test_403_uses_fancy_error_page(): void
    {
        $editor = User::factory()->create(['role' => UserRole::Editor]);

        $response = $this->actingAs($editor)->get(AdminPanelConfig::url('role-permissions'));

        $response->assertForbidden();
        $response->assertSee('Access denied', false);
        $response->assertSee('error-mark', false);
    }

    public function test_401_uses_fancy_error_page_for_protected_public_route(): void
    {
        Route::get('/__test/unauthorized', function () {
            abort(401);
        });

        $response = $this->get('/__test/unauthorized');

        $response->assertUnauthorized();
        $response->assertSee('Sign in required', false);
        $response->assertSee('error-btn-primary', false);
    }

    public function test_419_uses_fancy_error_page(): void
    {
        Route::get('/__test/page-expired', function () {
            abort(419);
        });

        $response = $this->get('/__test/page-expired');

        $response->assertStatus(419);
        $response->assertSee('Session expired', false);
        $response->assertSee('error-shell', false);
    }

    public function test_429_uses_fancy_error_page_with_retry_hint(): void
    {
        Route::get('/__test/rate-limited', function () {
            throw new TooManyRequestsHttpException(90, 'Slow down');
        });

        $response = $this->get('/__test/rate-limited');

        $response->assertStatus(429);
        $response->assertSee('Too many requests', false);
        $response->assertSee('minute(s)', false);
    }

    public function test_500_uses_fancy_error_page_without_leaking_details(): void
    {
        Route::get('/__test/broken', function () {
            throw new \RuntimeException('Secret internals');
        });

        $response = $this->get('/__test/broken');

        $response->assertStatus(500);
        $response->assertSee('Something went wrong', false);
        $response->assertDontSee('Secret internals', false);
        $response->assertDontSee('RuntimeException', false);
    }

    public function test_503_uses_fancy_error_page(): void
    {
        Route::get('/__test/maintenance', function () {
            abort(503);
        });

        $response = $this->get('/__test/maintenance');

        $response->assertStatus(503);
        $response->assertSee('maintenance-page', false);
        $response->assertSee('We&#039;ll be right back', false);
    }
}
