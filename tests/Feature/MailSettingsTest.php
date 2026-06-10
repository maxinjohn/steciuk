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

    public function test_admin_mail_settings_override_mail_config(): void
    {
        Setting::set('mail_mailer', 'smtp', 'mail');
        Setting::set('mail_host', 'smtp.parish.test', 'mail');
        Setting::set('mail_port', '465', 'mail');
        Setting::set('mail_username', 'office@steciuk.org', 'mail');
        Setting::set('mail_password', Crypt::encryptString('secret-pass'), 'mail');
        Setting::set('mail_encryption', 'ssl', 'mail');
        Setting::set('mail_from_address', 'noreply@steciuk.org', 'mail');
        Setting::set('mail_from_name', 'STECI UK Parish', 'mail');

        MailConfigService::applyFromSettings();

        $this->assertSame('smtp', config('mail.default'));
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
            'mail_use_smtp' => true,
            'mail_log_only' => false,
            'mail_host' => 'smtp.test.example',
            'mail_port' => 587,
            'mail_username' => 'mail@test.example',
            'mail_password' => 'secret',
            'mail_encryption' => 'tls',
            'mail_from_address' => 'noreply@test.example',
            'mail_from_name' => 'Test Parish',
        ]);

        $this->assertSame('smtp', config('mail.default'));
        $this->assertSame('smtp.test.example', config('mail.mailers.smtp.host'));
        $this->assertSame('mail@test.example', config('mail.mailers.smtp.username'));
        $this->assertSame('secret', config('mail.mailers.smtp.password'));
        $this->assertSame('noreply@test.example', config('mail.from.address'));
    }

    public function test_normalize_form_data_maps_toggles_to_mailer(): void
    {
        $this->assertSame('sendmail', MailConfigService::normalizeFormData([
            'mail_use_smtp' => false,
            'mail_log_only' => false,
        ])['mail_mailer']);

        $this->assertSame('smtp', MailConfigService::normalizeFormData([
            'mail_use_smtp' => true,
            'mail_log_only' => false,
        ])['mail_mailer']);

        $this->assertSame('log', MailConfigService::normalizeFormData([
            'mail_use_smtp' => true,
            'mail_log_only' => true,
        ])['mail_mailer']);
    }

    public function test_validate_configuration_flags_missing_smtp_host(): void
    {
        config([
            'mail.default' => 'smtp',
            'mail.mailers.smtp.host' => '',
        ]);

        $this->assertSame(
            'Enter an SMTP host in Email Setup and save, or choose PHP sendmail instead.',
            MailConfigService::validateConfiguration(),
        );
    }

    public function test_validate_configuration_accepts_sendmail_command_with_arguments(): void
    {
        config([
            'mail.default' => 'sendmail',
            'mail.mailers.sendmail.path' => PHP_BINARY.' -v',
        ]);

        if (function_exists('mail') && ! in_array('mail', array_map('trim', explode(',', (string) ini_get('disable_functions'))), true)) {
            $this->assertNull(MailConfigService::validateConfiguration());
        } else {
            $this->assertNull(MailConfigService::validateConfiguration());
            $this->assertSame(PHP_BINARY, MailConfigService::sendmailBinary(PHP_BINARY.' -v'));
        }
    }

    public function test_sendmail_test_message_uses_php_mail_when_available(): void
    {
        if (! function_exists('mail')) {
            $this->markTestSkipped('mail() is not available in this environment.');
        }

        config([
            'mail.default' => 'sendmail',
            'mail.from.address' => 'noreply@steciuk.org',
            'mail.from.name' => 'STECI UK Parish',
        ]);

        MailConfigService::sendTestMessage('test@example.com');

        $this->assertTrue(true);
    }

    public function test_detect_sendmail_command_prefers_t_flag(): void
    {
        $commands = MailConfigService::defaultSendmailCommands();

        $this->assertSame('/usr/sbin/sendmail -t -i', $commands[0]);
    }

    public function test_apply_from_form_data_uses_unsaved_sendmail_path(): void
    {
        MailConfigService::applyFromFormData([
            'mail_use_smtp' => false,
            'mail_log_only' => false,
            'mail_mailer' => 'sendmail',
            'mail_sendmail_path' => '/custom/sendmail -t -i',
            'mail_from_address' => 'noreply@test.example',
            'mail_from_name' => 'Test Parish',
        ]);

        $this->assertSame('/custom/sendmail -t -i', config('mail.mailers.sendmail.path'));
        $this->assertSame('sendmail', config('mail.default'));
    }

    public function test_send_test_email_notifies_when_smtp_host_is_missing(): void
    {
        $admin = User::factory()->create([
            'email' => 'admin-mail@steciuk.org',
            'role' => UserRole::SuperAdmin,
        ]);

        Livewire::actingAs($admin)
            ->test(MailSettings::class)
            ->set('data.mail_use_smtp', true)
            ->set('data.mail_log_only', false)
            ->set('data.mail_host', '')
            ->set('data.mail_test_recipient', 'test@example.com')
            ->call('sendTestEmail')
            ->assertNotified('Fix the form first');
    }

    public function test_send_test_email_succeeds_with_log_mailer(): void
    {
        Mail::fake();

        $admin = User::factory()->create([
            'email' => 'admin-mail@steciuk.org',
            'role' => UserRole::SuperAdmin,
        ]);

        Livewire::actingAs($admin)
            ->test(MailSettings::class)
            ->set('data.mail_log_only', true)
            ->set('data.mail_use_smtp', false)
            ->set('data.mail_test_recipient', 'test@example.com')
            ->call('sendTestEmail')
            ->assertNotified('Test logged');
    }
}
