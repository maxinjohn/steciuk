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
            ->set('data.give_page_heading', 'Support our parish')
            ->set('data.give_bank_name', 'Barclays')
            ->set('data.give_account_name', 'STECI UK Parish')
            ->set('data.give_sort_code', '20-00-00')
            ->set('data.give_account_number', '87654321')
            ->set('data.give_payment_reference', 'Surname + Giving')
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('settings', [
            'key' => 'give_bank_name',
            'value' => 'Barclays',
        ]);
        $this->assertSame('Barclays', Setting::get('give_bank_name'));
        $this->assertSame('87654321', Setting::get('give_account_number'));
        $this->assertSame('/give', Setting::get('donation_link'));
    }

    public function test_email_templates_page_loads_with_defaults(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $service = app(ParishEmailService::class);

        $this->actingAs($admin)
            ->get(AdminPanelConfig::url('email-templates'))
            ->assertOk()
            ->assertSee('Member account approved', false)
            ->assertSee('Welcome after registration submitted', false);

        $expected = [];
        foreach ($service->defaultTemplates() as $key => $template) {
            $resolved = $service->resolve($key);
            $expected["{$key}_subject"] = $resolved['subject'];
            $expected["{$key}_body"] = $resolved['body'];
        }

        Livewire::actingAs($admin)
            ->test(EmailTemplatesSettings::class)
            ->assertFormFieldExists('account_approved_subject')
            ->assertFormSet($expected);
    }

    public function test_email_templates_restore_defaults_refills_form(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $service = app(ParishEmailService::class);

        Setting::set($service::STORAGE_KEY, json_encode([
            'parish_welcome' => ['subject' => 'Custom subject', 'body' => 'Custom body'],
        ]), 'mail');
        Setting::forgetCache();

        Livewire::actingAs($admin)
            ->test(EmailTemplatesSettings::class)
            ->call('restoreDefaults')
            ->assertSet('data.parish_welcome_subject', $service->defaultTemplates()[$service::PARISH_WELCOME]['subject'])
            ->assertSet('data.parish_welcome_body', $service->defaultTemplates()[$service::PARISH_WELCOME]['body']);
    }

    public function test_custom_saved_email_template_is_used_when_resolving(): void
    {
        Setting::set(ParishEmailService::STORAGE_KEY, json_encode([
            ParishEmailService::PARISH_WELCOME => [
                'subject' => 'Custom welcome subject',
                'body' => 'Custom welcome body for {first_name}',
            ],
        ]), 'mail');
        Setting::forgetCache();

        $template = app(ParishEmailService::class)->resolve(ParishEmailService::PARISH_WELCOME);

        $this->assertSame('Custom welcome subject', $template['subject']);
        $this->assertSame('Custom welcome body for {first_name}', $template['body']);
    }

    public function test_email_template_tabs_match_known_workflows(): void
    {
        $labels = array_column(app(ParishEmailService::class)->defaultTemplates(), 'label');

        $this->assertSame([
            'Member account approved',
            'Account created by parish admin',
            'Registration not approved',
            'Added to a parish family',
            'Family member request approved',
            'Welcome after registration submitted',
        ], $labels);
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
