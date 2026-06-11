<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Models\Family;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminUserFormTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_create_user_persists_pronouns_and_gender(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(CreateUser::class)
            ->fillForm([
                'first_name' => 'Test',
                'last_name' => 'Member',
                'email' => 'test.member@example.com',
                'password' => 'Password123!',
                'role' => UserRole::Member->value,
                'pronouns' => 'he/him',
                'gender' => 'male',
                'phone' => '07700900123',
                'preferred_worship_location' => 'Manchester',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('users', [
            'email' => 'test.member@example.com',
            'pronouns' => 'he/him',
            'gender' => 'male',
            'phone' => '07700900123',
            'preferred_worship_location' => 'Manchester',
        ]);
    }

    public function test_linked_family_member_can_sign_in(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::Member,
            'account_status' => 'approved',
        ]);

        $family = Family::query()->create([
            'name' => 'Test Family',
            'admin_user_id' => $admin->id,
            'is_active' => true,
        ]);

        $admin->update([
            'family_id' => $family->id,
            'is_family_admin' => true,
            'family_relationship' => 'head',
        ]);

        $spouse = User::factory()->create([
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'is_family_admin' => false,
            'family_relationship' => 'spouse',
            'email' => 'spouse@example.com',
            'password' => bcrypt('Password123!'),
            'account_status' => 'approved',
        ]);

        $this->assertTrue($spouse->canSignInToMemberPortal());

        Livewire::test(\App\Livewire\Auth\LoginForm::class)
            ->set('email', 'spouse@example.com')
            ->set('password', 'Password123!')
            ->call('login')
            ->assertRedirect(route('account'));
    }

    public function test_activity_log_is_super_admin_only(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $super = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('security-audit-logs'))
            ->assertForbidden();

        $this->actingAs($super)
            ->get(AdminPanelConfig::url('security-audit-logs'))
            ->assertOk();
    }
}
