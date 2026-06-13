<?php

namespace Tests\Unit;

use App\Filament\Support\HouseholdMemberOptions;
use App\Models\Family;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HouseholdMemberOptionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_marks_unlinked_members_as_available(): void
    {
        $family = Family::query()->create(['name' => 'Target Family']);
        $user = User::factory()->create(['family_id' => null]);

        $label = HouseholdMemberOptions::label($user, $family);

        $this->assertStringContainsString('Available to link', $label);
        $this->assertFalse(HouseholdMemberOptions::isBlocked($user->id, $family, false));
    }

    public function test_it_blocks_members_in_other_households_until_force_move_is_enabled(): void
    {
        $targetFamily = Family::query()->create(['name' => 'Target Family']);
        $otherFamily = Family::query()->create(['name' => 'Other Family']);
        $user = User::factory()->create(['family_id' => $otherFamily->id]);

        $label = HouseholdMemberOptions::label($user, $targetFamily);

        $this->assertStringContainsString('unlink first or enable move below', $label);
        $this->assertTrue(HouseholdMemberOptions::isBlocked($user->id, $targetFamily, false));
        $this->assertFalse(HouseholdMemberOptions::isBlocked($user->id, $targetFamily, true));
    }

    public function test_it_preloads_unlinked_members_in_options(): void
    {
        $family = Family::query()->create(['name' => 'Target Family']);
        $available = User::factory()->create(['family_id' => null, 'last_name' => 'Alpha']);
        User::factory()->create(['family_id' => Family::query()->create(['name' => 'Elsewhere'])->id, 'last_name' => 'Beta']);

        $options = HouseholdMemberOptions::options($family);

        $this->assertArrayHasKey($available->id, $options);
        $this->assertStringContainsString('Available to link', $options[$available->id]);
    }
}
