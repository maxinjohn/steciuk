<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Schema;

class MailConfigService
{
    public static function applyFromSettings(): void
    {
        static::applyForSending((bool) Setting::get('mail_use_admin_smtp', false));
    }

    public static function applyForSending(?bool $useAdminSmtp = null): void
    {
        if (! static::settingsTableReady()) {
            return;
        }

        $useAdminSmtp ??= (bool) Setting::get('mail_use_admin_smtp', false);

        static::applySenderIdentity();

        if (! $useAdminSmtp) {
            return;
        }

        $mailer = Setting::get('mail_mailer', 'smtp');
        $encryption = Setting::get('mail_encryption');
        $password = Setting::get('mail_password');

        if (is_string($password) && $password !== '') {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable) {
                $password = null;
            }
        }

        $config = [
            'mail.default' => $mailer ?: 'smtp',
            'mail.from.address' => Setting::get('mail_from_address') ?: config('mail.from.address'),
            'mail.from.name' => Setting::get('mail_from_name') ?: config('mail.from.name'),
        ];

        if ($mailer === 'sendmail') {
            $config['mail.mailers.sendmail.transport'] = 'sendmail';
            $config['mail.mailers.sendmail.path'] = Setting::get('mail_sendmail_path')
                ?: config('mail.mailers.sendmail.path', '/usr/sbin/sendmail -bs -i');
        } else {
            $config['mail.mailers.smtp.transport'] = 'smtp';
            $config['mail.mailers.smtp.host'] = Setting::get('mail_host');
            $config['mail.mailers.smtp.port'] = (int) (Setting::get('mail_port') ?: 587);
            $config['mail.mailers.smtp.username'] = Setting::get('mail_username');
            $config['mail.mailers.smtp.password'] = $password;
            $config['mail.mailers.smtp.encryption'] = $encryption === 'none' ? null : $encryption;
        }

        config($config);
    }

    public static function encryptPassword(?string $password): ?string
    {
        if ($password === null || $password === '') {
            return null;
        }

        return Crypt::encryptString($password);
    }

    public static function passwordIsConfigured(): bool
    {
        return (bool) Setting::get('mail_password');
    }

    private static function applySenderIdentity(): void
    {
        $fromAddress = Setting::get('mail_from_address');
        $fromName = Setting::get('mail_from_name');

        if ($fromAddress) {
            config(['mail.from.address' => $fromAddress]);
        }

        if ($fromName) {
            config(['mail.from.name' => $fromName]);
        }
    }

    private static function settingsTableReady(): bool
    {
        try {
            return Schema::hasTable('settings');
        } catch (\Throwable) {
            return false;
        }
    }
}
