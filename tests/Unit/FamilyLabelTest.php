<?php

namespace Tests\Unit;

use App\Enums\FamilyRelationship;
use App\Models\Family;
use App\Models\User;
use App\Support\FamilyLabel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FamilyLabelTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_label_disambiguates_same_family_name(): void
    {
        $adminA = User::factory()->create([
            'first_name' => 'Maxin',
            'last_name' => 'Thadathil',
            'email' => 'maxin@example.com',
        ]);

        $adminB = User::factory()->create([
            'first_name' => 'Jose',
            'last_name' => 'Thadathil',
            'email' => 'jose@example.com',
        ]);

        $familyA = Family::query()->create([
            'name' => 'Thadathil',
            'admin_user_id' => $adminA->id,
            'preferred_worship_location' => 'Manchester',
        ]);

        $familyB = Family::query()->create([
            'name' => 'Thadathil',
            'admin_user_id' => $adminB->id,
            'preferred_worship_location' => 'London',
        ]);

        $labelA = FamilyLabel::forAdmin($familyA->fresh()->load('admin'));
        $labelB = FamilyLabel::forAdmin($familyB->fresh()->load('admin'));

        $this->assertNotSame($labelA, $labelB);
        $this->assertStringContainsString('Thadathil', $labelA);
        $this->assertStringContainsString('maxin@example.com', $labelA);
        $this->assertStringContainsString('Manchester', $labelA);
        $this->assertStringContainsString('Household #'.$familyA->id, $labelA);
        $this->assertStringContainsString('jose@example.com', $labelB);
    }

    public function test_user_family_table_lines_use_labelled_compact_format(): void
    {
        $admin = User::factory()->create([
            'name' => 'Maxin John',
            'first_name' => 'Maxin',
            'last_name' => 'John',
        ]);

        $family = Family::query()->create([
            'name' => 'Test-Family',
            'admin_user_id' => $admin->id,
        ]);

        $member = User::factory()->create([
            'family_id' => $family->id,
            'family_relationship' => FamilyRelationship::Spouse->value,
        ]);

        $lines = FamilyLabel::userFamilyTableLines($member->fresh()->load('family.admin'));

        $this->assertSame([
            'Family name: Test-Family',
            'Family admin: Maxin John',
            'Relation to admin: Spouse / partner',
        ], $lines);
    }

    public function test_family_table_lines_show_name_and_admin_only(): void
    {
        $admin = User::factory()->create([
            'name' => 'Maxin John',
            'first_name' => 'Maxin',
            'last_name' => 'John',
        ]);

        $family = Family::query()->create([
            'name' => 'Test-Family',
            'admin_user_id' => $admin->id,
        ]);

        $lines = FamilyLabel::familyTableLines($family->fresh()->load('admin'));

        $this->assertSame([
            'Family name: Test-Family',
            'Family admin: Maxin John',
        ], $lines);
    }

    public function test_table_summary_only_flags_deactivated_households(): void
    {
        $admin = User::factory()->create();

        $family = Family::query()->create([
            'name' => 'Thadathil',
            'admin_user_id' => $admin->id,
            'is_active' => false,
        ]);

        $this->assertSame('Deactivated', FamilyLabel::tableSummary($family->fresh()));
        $this->assertNull(FamilyLabel::tableSummary(Family::query()->create([
            'name' => 'Active',
            'admin_user_id' => $admin->id,
        ])));
    }

    public function test_member_portal_label_includes_primary_account_name(): void
    {
        $admin = User::factory()->create([
            'name' => 'Maxin Thadathil',
            'first_name' => 'Maxin',
            'last_name' => 'Thadathil',
        ]);

        $family = Family::query()->create([
            'name' => 'Thadathil',
            'admin_user_id' => $admin->id,
        ]);

        $this->assertSame(
            'Thadathil (Maxin Thadathil)',
            FamilyLabel::forMemberPortal($family->fresh()->load('admin')),
        );
    }
}
