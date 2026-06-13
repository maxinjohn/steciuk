<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Support\FaithComfortVerseBuckets;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class FaithComfortSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_can_save_tabbed_verse_pools(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\FaithComfortSettings::class)
            ->set('data.'.FaithComfortVerseBuckets::FIELD_ALL, [
                ['ref' => 'Psalm 23:1', 'text' => 'The Lord is my shepherd.'],
            ])
            ->set('data.'.FaithComfortVerseBuckets::FIELD_ERROR, [
                ['ref' => 'John 3:16', 'text' => 'For God so loved the world.'],
            ])
            ->set('data.'.FaithComfortVerseBuckets::FIELD_MAINTENANCE, [])
            ->set('data.'.FaithComfortVerseBuckets::FIELD_LAUNCH, [])
            ->set('data.'.FaithComfortVerseBuckets::FIELD_PATHS, [])
            ->set('data.faith_sanctuary_ribbons', [
                ['kicker' => 'Peace be with you', 'note' => 'Go in the Lord\'s peace.'],
            ])
            ->set('data.faith_comfort_headers', [
                ['kicker' => 'For believers', 'heading' => 'Rest in Christ', 'subheading' => 'Anchored in grace.'],
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        Setting::forgetCache();

        $stored = json_decode(Setting::get('faith_sanctuary_verses', '[]') ?: '[]', true);
        $ribbons = json_decode(Setting::get('faith_sanctuary_ribbons', '[]') ?: '[]', true);
        $headers = json_decode(Setting::get('faith_comfort_headers', '[]') ?: '[]', true);

        $this->assertCount(2, $stored);
        $this->assertSame('', collect($stored)->firstWhere('text', 'The Lord is my shepherd.')['only_on']);
        $this->assertSame('error', collect($stored)->firstWhere('text', 'For God so loved the world.')['only_on']);
        $this->assertSame('Peace be with you', $ribbons[0]['kicker']);
        $this->assertSame('Rest in Christ', $headers[0]['heading']);
    }

    public function test_mount_splits_stored_verses_into_tabs(): void
    {
        Setting::set('faith_sanctuary_verses', json_encode([
            ['ref' => 'Psalm 46:10', 'text' => 'Be still.', 'only_on' => ''],
            ['ref' => 'Matthew 11:28', 'text' => 'Come to me.', 'only_on' => 'maintenance'],
        ]), 'faith');

        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\FaithComfortSettings::class)
            ->assertSet('data.'.FaithComfortVerseBuckets::FIELD_ALL, function (mixed $value): bool {
                $items = array_values(is_array($value) ? $value : []);

                return count($items) === 1
                    && ($items[0]['text'] ?? null) === 'Be still.'
                    && ($items[0]['ref'] ?? null) === 'Psalm 46:10';
            })
            ->assertSet('data.'.FaithComfortVerseBuckets::FIELD_MAINTENANCE, function (mixed $value): bool {
                $items = array_values(is_array($value) ? $value : []);

                return count($items) === 1
                    && ($items[0]['text'] ?? null) === 'Come to me.'
                    && ($items[0]['ref'] ?? null) === 'Matthew 11:28';
            });
    }
}
