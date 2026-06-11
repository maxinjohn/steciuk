<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Filament\Support\PanelMemberOptions;
use App\Models\Designation;
use App\Models\Panel;
use App\Models\Role;
use App\Models\User;
use App\Services\DonationReportService;
use App\Services\VicarVerificationService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VicarDesignationsAndPanelsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_vicar_role_is_seeded_and_name_is_locked(): void
    {
        $vicarRole = Role::query()->where('slug', UserRole::Vicar->value)->firstOrFail();

        $this->assertSame('Vicar', $vicarRole->name);
        $this->assertTrue($vicarRole->isNameLocked());
    }

    public function test_default_designations_and_panels_are_seeded(): void
    {
        $this->assertDatabaseHas('designations', ['slug' => 'vicar', 'name' => 'Vicar']);
        $this->assertDatabaseHas('designations', ['slug' => 'treasurer', 'name' => 'Treasurer']);
        $this->assertDatabaseHas('panels', ['slug' => 'parish-committee', 'name' => 'Parish Committee']);
        $this->assertDatabaseHas('panels', ['slug' => 'choir-members', 'name' => 'Choir Members']);
    }

    public function test_user_can_have_designation_and_panel_membership(): void
    {
        $designation = Designation::query()->where('slug', 'treasurer')->firstOrFail();
        $panel = Panel::query()->where('slug', 'parish-committee')->firstOrFail();
        $user = User::factory()->create([
            'role' => UserRole::Member->value,
            'designation_id' => $designation->id,
        ]);

        $panel->members()->attach($user->id, ['sort_order' => 1]);

        $user->refresh()->load(['designation', 'panels']);

        $this->assertSame('Treasurer', $user->designationLabel());
        $this->assertTrue($user->panels->contains($panel));
    }

    public function test_vicar_signature_is_included_in_giving_pdf_report(): void
    {
        Storage::fake('public');

        $designation = Designation::query()->where('slug', 'vicar')->firstOrFail();
        $vicar = User::factory()->create([
            'role' => UserRole::Vicar->value,
            'designation_id' => $designation->id,
        ]);

        $vicar->addMedia(UploadedFile::fake()->image('signature.png'))
            ->toMediaCollection('signature');

        $verification = app(VicarVerificationService::class)->pdfVerificationBlock();

        $this->assertNotNull($verification);
        $this->assertSame($vicar->displayFullName(), $verification['name']);
        $this->assertSame('Vicar', $verification['title']);
        $this->assertNotNull($verification['signature_data_uri']);

        $report = app(DonationReportService::class)->buildReport(
            scope: \App\Support\DonationReportScope::Personal,
            from: now()->startOfMonth(),
            to: now()->endOfMonth(),
            periodLabel: now()->format('F Y'),
            member: $vicar,
        );

        $this->assertNotNull($report['verification']);
        $this->assertStringContainsString('Verified by:', view('reports.donation-statement', $report)->render());
    }

    public function test_admin_users_designations_and_panels_screens_are_available(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin->value]);

        $this->actingAs($admin)->get(AdminPanelConfig::url('users'))->assertOk();
        $this->actingAs($admin)->get(AdminPanelConfig::url('designations'))->assertOk();
        $this->actingAs($admin)->get(AdminPanelConfig::url('panels'))->assertOk();
        $this->actingAs($admin)->get(AdminPanelConfig::url('roles'))->assertSee('Vicar', false);
    }

    public function test_panel_member_picker_lists_active_users_not_already_on_panel(): void
    {
        $admin = User::factory()->create([
            'role' => UserRole::SuperAdmin->value,
            'email' => 'panel-picker-admin@steciuk.org',
        ]);
        $member = User::factory()->create([
            'role' => UserRole::Member->value,
            'email' => 'panel-picker-member@steciuk.org',
        ]);
        $panel = Panel::query()->where('slug', 'parish-committee')->firstOrFail();
        $panel->members()->attach($member->id, ['sort_order' => 1]);

        $options = PanelMemberOptions::options($panel->fresh());

        $this->assertArrayHasKey($admin->id, $options);
        $this->assertArrayNotHasKey($member->id, $options);
        $this->assertStringContainsString('panel-picker-admin@steciuk.org', $options[$admin->id]);
    }
}
