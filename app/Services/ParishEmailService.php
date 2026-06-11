<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;

class ParishEmailService
{
    public const STORAGE_KEY = 'parish_email_templates';

    public const ACCOUNT_APPROVED = 'account_approved';

    public const ACCOUNT_ADDED_BY_ADMIN = 'account_added_by_admin';

    public const FAMILY_MEMBER_ADDED = 'family_member_added';

    public const FAMILY_REQUEST_APPROVED = 'family_request_approved';

    /**
     * @return array<string, array{label: string, subject: string, body: string, placeholders: list<string>}>
     */
    public function defaultTemplates(): array
    {
        $site = config('site.name', 'STECI UK Parish');

        return [
            self::ACCOUNT_APPROVED => [
                'label' => 'Member account approved',
                'subject' => 'Your {site_name} member account is approved',
                'body' => "Dear {first_name},\n\nYour parish member account has been approved. You can now sign in to the member portal at {login_url}.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{site_name}', '{login_url}'],
            ],
            self::ACCOUNT_ADDED_BY_ADMIN => [
                'label' => 'Account created by parish admin',
                'subject' => 'Your {site_name} parish account',
                'body' => "Dear {first_name},\n\nA parish administrator has created a member account for you on {site_name}. Sign in at {login_url} using the email address {email}.\n\nIf you did not expect this message, please contact the parish office.\n\n{site_name}",
                'placeholders' => ['{first_name}', '{site_name}', '{login_url}', '{email}'],
            ],
            self::FAMILY_MEMBER_ADDED => [
                'label' => 'Added to a parish family',
                'subject' => 'You have been linked to the {family_name} household',
                'body' => "Dear {first_name},\n\nYou have been added to the {family_name} household on {site_name}. Sign in at {login_url} to view your family profile and shared giving.\n\n{site_name}",
                'placeholders' => ['{first_name}', '{family_name}', '{site_name}', '{login_url}'],
            ],
            self::FAMILY_REQUEST_APPROVED => [
                'label' => 'Family member request approved',
                'subject' => 'Your family member request was approved',
                'body' => "Dear {first_name},\n\nYour request to add {member_name} to the {family_name} household was approved by {approver_name}.\n\nSign in at {login_url} to view your household.\n\n{site_name}",
                'placeholders' => ['{first_name}', '{member_name}', '{family_name}', '{approver_name}', '{site_name}', '{login_url}'],
            ],
        ];
    }

    /**
     * @return array{subject: string, body: string}
     */
    public function resolve(string $key): array
    {
        $defaults = $this->defaultTemplates();
        $stored = $this->storedTemplates();
        $template = array_merge($defaults[$key] ?? [], $stored[$key] ?? []);

        return [
            'subject' => (string) ($template['subject'] ?? ''),
            'body' => (string) ($template['body'] ?? ''),
        ];
    }

    /**
     * @param  array<string, string>  $replacements
     */
    public function send(string $key, string $recipient, array $replacements = []): void
    {
        if (! filled($recipient)) {
            return;
        }

        $template = $this->resolve($key);
        $merged = array_merge($this->baseReplacements(), $replacements);

        try {
            MailConfigService::applyFromSettings();
            MailConfigService::deliverPlainTextMessage(
                $recipient,
                $this->replace($template['subject'], $merged),
                $this->replace($template['body'], $merged),
            );
        } catch (\Throwable) {
            // Outbound mail failures must not block admin workflows.
        }
    }

    public function sendAccountApproved(User $user): void
    {
        if (! filled($user->email)) {
            return;
        }

        $this->send(self::ACCOUNT_APPROVED, $user->email, [
            '{first_name}' => $user->first_name ?: $user->name ?: 'Member',
        ]);
    }

    /**
     * @param  array<string, array{subject?: string, body?: string}>  $templates
     */
    public function saveTemplates(array $templates): void
    {
        $existing = $this->storedTemplates();
        Setting::set(self::STORAGE_KEY, array_merge($existing, $templates), 'mail');
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    public function allTemplates(): array
    {
        $defaults = $this->defaultTemplates();
        $stored = $this->storedTemplates();

        return collect($defaults)
            ->map(fn (array $template, string $key): array => array_merge($template, $stored[$key] ?? []))
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function baseReplacements(): array
    {
        return [
            '{site_name}' => (string) config('site.name', 'STECI UK Parish'),
            '{login_url}' => route('login'),
        ];
    }

    /**
     * @param  array<string, string>  $replacements
     */
    private function replace(string $text, array $replacements): string
    {
        return strtr($text, $replacements);
    }

    /**
     * @return array<string, array<string, mixed>>
     */
    private function storedTemplates(): array
    {
        $stored = Setting::get(self::STORAGE_KEY);
        $decoded = is_string($stored) ? json_decode($stored, true) : $stored;

        return is_array($decoded) ? $decoded : [];
    }
}
