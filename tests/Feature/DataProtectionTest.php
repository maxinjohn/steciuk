<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Livewire\Account\ProfilePrivacyForm;
use App\Livewire\Auth\RegisterForm;
use App\Models\User;
use App\Services\DataProtectionService;
use App\Support\GdprConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\Support\FakesUkAddressLookup;
use Tests\Support\RegistersTestMembers;
use Tests\TestCase;

class DataProtectionTest extends TestCase
{
    use FakesUkAddressLookup;
    use RefreshDatabase;
    use RegistersTestMembers;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_registration_requires_privacy_and_terms_consent(): void
    {
        $this->fakeUkAddressLookup();

        Livewire::test(RegisterForm::class)
            ->set('first_name', 'Parish')
            ->set('last_name', 'Member')
            ->set('email', 'member@example.com')
            ->set('password', 'SecurePass!123')
            ->set('password_confirmation', 'SecurePass!123')
            ->set('phone', '07700900123')
            ->set('date_of_birth', '1990-05-15')
            ->set('postcode', 'M1 1AE')
            ->call('lookupPostcode')
            ->call('register')
            ->assertHasErrors(['accept_privacy', 'accept_terms']);
    }

    public function test_registration_records_consent_timestamps(): void
    {
        $this->fakeUkAddressLookup();

        Livewire::test(RegisterForm::class)
            ->tap(fn ($component) => $this->withRequiredRegistrationFields($component, ['email' => 'consent@example.com']))
            ->call('lookupPostcode')
            ->set('accept_privacy', true)
            ->set('accept_terms', true)
            ->set('marketing_consent', true)
            ->call('register')
            ->assertHasNoErrors();

        $user = User::query()->where('email', 'consent@example.com')->firstOrFail();

        $this->assertNotNull($user->privacy_policy_accepted_at);
        $this->assertSame(GdprConfig::privacyPolicyVersion(), $user->privacy_policy_version);
        $this->assertNotNull($user->terms_accepted_at);
        $this->assertTrue($user->marketing_consent);
        $this->assertNotNull($user->marketing_consent_at);
    }

    public function test_member_can_export_personal_data(): void
    {
        $member = User::factory()->create([
            'email' => 'export@example.com',
            'role' => UserRole::Member,
        ]);

        $payload = app(DataProtectionService::class)->exportPersonalData($member);

        $this->assertSame('export@example.com', $payload['profile']['email']);
        $this->assertSame(GdprConfig::privacyPolicyVersion(), $payload['privacy_policy_version']);
    }

    public function test_member_can_request_erasure_from_account(): void
    {
        $member = User::factory()->create([
            'email' => 'erase@example.com',
            'role' => UserRole::Member,
        ]);

        Livewire::actingAs($member)
            ->test(ProfilePrivacyForm::class)
            ->call('requestErasure')
            ->assertHasNoErrors();

        $member->refresh();

        $this->assertTrue($member->hasErasureRequest());
    }

    public function test_admin_can_anonymize_member_account(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'name' => 'Member Name',
            'role' => UserRole::Member,
            'phone' => '07700900123',
        ]);

        app(DataProtectionService::class)->anonymizeUser($member, $admin);

        $member->refresh();

        $this->assertTrue($member->isAnonymized());
        $this->assertFalse($member->isActive());
        $this->assertStringContainsString('deleted-user-', $member->email);
        $this->assertNull($member->phone);
        $this->assertNull($member->pronouns);
        $this->assertSame('Deleted', $member->first_name);
        $this->assertSame('Account', $member->last_name);
    }
}
