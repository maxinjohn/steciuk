<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Exception\ProcessTimedOutException;
use Symfony\Component\Process\Process;

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
        static::ensureTransportTimeouts();

        if (! $useAdminSmtp) {
            return;
        }

        static::applyAdminMailerFromSettings();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function applyFromFormData(array $data, bool $useAdminSmtp): void
    {
        static::applySenderIdentityFromForm($data);
        static::ensureTransportTimeouts();

        if (! $useAdminSmtp) {
            return;
        }

        $mailer = (string) ($data['mail_mailer'] ?? 'smtp');
        $encryption = (string) ($data['mail_encryption'] ?? 'tls');
        $password = static::resolvePasswordFromForm($data);

        $config = [
            'mail.default' => $mailer ?: 'smtp',
            'mail.from.address' => ($data['mail_from_address'] ?? null) ?: config('mail.from.address'),
            'mail.from.name' => ($data['mail_from_name'] ?? null) ?: config('mail.from.name'),
        ];

        if ($mailer === 'sendmail') {
            $config['mail.mailers.sendmail.transport'] = 'sendmail';
            $config['mail.mailers.sendmail.path'] = static::resolveSendmailPath($data['mail_sendmail_path'] ?? null);
        } else {
            $config['mail.mailers.smtp.transport'] = 'smtp';
            $config['mail.mailers.smtp.host'] = (string) ($data['mail_host'] ?? '');
            $config['mail.mailers.smtp.port'] = (int) ($data['mail_port'] ?: 587);
            $config['mail.mailers.smtp.username'] = (string) ($data['mail_username'] ?? '');
            $config['mail.mailers.smtp.password'] = $password;
            $config['mail.mailers.smtp.encryption'] = $encryption === 'none' ? null : $encryption;
        }

        config($config);
    }

    public static function validateConfiguration(?bool $useAdminSmtp = null): ?string
    {
        $useAdminSmtp ??= (bool) Setting::get('mail_use_admin_smtp', false);
        $mailer = (string) config('mail.default', 'log');

        if ($mailer === 'log') {
            return null;
        }

        if ($mailer === 'array') {
            return 'Mail driver is set to array — no messages are delivered.';
        }

        if ($mailer === 'sendmail') {
            return static::validateSendmailPath();
        }

        if ($mailer !== 'smtp') {
            return null;
        }

        $host = trim((string) config('mail.mailers.smtp.host', ''));

        if ($host === '') {
            return $useAdminSmtp
                ? 'Enter an SMTP host, or save settings and try again.'
                : 'MAIL_HOST is missing in .env. Add SMTP settings on the server, set MAIL_MAILER=sendmail for PHP mail, or enable admin-configured mail.';
        }

        $port = (int) config('mail.mailers.smtp.port', 0);
        if ($port <= 0) {
            return 'SMTP port is invalid.';
        }

        return null;
    }

    public static function sendTestMessage(string $recipient): void
    {
        static::deliverPlainTextMessage(
            $recipient,
            'STECI UK — mail test',
            'This is a test message from the STECI UK parish admin panel. Mail delivery is working.',
        );
    }

    public static function deliverPlainTextMessage(
        string $to,
        string $subject,
        string $body,
        ?string $fromAddress = null,
        ?string $fromName = null,
    ): void {
        static::ensureTransportTimeouts();

        $mailer = (string) config('mail.default', 'log');
        $fromAddress ??= (string) config('mail.from.address');
        $fromName ??= (string) config('mail.from.name');

        if ($mailer === 'sendmail') {
            static::deliverViaSendmailProcess($to, $subject, $body, $fromAddress, $fromName);

            return;
        }

        Mail::mailer($mailer)->raw(
            $body,
            fn ($message) => $message
                ->to($to)
                ->subject($subject)
                ->from($fromAddress, $fromName !== '' ? $fromName : null),
        );
    }

    public static function friendlyError(\Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        if ($exception instanceof ProcessTimedOutException) {
            return 'PHP mail (sendmail) timed out. Check MAIL_SENDMAIL_PATH, local mail service, or switch to SMTP.';
        }

        if ($message === '') {
            return 'Mail could not be sent. Check your mail settings and try again.';
        }

        if (str_contains(strtolower($message), 'connection could not be established')) {
            return 'Could not connect to the SMTP server. Check host, port, firewall, and that outbound mail is allowed on your host.';
        }

        if (str_contains(strtolower($message), 'authentication')) {
            return 'SMTP authentication failed. Check username and password.';
        }

        if (str_contains(strtolower($message), 'timed out') || str_contains(strtolower($message), 'timeout')) {
            return 'Mail delivery timed out. For PHP mail, check sendmail/local MTA. For SMTP, check host, port, and encryption.';
        }

        if (str_contains(strtolower($message), 'sendmail')) {
            return 'PHP mail (sendmail) failed. Verify MAIL_SENDMAIL_PATH and that the server mail service is running.';
        }

        return $message;
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

    /**
     * @return list<string>
     */
    public static function parseSendmailCommand(?string $command = null): array
    {
        $command = trim($command ?? static::resolveSendmailPath());

        if ($command === '') {
            return [];
        }

        preg_match('/^\S+/', $command, $matches);
        $binary = $matches[0] ?? '';
        $arguments = preg_split('/\s+/', trim(substr($command, strlen($binary))), flags: PREG_SPLIT_NO_EMPTY);

        return array_values(array_filter([$binary, ...($arguments ?: [])]));
    }

    public static function sendmailBinary(?string $command = null): ?string
    {
        $parts = static::parseSendmailCommand($command);

        return $parts[0] ?? null;
    }

    private static function applyAdminMailerFromSettings(): void
    {
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
            $config['mail.mailers.sendmail.path'] = static::resolveSendmailPath(Setting::get('mail_sendmail_path'));
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

    /**
     * @param  array<string, mixed>  $data
     */
    private static function resolvePasswordFromForm(array $data): ?string
    {
        $password = $data['mail_password'] ?? null;

        if (is_string($password) && $password !== '') {
            return $password;
        }

        $stored = Setting::get('mail_password');

        if (! is_string($stored) || $stored === '') {
            return null;
        }

        try {
            return Crypt::decryptString($stored);
        } catch (\Throwable) {
            return null;
        }
    }

    private static function applySenderIdentity(): void
    {
        static::applySenderIdentityFromForm([
            'mail_from_address' => Setting::get('mail_from_address'),
            'mail_from_name' => Setting::get('mail_from_name'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function applySenderIdentityFromForm(array $data): void
    {
        $fromAddress = $data['mail_from_address'] ?? null;
        $fromName = $data['mail_from_name'] ?? null;

        if ($fromAddress) {
            config(['mail.from.address' => $fromAddress]);
        }

        if ($fromName) {
            config(['mail.from.name' => $fromName]);
        }
    }

    private static function deliverViaSendmailProcess(
        string $to,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName,
    ): void {
        $command = static::parseSendmailCommand();

        if ($command === []) {
            throw new \RuntimeException('Sendmail command is not configured.');
        }

        $fromHeader = $fromName !== ''
            ? sprintf('%s <%s>', static::encodeHeaderValue($fromName), $fromAddress)
            : $fromAddress;

        $payload = implode("\r\n", [
            'To: '.$to,
            'From: '.$fromHeader,
            'Subject: '.static::encodeHeaderValue($subject),
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            '',
            $body,
        ])."\r\n";

        $process = new Process($command);
        $process->setInput($payload);
        $process->setTimeout(static::sendmailTimeout());
        $process->run();

        if (! $process->isSuccessful()) {
            throw new \RuntimeException(trim($process->getErrorOutput() ?: $process->getOutput() ?: 'Sendmail failed to send the message.'));
        }
    }

    private static function validateSendmailPath(?string $command = null): ?string
    {
        $command = trim($command ?? static::resolveSendmailPath());

        if ($command === '') {
            return 'Sendmail path is missing. Set MAIL_SENDMAIL_PATH in .env or choose PHP sendmail in admin mail settings.';
        }

        $binary = static::sendmailBinary($command);

        if ($binary === null || $binary === '') {
            return 'Sendmail path is invalid. Example: /usr/sbin/sendmail -bs -i';
        }

        if (! file_exists($binary)) {
            return "Sendmail binary not found at {$binary}. Check MAIL_SENDMAIL_PATH in .env.";
        }

        if (! is_executable($binary)) {
            return "Sendmail at {$binary} is not executable. Check permissions or use SMTP instead.";
        }

        return null;
    }

    private static function resolveSendmailPath(?string $override = null): string
    {
        $path = trim((string) ($override ?: Setting::get('mail_sendmail_path')));

        if ($path !== '') {
            return $path;
        }

        return (string) config('mail.mailers.sendmail.path', '/usr/sbin/sendmail -bs -i');
    }

    private static function ensureTransportTimeouts(): void
    {
        $smtpTimeout = (int) config('mail.mailers.smtp.timeout', 10);
        if ($smtpTimeout <= 0) {
            $smtpTimeout = 10;
        }

        $sendmailTimeout = static::sendmailTimeout();

        config([
            'mail.mailers.smtp.timeout' => $smtpTimeout,
            'mail.mailers.sendmail.timeout' => $sendmailTimeout,
        ]);
    }

    private static function sendmailTimeout(): int
    {
        $timeout = (int) config('mail.mailers.sendmail.timeout', 15);

        return $timeout > 0 ? $timeout : 15;
    }

    private static function encodeHeaderValue(string $value): string
    {
        return str_contains($value, "\r") || str_contains($value, "\n")
            ? mb_encode_mimeheader($value, 'UTF-8')
            : $value;
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
