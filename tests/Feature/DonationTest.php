<?php

namespace Tests\Feature;

use App\Enums\DonationStatus;
use App\Enums\UserRole;
use App\Livewire\Account\DonationManager;
use App\Livewire\Account\FamilyMembersManager;
use App\Models\Donation;
use App\Models\Family;
use App\Models\User;
use App\Services\DonationService;
use App\Support\AdminPanelConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class DonationTest extends TestCase
{
    use RefreshDatabase;

    public function test_member_can_submit_donation_for_verification(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
        ]);

        Livewire::actingAs($member)
            ->test(DonationManager::class)
            ->set('amount', '50.00')
            ->set('method', 'bank_transfer')
            ->set('donated_on', now()->format('Y-m-d'))
            ->set('reference', 'REF123')
            ->set('member_note', 'Monthly offering')
            ->set('confirm_accuracy', true)
            ->call('submit')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('donations', [
            'user_id' => $member->id,
            'amount' => 50.00,
            'method' => 'bank_transfer',
            'status' => DonationStatus::Pending->value,
            'reference' => 'REF123',
        ]);
    }

    public function test_admin_can_approve_donation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $donation = Donation::query()->create([
            'user_id' => $member->id,
            'family_id' => $member->family_id,
            'amount' => 25.00,
            'currency' => 'GBP',
            'method' => 'cash',
            'status' => DonationStatus::Pending->value,
            'donated_on' => now()->toDateString(),
        ]);

        app(DonationService::class)->approve($donation, $admin, 'Verified in parish ledger');

        $donation->refresh();

        $this->assertSame(DonationStatus::Approved, $donation->statusEnum());
        $this->assertSame(25.0, app(DonationService::class)->approvedTotalForUser($member));
    }

    public function test_admin_can_record_manual_donation(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $member = User::factory()->create(['role' => UserRole::Member]);

        $donation = app(DonationService::class)->recordManual($admin, [
            'user_id' => $member->id,
            'amount' => 100,
            'method' => 'bank_transfer',
            'donated_on' => now()->toDateString(),
            'status' => DonationStatus::Approved->value,
        ]);

        $this->assertSame(DonationStatus::Approved, $donation->statusEnum());
        $this->assertSame($member->id, $donation->user_id);
        $this->assertSame(100.0, (float) $donation->amount);
    }

    public function test_member_can_export_personal_giving_pdf(): void
    {
        $member = User::factory()->create(['role' => UserRole::Member]);

        Donation::query()->create([
            'user_id' => $member->id,
            'amount' => 40.00,
            'currency' => 'GBP',
            'method' => 'cash',
            'status' => DonationStatus::Approved->value,
            'donated_on' => now()->toDateString(),
        ]);

        $response = $this->actingAs($member)->get(route('account.giving.export', [
            'scope' => 'personal',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }

    public function test_admin_can_open_donations_create_page(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('donations/create'))
            ->assertOk()
            ->assertSee('Record donation', false)
            ->assertSee('Donor', false);
    }

    public function test_family_head_can_remove_pending_child_without_email(): void
    {
        $family = Family::query()->create(['name' => 'Test Family']);

        $head = User::factory()->create([
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'is_family_admin' => true,
            'family_relationship' => 'head',
        ]);

        $family->update(['admin_user_id' => $head->id]);

        Livewire::actingAs($head)
            ->test(FamilyMembersManager::class)
            ->set('first_name', 'Child')
            ->set('last_name', 'Without Email')
            ->set('date_of_birth', '2015-06-01')
            ->set('relationship', 'child')
            ->set('household_data_consent', true)
            ->call('addMember')
            ->assertHasNoErrors();

        $child = User::query()->where('first_name', 'Child')->where('last_name', 'Without Email')->firstOrFail();

        Livewire::actingAs($head)
            ->test(FamilyMembersManager::class)
            ->call('removeMember', $child->id)
            ->assertHasNoErrors();

        $this->assertDatabaseMissing('users', ['id' => $child->id]);
    }

    public function test_household_giving_totals_use_family_id_snapshot(): void
    {
        $familyA = Family::query()->create(['name' => 'Family A']);
        $familyB = Family::query()->create(['name' => 'Family B']);

        $member = User::factory()->create([
            'role' => UserRole::Member,
            'family_id' => $familyA->id,
        ]);

        Donation::query()->create([
            'user_id' => $member->id,
            'family_id' => $familyA->id,
            'amount' => 50.00,
            'currency' => 'GBP',
            'method' => 'cash',
            'status' => DonationStatus::Approved->value,
            'donated_on' => now()->toDateString(),
        ]);

        $member->update(['family_id' => $familyB->id]);

        $service = app(DonationService::class);

        $this->assertSame(50.0, $service->approvedTotalForFamily($familyA->id));
        $this->assertSame(0.0, $service->approvedTotalForFamily($familyB->id));
    }

    public function test_household_member_sees_shared_giving_summary_and_export(): void
    {
        $family = Family::query()->create(['name' => 'Shared Family']);

        $head = User::factory()->create([
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'is_family_admin' => true,
            'family_relationship' => 'head',
        ]);

        $family->update(['admin_user_id' => $head->id]);

        $spouse = User::factory()->create([
            'email' => 'spouse@example.com',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'is_family_admin' => false,
            'family_relationship' => 'spouse',
        ]);

        Donation::query()->create([
            'user_id' => $head->id,
            'family_id' => $family->id,
            'amount' => 75.00,
            'currency' => 'GBP',
            'method' => 'bank_transfer',
            'status' => DonationStatus::Approved->value,
            'donated_on' => now()->toDateString(),
        ]);

        Livewire::actingAs($spouse)
            ->test(DonationManager::class)
            ->assertSee('Household approved giving')
            ->assertSee('£75.00')
            ->assertSee($head->displayFullName());

        $response = $this->actingAs($spouse)->get(route('account.giving.export', [
            'scope' => 'household',
            'from' => now()->startOfMonth()->toDateString(),
            'to' => now()->toDateString(),
        ]));

        $response->assertOk();
        $response->assertHeader('content-type', 'application/pdf');
    }
}
