<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
use App\Enums\UserRole;
use App\Livewire\Account\FamilyMembersManager;
use App\Livewire\Account\ProfileAvatarForm;
use App\Livewire\Account\ProfilePasswordForm;
use App\Livewire\Auth\LoginForm;
use App\Livewire\Auth\RegisterForm;
use App\Models\Family;
use App\Models\User;
use App\Services\MemberRegistrationService;
use App\Services\PermissionService;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\Support\FakesUkAddressLookup;
use Tests\TestCase;

class MemberRegistrationTest extends TestCase
{
    use FakesUkAddressLookup;
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_register_page_is_available(): void
    {
        $this->get('/register')->assertOk()->assertSee('Create Your Parish Account', false);
    }

    public function test_validation_failures_do_not_trigger_registration_rate_limit(): void
    {
        RateLimiter::clear('register:127.0.0.1');

        for ($attempt = 0; $attempt < 6; $attempt++) {
            Livewire::test(RegisterForm::class)
                ->set('first_name', 'Parish')
                ->set('email', 'member@example.com')
                ->call('register')
                ->assertHasErrors(['accept_privacy']);
        }

        Livewire::test(RegisterForm::class)
            ->set('first_name', 'Parish')
            ->set('email', 'member@example.com')
            ->call('register')
            ->assertHasErrors(['accept_privacy'])
            ->assertHasNoErrors(['form']);
    }

    public function test_member_registration_is_pending_until_admin_approval(): void
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
            ->set('accept_privacy', true)
            ->set('accept_terms', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('registration.pending'));

        $user = User::query()->where('email', 'member@example.com')->firstOrFail();

