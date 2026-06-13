<?php

namespace Tests\Unit;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\AdminQuickLinks;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminQuickLinksTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_super_admin_gets_multiple_quick_link_sections(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $this->actingAs($admin);

        $sections = AdminQuickLinks::sections();

        $this->assertNotEmpty($sections);
        $this->assertArrayHasKey('group', $sections[0]);
        $this->assertArrayHasKey('items', $sections[0]);
        $this->assertNotEmpty($sections[0]['items']);
    }

    public function test_guest_gets_no_quick_link_sections(): void
    {
        $this->assertSame([], AdminQuickLinks::sections());
    }
}
