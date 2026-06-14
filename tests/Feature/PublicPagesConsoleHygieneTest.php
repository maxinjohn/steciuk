<?php

namespace Tests\Feature;

use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicPagesConsoleHygieneTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @return list<string>
     */
    private function publicPaths(): array
    {
        return [
            '/',
            '/prayer-request',
            '/service-times',
            '/contact',
            '/events',
            '/news',
            '/ministries',
            '/give',
            '/sermons',
            '/gallery',
            '/our-church',
        ];
    }

    public function test_public_pages_avoid_speculation_and_vite_preload_tags(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        foreach ($this->publicPaths() as $path) {
            $response = $this->get($path);

            $response->assertOk();
            $response->assertDontSee('type="speculationrules"', false);
            $response->assertDontSee('rel="preload"', false);
            $response->assertDontSee('rel="modulepreload"', false);

            $csp = (string) $response->headers->get('Content-Security-Policy');
            $this->assertStringContainsString('trusted-types * goog#html', $csp, "Missing Trusted Types allowance on {$path}");
        }
    }
}
