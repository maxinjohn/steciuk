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
    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    public static function normalizeFormData(array $data): array
    {
        if ($data['mail_log_only'] ?? false) {
            $data['mail_mailer'] = 'log';
        } elseif ($data['mail_use_smtp'] ?? false) {
            $data['mail_mailer'] = 'smtp';
        } else {
            $data['mail_mailer'] = 'sendmail';
        }

        return $data;
    }

    /**
     * @return array{mail_use_smtp: bool, mail_log_only: bool}
     */
    public static function togglesFromMailer(?string $mailer): array
    {
        return [
            'mail_use_smtp' => $mailer === 'smtp',
            'mail_log_only' => $mailer === 'log',
        ];
    }

    public static function applyFromSettings(): void
    {
        if (! static::settingsTableReady()) {
            return;
        }

        static::applySenderIdentity();
        static::ensureTransportTimeouts();
        static::applyAdminMailerFromSettings();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function applyFromFormData(array $data): void
    {
        $data = static::normalizeFormData($data);

        static::applySenderIdentityFromForm($data);
        static::ensureTransportTimeouts($data);
        static::applyMailerFromFormData($data);
    }

    public static function validateConfiguration(): ?string
    {
        $mailer = (string) config('mail.default', 'log');

        if ($mailer === 'log') {
            return null;
        }

        if ($mailer === 'array') {
            return 'Mail driver is set to array — no messages are delivered.';
        }

        if ($mailer === 'sendmail') {
            return static::validateSendmailPath((string) config('mail.mailers.sendmail.path', ''));
        }

        if ($mailer !== 'smtp') {
            return null;
        }

        $host = trim((string) config('mail.mailers.smtp.host', ''));

        if ($host === '') {
            return 'Enter an SMTP host in Email Setup and save, or choose PHP sendmail instead.';
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
            return 'PHP mail (sendmail) timed out. Check the sendmail command in Email Setup or switch to SMTP.';
        }

        if ($message === '') {
            return 'Mail could not be sent. Check Email Setup and try again.';
        }

        if (str_contains(strtolower($message), 'connection could not be established')) {
            return 'Could not connect to the SMTP server. Check host, port, firewall, and that outbound mail is allowed on your host.';
        }

        if (str_contains(strtolower($message), 'authentication')) {
            return 'SMTP authentication failed. Check username and password in Email Setup.';
        }

        if (str_contains(strtolower($message), 'timed out') || str_contains(strtolower($message), 'timeout')) {
            return 'Mail delivery timed out. For PHP mail, check sendmail/local MTA. For SMTP, check host, port, and encryption.';
        }

        if (str_contains(strtolower($message), 'sendmail')) {
            return 'PHP mail (sendmail) failed. Verify the sendmail command in Email Setup.';
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
        static::applyMailerConfig(
            (string) (Setting::get('mail_mailer') ?: 'sendmail'),
            Setting::get('mail_host'),
            Setting::get('mail_port'),
            Setting::get('mail_username'),
            static::decryptStoredPassword(Setting::get('mail_password')),
            Setting::get('mail_encryption'),
            static::resolveSendmailPath(Setting::get('mail_sendmail_path')),
            Setting::get('mail_from_address'),
            Setting::get('mail_from_name'),
        );
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private static function applyMailerFromFormData(array $data): void
    {
        static::applyMailerConfig(
            (string) ($data['mail_mailer'] ?? 'sendmail'),
            $data['mail_host'] ?? null,
            $data['mail_port'] ?? null,
            $data['mail_username'] ?? null,
            static::resolvePasswordFromForm($data),
            $data['mail_encryption'] ?? null,
            static::resolveSendmailPath($data['mail_sendmail_path'] ?? null),
            $data['mail_from_address'] ?? null,
            $data['mail_from_name'] ?? null,
        );
    }

    private static function applyMailerConfig(
        string $mailer,
        mixed $host,
        mixed $port,
        mixed $username,
        ?string $password,
        mixed $encryption,
        string $sendmailPath,
        mixed $fromAddress,
        mixed $fromName,
    ): void {
        $config = [
            'mail.default' => $mailer ?: 'sendmail',
            'mail.from.address' => $fromAddress ?: config('mail.from.address'),
            'mail.from.name' => $fromName ?: config('mail.from.name'),
        ];

        if ($mailer === 'sendmail') {
            $config['mail.mailers.sendmail.transport'] = 'sendmail';
            $config['mail.mailers.sendmail.path'] = $sendmailPath;
        } elseif ($mailer === 'log') {
            $config['mail.mailers.log.transport'] = 'log';
        } else {
            $encryptionValue = $encryption === 'none' ? null : $encryption;
            $config['mail.mailers.smtp.transport'] = 'smtp';
            $config['mail.mailers.smtp.host'] = (string) ($host ?? '');
            $config['mail.mailers.smtp.port'] = (int) ($port ?: 587);
            $config['mail.mailers.smtp.username'] = (string) ($username ?? '');
            $config['mail.mailers.smtp.password'] = $password;
            $config['mail.mailers.smtp.encryption'] = $encryptionValue ?: 'tls';
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

        return static::decryptStoredPassword(Setting::get('mail_password'));
    }

    private static function decryptStoredPassword(mixed $stored): ?string
    {
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
            return 'Sendmail command is missing. Enter it in Email Setup (e.g. /usr/sbin/sendmail -bs -i).';
        }

        $binary = static::sendmailBinary($command);

        if ($binary === null || $binary === '') {
            return 'Sendmail command is invalid. Example: /usr/sbin/sendmail -bs -i';
        }

        if (! file_exists($binary)) {
            return "Sendmail binary not found at {$binary}. Check the sendmail command in Email Setup.";
        }

        if (! is_executable($binary)) {
            return "Sendmail at {$binary} is not executable. Check permissions or use SMTP instead.";
        }

        return null;
    }

    private static function resolveSendmailPath(?string $override = null): string
    {
        $path = trim((string) ($override ?? ''));

        if ($path === '') {
            $path = trim((string) config('mail.mailers.sendmail.path', ''));
        }

        if ($path === '') {
            $path = trim((string) Setting::get('mail_sendmail_path'));
        }

        if ($path === '') {
            $path = '/usr/sbin/sendmail -bs -i';
        }

        return $path;
    }

    private static function ensureTransportTimeouts(?array $data = null): void
    {
        $smtpTimeout = (int) ($data['mail_smtp_timeout'] ?? Setting::get('mail_smtp_timeout') ?: config('mail.mailers.smtp.timeout', 10));
        if ($smtpTimeout <= 0) {
            $smtpTimeout = 10;
        }

        $sendmailTimeout = (int) ($data['mail_sendmail_timeout'] ?? Setting::get('mail_sendmail_timeout') ?: config('mail.mailers.sendmail.timeout', 15));
        if ($sendmailTimeout <= 0) {
            $sendmailTimeout = 15;
        }

        config([
            'mail.mailers.smtp.timeout' => $smtpTimeout,
            'mail.mailers.sendmail.timeout' => $sendmailTimeout,
        ]);
    }

    private static function sendmailTimeout(): int
    {
        static::ensureTransportTimeouts();

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
