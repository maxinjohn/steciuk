<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Families\Pages\ListFamilies;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\Family;
use App\Models\User;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminTableSearchTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_users_list_search_finds_member_by_email(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $member = User::factory()->create([
            'first_name' => 'Searchable',
            'last_name' => 'Member',
            'email' => 'find-me@example.com',
            'role' => UserRole::Member->value,
        ]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->set('tableSearch', 'find-me@example.com')
            ->assertCanSeeTableRecords([$member])
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_users_list_search_finds_member_by_family_name(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $familyAdmin = User::factory()->create([
            'first_name' => 'Family',
            'last_name' => 'Admin',
            'email' => 'family-admin@example.com',
            'role' => UserRole::Member->value,
        ]);

        $family = Family::query()->create([
            'name' => 'Thadathil',
            'admin_user_id' => $familyAdmin->id,
            'preferred_worship_location' => 'Manchester',
        ]);

        $member = User::factory()->create([
            'first_name' => 'Linked',
            'last_name' => 'Person',
            'email' => 'linked@example.com',
            'family_id' => $family->id,
            'role' => UserRole::Member->value,
        ]);

        Livewire::actingAs($admin)
            ->test(ListUsers::class)
            ->set('tableSearch', 'Thadathil')
            ->assertCanSeeTableRecords([$member, $familyAdmin])
            ->assertCanNotSeeTableRecords([$admin]);
    }

    public function test_families_list_search_finds_household_by_primary_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $familyAdmin = User::factory()->create([
            'first_name' => 'Primary',
            'last_name' => 'Contact',
            'email' => 'primary-contact@example.com',
            'role' => UserRole::Member->value,
        ]);

        $family = Family::query()->create([
            'name' => 'Joseph',
            'admin_user_id' => $familyAdmin->id,
            'preferred_worship_location' => 'London',
        ]);

        $otherFamily = Family::query()->create([
            'name' => 'Other-Household',
            'admin_user_id' => $admin->id,
        ]);

        Livewire::actingAs($admin)
            ->test(ListFamilies::class)
            ->set('tableSearch', 'primary-contact@example.com')
            ->assertCanSeeTableRecords([$family])
            ->assertCanNotSeeTableRecords([$otherFamily]);
    }

    public function test_families_list_search_finds_household_by_location(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $manchesterFamily = Family::query()->create([
            'name' => 'North',
            'admin_user_id' => $admin->id,
            'preferred_worship_location' => 'Manchester',
        ]);

        $londonFamily = Family::query()->create([
            'name' => 'South',
            'admin_user_id' => $admin->id,
            'preferred_worship_location' => 'London',
        ]);

        Livewire::actingAs($admin)
            ->test(ListFamilies::class)
            ->set('tableSearch', 'Manchester')
            ->assertCanSeeTableRecords([$manchesterFamily])
            ->assertCanNotSeeTableRecords([$londonFamily]);
    }
}
