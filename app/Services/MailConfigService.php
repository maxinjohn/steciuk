<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\Process\Exception\ProcessTimedOutException;

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
            if (static::canUsePhpMail()) {
                return null;
            }

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
        ?string $replyToAddress = null,
        ?string $replyToName = null,
    ): void {
        static::ensureTransportTimeouts();

        $mailer = (string) config('mail.default', 'log');
        $fromAddress ??= (string) config('mail.from.address');
        $fromName ??= (string) config('mail.from.name');

        if ($mailer === 'sendmail') {
            static::deliverViaSendmail($to, $subject, $body, $fromAddress, $fromName, $replyToAddress, $replyToName);

            return;
        }

        Mail::mailer($mailer)->raw(
            $body,
            function ($message) use ($to, $subject, $fromAddress, $fromName, $replyToAddress, $replyToName): void {
                $message
                    ->to($to)
                    ->subject($subject)
                    ->from($fromAddress, $fromName !== '' ? $fromName : null);

                if ($replyToAddress) {
                    $message->replyTo($replyToAddress, $replyToName !== '' ? $replyToName : null);
                }
            },
        );
    }

    public static function friendlyError(\Throwable $exception): string
    {
        $message = trim($exception->getMessage());

        if ($exception instanceof ProcessTimedOutException) {
            return 'PHP mail timed out. Try SMTP in Email Setup or ask your host to enable server mail.';
        }

        if ($message === '') {
            return 'Mail could not be sent. Check Email Setup and try again.';
        }

        if (str_contains(strtolower($message), 'proc_open')) {
            return 'This server blocks subprocess mail. PHP mail() fallback was used — if it still fails, switch to SMTP in Email Setup.';
        }

        if (str_contains(strtolower($message), 'connection could not be established')) {
            return 'Could not connect to the SMTP server. Check host, port, firewall, and that outbound mail is allowed on your host.';
        }

        if (str_contains(strtolower($message), 'authentication')) {
            return 'SMTP authentication failed. Check username and password in Email Setup.';
        }

        if (str_contains(strtolower($message), 'timed out') || str_contains(strtolower($message), 'timeout')) {
            return 'Mail delivery timed out. For PHP mail, check with your host. For SMTP, check host, port, and encryption.';
        }

        if (str_contains(strtolower($message), 'sendmail') || str_contains(strtolower($message), 'php mail')) {
            return $message;
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

    /**
     * @return list<string>
     */
    public static function defaultSendmailCommands(): array
    {
        return [
            '/usr/sbin/sendmail -t -i',
            '/bin/sendmail -t -i',
            '/usr/sbin/sendmail -bs -i',
            '/bin/sendmail -bs -i',
        ];
    }

    public static function detectSendmailCommand(): ?string
    {
        foreach (static::defaultSendmailCommands() as $command) {
            $binary = static::sendmailBinary($command);

            if ($binary !== null && $binary !== '' && file_exists($binary)) {
                return $command;
            }
        }

        return null;
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

    private static function deliverViaSendmail(
        string $to,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName,
        ?string $replyToAddress = null,
        ?string $replyToName = null,
    ): void {
        if (static::canUsePhpMail()) {
            static::deliverViaPhpMail($to, $subject, $body, $fromAddress, $fromName, $replyToAddress, $replyToName);

            return;
        }

        if (static::canUseSubprocess()) {
            Mail::mailer('sendmail')->raw(
                $body,
                function ($message) use ($to, $subject, $fromAddress, $fromName, $replyToAddress, $replyToName): void {
                    $message
                        ->to($to)
                        ->subject($subject)
                        ->from($fromAddress, $fromName !== '' ? $fromName : null);

                    if ($replyToAddress) {
                        $message->replyTo($replyToAddress, $replyToName !== '' ? $replyToName : null);
                    }
                },
            );

            return;
        }

        throw new \RuntimeException('Neither PHP mail() nor sendmail subprocess is available on this server. Use SMTP in Email Setup.');
    }

    private static function deliverViaPhpMail(
        string $to,
        string $subject,
        string $body,
        string $fromAddress,
        string $fromName,
        ?string $replyToAddress = null,
        ?string $replyToName = null,
    ): void {
        if (! static::canUsePhpMail()) {
            throw new \RuntimeException('PHP mail() is not available on this server.');
        }

        $fromHeader = $fromName !== ''
            ? static::encodeHeaderValue($fromName).' <'.$fromAddress.'>'
            : $fromAddress;

        $replyTo = $replyToAddress ?: $fromAddress;
        $replyToHeader = $replyToName !== null && $replyToName !== ''
            ? static::encodeHeaderValue($replyToName).' <'.$replyTo.'>'
            : $replyTo;

        $headers = implode("\r\n", [
            'From: '.$fromHeader,
            'Reply-To: '.$replyToHeader,
            'MIME-Version: 1.0',
            'Content-Type: text/plain; charset=UTF-8',
            'Content-Transfer-Encoding: 8bit',
            'X-Mailer: STECI-UK-Parish',
        ]);

        $parameters = sprintf('-f%s', $fromAddress);

        $sent = @mail($to, static::encodeHeaderValue($subject), $body, $headers, $parameters);

        if (! $sent) {
            throw new \RuntimeException('PHP mail() could not send the message. Your host may require SMTP instead of PHP mail.');
        }
    }

    private static function canUsePhpMail(): bool
    {
        if (! function_exists('mail')) {
            return false;
        }

        $disabled = array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions'))));

        return ! in_array('mail', $disabled, true);
    }

    private static function canUseSubprocess(): bool
    {
        if (! function_exists('proc_open')) {
            return false;
        }

        $disabled = array_filter(array_map('trim', explode(',', (string) ini_get('disable_functions'))));

        return ! in_array('proc_open', $disabled, true);
    }

    private static function validateSendmailPath(?string $command = null): ?string
    {
        $command = trim($command ?? static::resolveSendmailPath());

        if ($command === '') {
            $command = static::detectSendmailCommand() ?? '';
        }

        if ($command === '') {
            return 'PHP mail() is not available and no sendmail binary was found. Use SMTP in Email Setup or ask your host to enable PHP mail.';
        }

        $binary = static::sendmailBinary($command);

        if ($binary === null || $binary === '') {
            return 'Sendmail command is invalid. Example: /usr/sbin/sendmail -t -i';
        }

        if (! file_exists($binary)) {
            return "Sendmail binary not found at {$binary}. Try /usr/sbin/sendmail -t -i or use SMTP.";
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
            $path = static::detectSendmailCommand() ?? '/usr/sbin/sendmail -t -i';
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

    private static function encodeHeaderValue(string $value): string
    {
        if ($value === '') {
            return $value;
        }

        if (str_contains($value, "\r") || str_contains($value, "\n")) {
            return function_exists('mb_encode_mimeheader')
                ? mb_encode_mimeheader($value, 'UTF-8')
                : $value;
        }

        if (function_exists('mb_check_encoding') && ! mb_check_encoding($value, 'ASCII')) {
            return function_exists('mb_encode_mimeheader')
                ? mb_encode_mimeheader($value, 'UTF-8')
                : $value;
        }

        return $value;
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
