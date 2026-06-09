<?php

namespace Tests\Feature;

use App\Models\Setting;
use App\Services\MailConfigService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Tests\TestCase;

class MailSettingsTest extends TestCase
{
    use RefreshDatabase;

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
}