        $this->assertTrue($user->isMember());
        $this->assertTrue($user->isAccountPending());
        $this->assertGuest();
    }

    public function test_duplicate_email_registration_is_blocked(): void
    {
        User::factory()->create([
            'email' => 'member@example.com',
            'account_status' => AccountStatus::Approved,
        ]);

        Livewire::test(RegisterForm::class)
            ->set('email', 'member@example.com')
            ->set('name', 'Another Person')
            ->set('password', 'SecurePass!123')
            ->set('password_confirmation', 'SecurePass!123')
            ->set('phone', '07700900123')
            ->set('date_of_birth', '1990-05-15')
            ->set('postcode', 'M1 1AE')
            ->set('address_line_1', '1 Example Street')
            ->set('city', 'Manchester')
            ->set('accept_privacy', true)
            ->set('accept_terms', true)
            ->call('register')
            ->assertHasErrors(['email']);
    }

    public function test_pending_member_cannot_sign_in(): void
    {
        User::factory()->pending()->create([
            'email' => 'member@example.com',
            'password' => 'password',
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'member@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    public function test_admin_can_approve_member_and_member_can_sign_in(): void
    {
        $member = User::factory()->pending()->create([
            'email' => 'member@example.com',
            'password' => 'password',
        ]);

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        app(MemberRegistrationService::class)->approve($member, $admin);

        Livewire::test(LoginForm::class)
            ->set('email', 'member@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('account'));
    }

    public function test_individual_registration_does_not_create_family(): void
    {
        $this->fakeUkAddressLookup();

        Livewire::test(RegisterForm::class)
            ->set('first_name', 'Parish')
            ->set('last_name', 'Member')
            ->set('email', 'solo@example.com')
            ->set('password', 'SecurePass!123')
            ->set('password_confirmation', 'SecurePass!123')
            ->set('phone', '07700900123')
            ->set('date_of_birth', '1990-05-15')
            ->set('postcode', 'M1 1AE')
            ->call('lookupPostcode')
            ->set('accept_privacy', true)
            ->set('accept_terms', true)
            ->call('register')
            ->assertHasNoErrors()
            ->assertRedirect(route('registration.pending'));

        $member = User::query()->where('email', 'solo@example.com')->firstOrFail();

        $this->assertNull($member->family_id);
        $this->assertFalse($member->isFamilyAdmin());
    }

    public function test_postcode_lookup_lists_and_autofills_address(): void
    {
        $this->fakeUkAddressLookup('M1 1AE', [
            [
                'line_1' => '1 Example Street',
                'line_2' => '',
                'town_or_city' => 'Manchester',
                'county' => 'Greater Manchester',
            ],
            [
                'line_1' => '2 Example Street',
                'line_2' => '',
                'town_or_city' => 'Manchester',
                'county' => 'Greater Manchester',
            ],
        ]);

        Livewire::test(RegisterForm::class)
            ->set('postcode', 'M1 1AE')
            ->call('lookupPostcode')
            ->assertSet('postcodeAddressOptions', fn (array $options): bool => count($options) === 2)
            ->call('selectAddress', 'osm-1')
            ->assertSet('address_line_1', '2 Example Street')
            ->assertSet('city', 'Manchester')
            ->assertSet('county', 'Greater Manchester');
    }

    public function test_approved_member_can_create_household_from_account(): void
    {
        $member = User::factory()->create([
            'email' => 'head@example.com',
            'role' => UserRole::Member,
            'account_status' => AccountStatus::Approved->value,
        ]);

        Livewire::actingAs($member)
            ->test(FamilyMembersManager::class)
            ->set('family_name', 'Thomas Family')
            ->call('createHousehold')
            ->assertHasNoErrors();

        $member->refresh();

        $this->assertTrue($member->isFamilyAdmin());
        $this->assertDatabaseHas('families', ['name' => 'Thomas Family', 'admin_user_id' => $member->id]);
    }

    public function test_family_admin_can_add_child_without_email(): void
    {
        $head = User::factory()->create([
            'email' => 'head@example.com',
            'is_family_admin' => true,
            'family_relationship' => 'head',
        ]);

        $family = Family::query()->create([
            'name' => 'Thomas Family',
            'admin_user_id' => $head->id,
        ]);

        $head->update(['family_id' => $family->id]);

        Livewire::actingAs($head)
            ->test(FamilyMembersManager::class)
            ->set('first_name', 'Child')
            ->set('last_name', 'Member')
            ->set('date_of_birth', '2015-06-01')
            ->set('relationship', 'child')
            ->set('email', '')
            ->set('household_data_consent', true)
            ->call('addMember')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('users', [
            'first_name' => 'Child',
            'last_name' => 'Member',
            'name' => 'Child Member',
            'email' => null,
            'family_id' => $family->id,
            'account_status' => AccountStatus::Pending->value,
            'family_relationship' => 'child',
        ]);
    }

    public function test_family_admin_adding_email_to_approved_child_sets_pending(): void
    {
        $head = User::factory()->create([
            'email' => 'head@example.com',
            'is_family_admin' => true,
            'family_relationship' => 'head',
        ]);

        $family = Family::query()->create([
            'name' => 'Thomas Family',
            'admin_user_id' => $head->id,
        ]);

        $head->update(['family_id' => $family->id]);

        $child = User::factory()->create([
            'name' => 'Child Member',
            'email' => null,
            'family_id' => $family->id,
            'family_relationship' => 'child',
            'account_status' => AccountStatus::Approved->value,
        ]);

        Livewire::actingAs($head)
            ->test(FamilyMembersManager::class)
            ->call('startEditingEmail', $child->id)
            ->set('editEmail', 'child@example.com')
            ->call('saveMemberEmail')
            ->assertHasNoErrors();

        $child->refresh();

        $this->assertSame('child@example.com', $child->email);
        $this->assertSame(AccountStatus::Pending, $child->accountStatus());
    }

    public function test_member_can_update_password_from_profile_form(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'OldPassword!123',
            'role' => UserRole::Member,
        ]);

        Livewire::actingAs($member)
            ->test(ProfilePasswordForm::class)
            ->set('current_password', 'OldPassword!123')
            ->set('password', 'NewPassword!456')
            ->set('password_confirmation', 'NewPassword!456')
            ->call('updatePassword')
            ->assertHasNoErrors();

        $this->assertTrue(Hash::check('NewPassword!456', $member->fresh()->password));
    }

    public function test_member_can_upload_profile_photo(): void
    {
        Storage::fake('public');

        $member = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
        ]);

        $photo = UploadedFile::fake()->image('avatar.jpg', 240, 240)->size(900);

        Livewire::actingAs($member)
            ->test(ProfileAvatarForm::class)
            ->set('photo', $photo)
            ->call('uploadPhoto')
            ->assertHasNoErrors();

        $member->refresh();

        $this->assertTrue($member->hasUploadedProfilePhoto());
        $this->assertNotNull($member->profilePhotoUrl());
    }

    public function test_member_can_remove_profile_photo(): void
    {
        Storage::fake('public');

        $member = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
        ]);

        $photo = UploadedFile::fake()->image('avatar.jpg', 240, 240)->size(900);

        $member->addMedia($photo->getRealPath())
            ->usingFileName('profile.jpg')
            ->toMediaCollection('profile_photo');

        Livewire::actingAs($member)
            ->test(ProfileAvatarForm::class)
            ->call('removePhoto')
            ->assertHasNoErrors();

        $member->refresh();

        $this->assertFalse($member->hasUploadedProfilePhoto());
    }

    public function test_user_gravatar_url_is_generated_from_email(): void
    {
        $member = User::factory()->create([
            'email' => 'Member@Example.com',
        ]);

        $expectedHash = md5('member@example.com');

        $this->assertSame(
            "https://www.gravatar.com/avatar/{$expectedHash}?s=256&d=404",
            $member->gravatarUrl()
        );
    }

    public function test_member_cannot_access_admin_panel(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'role' => UserRole::Member,
        ]);

        $this->assertFalse(app(PermissionService::class)->canAccessAdmin($member));
        $this->actingAs($member)->get(AdminPanelConfig::url())->assertForbidden();
    }

    public function test_promoted_admin_can_access_admin_panel(): void
    {
        $admin = User::factory()->create([
            'email' => 'parish-admin@example.com',
            'role' => UserRole::Admin,
        ]);

        $this->assertTrue(app(PermissionService::class)->canAccessAdmin($admin));
        $this->actingAs($admin)->get(AdminPanelConfig::url())->assertOk();
    }

    public function test_deactivated_member_cannot_sign_in(): void
    {
        $member = User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => 'SecurePass!123',
            'role' => UserRole::Member,
            'is_active' => false,
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'inactive@example.com')
            ->set('password', 'SecurePass!123')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    public function test_deactivated_family_blocks_member_sign_in(): void
    {
        $family = Family::query()->create([
            'name' => 'Inactive Family',
            'is_active' => false,
        ]);

        User::factory()->create([
            'email' => 'family-member@example.com',
            'password' => 'SecurePass!123',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'is_active' => true,
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'family-member@example.com')
            ->set('password', 'SecurePass!123')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    public function test_parish_admin_can_open_families_admin_page(): void
    {
        $admin = User::factory()->create([
            'email' => 'parish-admin@example.com',
            'role' => UserRole::Admin,
        ]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('families'))
            ->assertOk()
            ->assertSee('Families', false)
            ->assertSee('New family', false);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('families/create'))
            ->assertOk()
            ->assertSee('Family name', false);
    }

    public function test_create_household_without_name_uses_surname_default(): void
    {
        $member = User::factory()->create([
            'first_name' => 'John',
            'last_name' => 'Thomas',
            'name' => 'John Thomas',
            'email' => 'john@example.com',
            'role' => UserRole::Member,
            'account_status' => AccountStatus::Approved->value,
        ]);

        Livewire::actingAs($member)
            ->test(FamilyMembersManager::class)
            ->set('family_name', '')
            ->call('createHousehold')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('families', ['name' => 'Thomas family', 'admin_user_id' => $member->id]);
    }

    public function test_admin_can_create_family_without_name_when_linking_head(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $head = User::factory()->create([
            'name' => 'Mary Smith',
            'email' => 'mary@example.com',
            'role' => UserRole::Member,
        ]);

        $family = app(MemberRegistrationService::class)->createFamily($admin, [
            'name' => '',
        ], $head);

        $this->assertSame('Smith family', $family->name);
        $this->assertSame($family->id, $head->fresh()->family_id);
    }

    public function test_admin_can_add_new_member_to_family(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $family = app(MemberRegistrationService::class)->createFamily($admin, [
            'name' => 'Test Family',
        ]);

        app(MemberRegistrationService::class)->adminCreateFamilyMember($admin, $family, [
            'name' => 'Child Member',
            'relationship' => 'child',
            'account_status' => AccountStatus::Approved->value,
        ]);

        $this->assertDatabaseHas('users', [
            'name' => 'Child Member',
            'family_id' => $family->id,
            'account_status' => AccountStatus::Approved->value,
        ]);
    }

    public function test_admin_can_create_family_and_link_individual_registrants(): void
    {
        $admin = User::factory()->create([
            'email' => 'parish-admin@example.com',
            'role' => UserRole::Admin,
        ]);

        $husband = User::factory()->create([
            'email' => 'husband@example.com',
            'role' => UserRole::Member,
            'family_id' => null,
        ]);

        $wife = User::factory()->create([
            'email' => 'wife@example.com',
            'role' => UserRole::Member,
            'family_id' => null,
        ]);

        $service = app(MemberRegistrationService::class);

        $family = $service->createFamily($admin, [
            'name' => 'Thomas Family',
        ], $husband);

        $service->assignUserToFamily(
            $admin,
            $wife,
            $family,
            FamilyRelationship::Spouse,
            forceMove: true,
        );

        $husband->refresh();
        $wife->refresh();
        $family->refresh();

        $this->assertSame($family->id, $husband->family_id);
        $this->assertSame($family->id, $wife->family_id);
        $this->assertTrue($husband->isFamilyAdmin());
        $this->assertSame('head', $husband->family_relationship);
        $this->assertSame('spouse', $wife->family_relationship);
        $this->assertSame($husband->id, $family->admin_user_id);
    }

    public function test_editor_as_family_head_can_manage_household_on_portal(): void
    {
        $family = Family::query()->create(['name' => 'Parish Family']);

        $editor = User::factory()->create([
            'email' => 'editor-head@example.com',
            'role' => UserRole::Editor,
            'family_id' => $family->id,
            'family_relationship' => 'head',
            'is_family_admin' => true,
        ]);

        $family->update(['admin_user_id' => $editor->id]);

        $this->assertTrue($editor->canBelongToHousehold());
        $this->assertTrue($editor->canManageHouseholdOnPortal());

        Livewire::actingAs($editor)
            ->test(FamilyMembersManager::class)
            ->assertSee('Add household member', false);
    }

    public function test_deactivated_family_blocks_editor_admin_panel_access(): void
    {
        $family = Family::query()->create([
            'name' => 'Inactive Parish Family',
            'is_active' => false,
        ]);

        $editor = User::factory()->create([
            'email' => 'editor@example.com',
            'role' => UserRole::Editor,
            'family_id' => $family->id,
            'is_family_admin' => true,
        ]);

        $this->assertFalse($editor->canAccessPanel(filament()->getCurrentOrDefaultPanel()));
    }

    public function test_admin_can_link_editor_to_family_as_head(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $editor = User::factory()->create([
            'email' => 'editor@example.com',
            'role' => UserRole::Editor,
            'family_id' => null,
        ]);

        $family = app(MemberRegistrationService::class)->createFamily($admin, [
            'name' => 'Smith Family',
        ], $editor);

        $editor->refresh();

        $this->assertSame($family->id, $editor->family_id);
        $this->assertTrue($editor->isFamilyAdmin());
        $this->assertSame('head', $editor->family_relationship);
    }

    public function test_admin_can_move_member_between_families(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $familyA = Family::query()->create(['name' => 'Family A']);
        $familyB = Family::query()->create(['name' => 'Family B']);

        $member = User::factory()->create([
            'role' => UserRole::Member,
            'family_id' => $familyA->id,
            'family_relationship' => 'other',
        ]);

        app(MemberRegistrationService::class)->assignUserToFamily(
            $admin,
            $member,
            $familyB,
            FamilyRelationship::Spouse,
            forceMove: true,
        );

        $member->refresh();

        $this->assertSame($familyB->id, $member->family_id);
        $this->assertSame('spouse', $member->family_relationship);
    }

    public function test_approved_member_can_open_modern_account_page(): void
    {
        $member = User::factory()->create([
            'email' => 'member@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
        ]);

        $this->actingAs($member)
            ->get(route('account'))
            ->assertOk()
            ->assertSee('Member portal', false)
            ->assertSee('Overview', false)
            ->assertSee('Contact', false)
            ->assertSee('Password', false)
            ->assertDontSee('Open admin panel', false);
    }

    public function test_household_member_cannot_register_with_existing_email(): void
    {
        $family = Family::query()->create(['name' => 'Test-Family']);

        User::factory()->create([
            'email' => 'jeena@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'spouse',
            'is_family_admin' => false,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $this->fakeUkAddressLookup();

        Livewire::test(RegisterForm::class)
            ->set('first_name', 'Jeena')
            ->set('last_name', 'Joseph')
            ->set('email', 'jeena@example.com')
            ->set('password', 'SecurePass!123')
            ->set('password_confirmation', 'SecurePass!123')
            ->set('phone', '07700900123')
            ->set('date_of_birth', '1990-01-01')
            ->set('postcode', 'M1 1AE')
            ->set('address_line_1', '1 Example Street')
            ->set('city', 'Manchester')
            ->set('accept_privacy', true)
            ->set('accept_terms', true)
            ->call('register')
            ->assertHasErrors(['email'])
            ->assertSee('Test-Family', false);
    }

    public function test_household_member_cannot_sign_in(): void
    {
        $family = Family::query()->create(['name' => 'Test-Family']);

        User::factory()->create([
            'email' => 'jeena@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'spouse',
            'is_family_admin' => false,
            'account_status' => AccountStatus::Approved->value,
        ]);

        Livewire::test(LoginForm::class)
            ->set('email', 'jeena@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email'])
            ->assertSee('Test-Family', false);
    }

    public function test_family_admin_can_sign_in(): void
    {
        $family = Family::query()->create(['name' => 'Test-Family']);

        $head = User::factory()->create([
            'email' => 'head@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'head',
            'is_family_admin' => true,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $family->update(['admin_user_id' => $head->id]);

        Livewire::test(LoginForm::class)
            ->set('email', 'head@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('account'));
    }

    public function test_admin_can_change_family_admin(): void
    {
        $admin = User::factory()->create(['role' => UserRole::Admin]);
        $family = Family::query()->create(['name' => 'Test-Family']);

        $head = User::factory()->create([
            'email' => 'head@example.com',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'head',
            'is_family_admin' => true,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $spouse = User::factory()->create([
            'email' => 'spouse@example.com',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'spouse',
            'is_family_admin' => false,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $family->update(['admin_user_id' => $head->id]);

        app(MemberRegistrationService::class)->setFamilyAdmin($admin, $family, $spouse);

        $head->refresh();
        $spouse->refresh();
        $family->refresh();

        $this->assertFalse($head->isFamilyAdmin());
        $this->assertSame('spouse', $head->family_relationship);
        $this->assertTrue($spouse->isFamilyAdmin());
        $this->assertSame('head', $spouse->family_relationship);
        $this->assertSame($spouse->id, $family->admin_user_id);
    }

    public function test_sync_family_admin_state_repairs_stale_family_pointer(): void
    {
        $family = Family::query()->create(['name' => 'Test-Family']);

        $head = User::factory()->create([
            'email' => 'head@example.com',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'head',
            'is_family_admin' => true,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $spouse = User::factory()->create([
            'email' => 'spouse@example.com',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'spouse',
            'is_family_admin' => false,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $family->update(['admin_user_id' => $spouse->id]);

        app(MemberRegistrationService::class)->syncFamilyAdminState($family->fresh());

        $this->assertSame($head->id, $family->fresh()->admin_user_id);
        $this->assertTrue($head->fresh()->isFamilyAdmin());
        $this->assertFalse($spouse->fresh()->isFamilyAdmin());
    }

    public function test_promoted_family_admin_can_sign_in_and_demoted_admin_cannot(): void
    {
        $family = Family::query()->create(['name' => 'Test-Family']);
        $admin = User::factory()->create(['role' => UserRole::Admin]);

        $head = User::factory()->create([
            'email' => 'head@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'head',
            'is_family_admin' => true,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $spouse = User::factory()->create([
            'email' => 'spouse@example.com',
            'password' => 'password',
            'role' => UserRole::Member,
            'family_id' => $family->id,
            'family_relationship' => 'spouse',
            'is_family_admin' => false,
            'account_status' => AccountStatus::Approved->value,
        ]);

        $family->update(['admin_user_id' => $head->id]);

        Livewire::test(LoginForm::class)
            ->set('email', 'head@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors();

        auth()->logout();

        app(MemberRegistrationService::class)->setFamilyAdmin($admin, $family, $spouse);

        Livewire::test(LoginForm::class)
            ->set('email', 'head@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasErrors(['email']);

        Livewire::test(LoginForm::class)
            ->set('email', 'spouse@example.com')
            ->set('password', 'password')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('account'));
    }
}
