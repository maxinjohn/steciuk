<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Http\Middleware\BlockSuspiciousRequests;
use App\Http\Middleware\CheckSiteMaintenance;
use App\Models\User;
use App\Services\MaintenanceModeService;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class AdminReliabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['security.block_suspicious_requests' => true]);
        $this->clearPublicLivewireRateLimiter();
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_livewire_requests_continue_during_maintenance_mode(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($user);

        MaintenanceModeService::enable();

        foreach (['app.filament.pages.dashboard', 'filament.livewire.sidebar'] as $componentName) {
            $request = Request::create('/livewire/update', 'POST', [
                'components' => [
                    [
                        'snapshot' => json_encode(['memo' => ['name' => $componentName]]),
                        'calls' => [],
                    ],
                ],
            ]);
            $request->headers->set('X-Livewire', '');

            $middleware = new CheckSiteMaintenance;
            $response = $middleware->handle($request, fn () => response('ok'));

            $this->assertSame('ok', $response->getContent(), "Failed for {$componentName}");
        }
    }

    public function test_public_livewire_requests_get_json_503_during_maintenance_mode(): void
    {
        MaintenanceModeService::enable();

        $token = 'test-csrf-token';

        $response = $this->withSession(['_token' => $token])
            ->withHeaders(['X-Livewire' => ''])
            ->post('/livewire/update', [
                '_token' => $token,
                'components' => [],
            ]);

        $response->assertStatus(503);
        $response->assertJson([
            'message' => 'Service unavailable.',
            'reload' => true,
        ]);
    }

    public function test_admin_livewire_requests_skip_suspicious_request_scan(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($user);

        $request = Request::create('/livewire/update', 'POST', [
            'components' => [
                [
                    'snapshot' => json_encode(['memo' => ['name' => 'app.filament.resources.pages.edit-record']]),
                    'calls' => [
                        [
                            'method' => 'save',
                            'params' => [],
                        ],
                    ],
                ],
            ],
            'content' => '<script>alert(1)</script>',
        ]);
        $request->headers->set('X-Livewire', '');

        $middleware = new BlockSuspiciousRequests;
        $response = $middleware->handle($request, fn () => response('ok'));

        $this->assertSame('ok', $response->getContent());
    }

    public function test_admin_livewire_errors_include_reload_for_server_failures(): void
    {
        $user = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $response = $this->actingAs($user)
            ->withHeaders([
                'X-Livewire' => '',
                'Referer' => AdminPanelConfig::url(),
            ])
            ->post('/livewire/update', [
                '_token' => csrf_token(),
                'components' => [
                    [
                        'snapshot' => json_encode(['memo' => ['name' => 'app.filament.pages.dashboard']]),
                        'calls' => [
                            [
                                'method' => '__nonexistent_method__',
                                'params' => [],
                            ],
                        ],
                    ],
                ],
            ]);

        if ($response->status() >= 500) {
            $response->assertJson(['reload' => true]);
        } else {
            $this->assertNotSame(503, $response->status());
        }
    }
}
