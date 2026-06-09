<?php

namespace Tests\Feature;

use App\Filament\Pages\MailSettings;
use App\Models\Setting;
use App\Models\User;
use App\Enums\UserRole;
use App\Services\MailConfigService;
use Database\Seeders\ReferenceDataSeeder;
use App\Support\SeedConfig;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Livewire\Livewire;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_admin_smtp_settings_override_mail_config(): void
    {
        Setting::set('mail_use_admin_smtp', '1', 'mail');
        Setting::set('mail_host', 'smtp.parish.test', 'mail');
        Setting::set('mail_port', '465', 'mail');
        Setting::set('mail_username', 'office@steciuk.org', 'mail');
        Setting::set('mail_password', Crypt::encryptString('secret-pass'), 'mail');
        Setting::set('mail_encryption', 'ssl', 'mail');
        Setting::set('mail_from_address', 'noreply@steciuk.org', 'mail');
        Setting::set('mail_from_name', 'STECI UK Parish', 'mail');

        MailConfigService::applyFromSettings();

        $this->assertSame('smtp.parish.test', config('mail.mailers.smtp.host'));
        $this->assertSame(465, config('mail.mailers.smtp.port'));
        $this->assertSame('office@steciuk.org', config('mail.mailers.smtp.username'));
        $this->assertSame('secret-pass', config('mail.mailers.smtp.password'));
        $this->assertSame('ssl', config('mail.mailers.smtp.encryption'));
        $this->assertSame('noreply@steciuk.org', config('mail.from.address'));
    }

    public function test_apply_from_form_data_uses_unsaved_smtp_fields(): void
    {
        MailConfigService::applyFromFormData([
            'mail_mailer' => 'smtp',
            'mail_host' => 'smtp.test.example',
            'mail_port' => 587,
            'mail_username' => 'mail@test.example',
            'mail_password' => 'secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@test.example',
            'mail_from_name' => 'Test Parish',
        ], true);

        $this->assertSame('smtp.test.example', config('mail.mailers.smtp.host'));
        $this->assertSame('mail@test.example', config('mail.mailers.smtp.username'));
        $this->assertSame('secret', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@test.example', config('mail.from.address'));
    }

    public function test_validate_configuration_flags_missing_env_smtp_host(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '',
        ]);

        $this->assertSame(
            'MAIL_HOST is missing in .env. Add SMTP settings on the server, set MAIL_MAILER=sendmail for PHP mail, or enable admin-configured mail.',
            MailConfigService::validateConfiguration(false),
        );
    }

    public function test_validate_configuration_accepts_sendmail_command_with_arguments(): void
    {
        config([
            'mail.default' => 'sendmail',
            'mail.mailers.sendmail.path' => PHP_BINARY.' -v',
        ]);

        $this->assertNull(MailConfigService::validateConfiguration(false));
        $this->assertSame(PHP_BINARY, MailConfigService::sendmailBinary());
    }

    public function test_sendmail_test_message_uses_process_timeout_without_hanging(): void
    {
        config([
            'mail.default' => 'sendmail',
            'mail.mailers.sendmail.path' => PHP_BINARY.' -r exit(0);',
            'mail.from.address' => 'noreply@steciuk.org',
            'mail.from.name' => 'STECI UK Parish',
        ]);

        MailConfigService::sendTestMessage('test@example.com');

        $this->assertTrue(true);
    }

    public function test_apply_from_form_data_uses_unsaved_sendmail_path(): void
    {
        MailConfigService::applyFromFormData([
            'mail_mailer' => 'sendmail',
            'mail_sendmail_path' => '/custom/sendmail -bs -i',
            'mail_from_address' => 'noreply@test.example',
            'mail_from_name' => 'Test Parish',
        ], true);

        $this->assertSame('/custom/sendmail -bs -i', config('mail.mailers.sendmail.path'));
        $this->assertSame('sendmail', config('mail.default'));
    }

    public function test_send_test_email_notifies_when_env_smtp_is_missing(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '',
        ]);

        $admin = User::factory()->create([
            'email' => 'admin-mail@steciuk.org',
            'role' => UserRole::SuperAdmin,
        ]);

        Livewire::actingAs($admin)
            ->test(MailSettings::class)
            ->set('data.mail_test_recipient', 'test@example.com')
            ->call('sendTestEmail')
            ->assertNotified('Mail not configured');
    }

    public function test_send_test_email_succeeds_with_log_mailer(): void
    {
        Mail::fake();
        config(['mail.default' => 'log']);

        $admin = User::factory()->create([
            'email' => 'admin-mail@steciuk.org',
            'role' => UserRole::SuperAdmin,
        ]);

        Livewire::actingAs($admin)
            ->test(MailSettings::class)
            ->set('data.mail_test_recipient', 'test@example.com')
            ->call('sendTestEmail')
            ->assertNotified('Test logged');
    }
}
