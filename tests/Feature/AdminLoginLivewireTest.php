<?php

namespace Tests\Feature;

use App\Http\Middleware\ThrottlePublicForms;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Tests\TestCase;

class AdminLoginLivewireTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_livewire_requests_skip_public_form_rate_limiter(): void
    {
        $key = 'livewire-form:127.0.0.1';

        RateLimiter::clear($key);

        for ($attempt = 0; $attempt < 25; $attempt++) {
            RateLimiter::hit($key, 60);
        }

        $this->assertTrue(RateLimiter::tooManyAttempts($key, 20));

        $request = Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => '{"memo":{"name":"app.filament.auth.login"}}',
                    'calls' => [],
                ],
            ],
        ]);
        $request->headers->set('X-Livewire', '');
        $request->headers->set('Referer', AdminPanelConfig::url('login'));

        $middleware = new ThrottlePublicForms;
        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_livewire_errors_return_json_not_html(): void
    {
        $response = $this->withHeaders([
            'X-Livewire' => '',
            'Referer' => AdminPanelConfig::url('login'),
        ])->postJson('/livewire/update', [
            'components' => [],
        ]);

        $this->assertTrue(
            str_contains($response->headers->get('Content-Type', ''), 'json'),
            'Livewire error responses must be JSON so the UI does not hang.',
        );

        if ($response->status() === 419) {
            $response->assertJsonStructure(['message', 'reload']);
        }
    }
}
