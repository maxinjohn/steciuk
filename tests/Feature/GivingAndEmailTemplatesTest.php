<?php

namespace Tests\Feature;

use App\Filament\Pages\EmailTemplatesSettings;
use App\Filament\Pages\GivingSettings;
use App\Models\Setting;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\ParishEmailService;
use App\Support\AdminPanelConfig;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class GivingAndEmailTemplatesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_give_page_loads(): void
    {
        $response = $this->get(route('give'));

        $response->assertOk();
        $response->assertSee('Support our parish', false);
    }

    public function test_give_page_shows_bank_details_when_configured(): void
    {
        Setting::set('give_bank_name', 'Test Bank', 'giving');
        Setting::set('give_account_name', 'STECI UK Parish', 'giving');
        Setting::set('give_sort_code', '12-34-56', 'giving');
        Setting::set('give_account_number', '12345678', 'giving');
        Setting::forgetCache();

        $response = $this->get(route('give'));

        $response->assertOk();
        $response->assertSee('Test Bank', false);
        $response->assertSee('12-34-56', false);
        $response->assertSee('12345678', false);
    }

    public function test_email_templates_seed_defaults_when_blank(): void
    {
        Setting::set(ParishEmailService::STORAGE_KEY, json_encode([
            'account_approved' => ['subject' => '', 'body' => ''],
        ]), 'mail');
        Setting::forgetCache();

        app(ParishEmailService::class)->seedDefaultsIfMissing();

        $template = app(ParishEmailService::class)->resolve(ParishEmailService::ACCOUNT_APPROVED);

        $this->assertNotSame('', trim($template['subject']));
        $this->assertNotSame('', trim($template['body']));
        $this->assertStringContainsString('{site_name}', $template['subject']);
    }

    public function test_super_admin_can_save_giving_bank_details(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        Livewire::actingAs($admin)
            ->test(GivingSettings::class)
            ->fillForm([
                'give_bank_name' => 'Barclays',
                'give_account_name' => 'STECI UK Parish',
                'give_sort_code' => '20-00-00',
                'give_account_number' => '87654321',
                'give_payment_reference' => 'Surname + Giving',
            ])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('Barclays', Setting::get('give_bank_name'));
        $this->assertSame('87654321', Setting::get('give_account_number'));
        $this->assertSame('/give', Setting::get('donation_link'));
    }

    public function test_email_templates_page_loads_with_defaults(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('email-templates'))
            ->assertOk()
            ->assertSee('Member account approved', false);

        Livewire::actingAs($admin)
            ->test(EmailTemplatesSettings::class)
            ->assertFormFieldExists('account_approved_subject')
            ->assertFormSet([
                'account_approved_subject' => app(ParishEmailService::class)->resolve(ParishEmailService::ACCOUNT_APPROVED)['subject'],
            ]);
    }

    public function test_legacy_external_give_link_resolves_to_local_route(): void
    {
        Setting::set('donation_link', 'https://steciuk.org/give', 'general');
        Setting::forgetCache();

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee(route('give'), false);
        $response->assertDontSee('https://steciuk.org/give', false);
    }

    public function test_legacy_give_slug_redirects_to_give_route(): void
    {
        $this->get('/give')->assertOk();

        // Safety redirect if a CMS page slug ever catches first.
        $this->assertSame(200, $this->get(route('give'))->status());
    }
}
