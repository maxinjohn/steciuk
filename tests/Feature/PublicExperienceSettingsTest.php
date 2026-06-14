<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Setting;
use App\Models\User;
use App\Support\PublicUiContent;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PublicExperienceSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_can_save_public_experience_tabs(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(\App\Filament\Pages\PublicExperienceSettings::class)
            ->set('data.public_ui_spark_strip', [
                'kicker' => 'Held by grace',
                'items' => [
                    ['label' => 'Mercy', 'ref' => 'Lam 3:22', 'href' => '/our-church'],
                ],
            ])
            ->set('data.public_ui_experience', [
                'enabled' => true,
                'heavenly_atmosphere' => true,
                'speculation_rules' => false,
                'reading_progress' => true,
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        Setting::forgetCache();

        $this->assertSame('Held by grace', PublicUiContent::sparkStrip()['kicker']);
        $this->assertFalse(\App\Support\FutureSiteConfig::speculationEnabled());
    }
}
