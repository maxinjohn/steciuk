<?php

namespace App\Services;

use App\Models\Setting;
use App\Models\User;
use App\Models\Family;

class ParishEmailService
{
    public const STORAGE_KEY = 'parish_email_templates';

    public const ACCOUNT_APPROVED = 'account_approved';

    public const ACCOUNT_ADDED_BY_ADMIN = 'account_added_by_admin';

    public const ACCOUNT_REJECTED = 'account_rejected';

    public const FAMILY_MEMBER_ADDED = 'family_member_added';

    public const FAMILY_REQUEST_APPROVED = 'family_request_approved';

    public const PARISH_WELCOME = 'parish_welcome';

    /**
     * @return array<string, array{label: string, subject: string, body: string, placeholders: list<string>}>
     */
    public function defaultTemplates(): array
    {
        return [
            self::ACCOUNT_APPROVED => [
                'label' => 'Member account approved',
                'subject' => 'Your {site_name} member account is approved',
                'body' => "Dear {first_name},\n\nGood news — your parish member account has been approved.\n\nYou can now sign in at {login_url} using the email address we hold for you.\n\nOnce signed in you can update your profile, view household members, and report giving from your account.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{site_name}', '{login_url}'],
            ],
            self::ACCOUNT_ADDED_BY_ADMIN => [
                'label' => 'Account created by parish admin',
                'subject' => 'Your {site_name} parish account is ready',
                'body' => "Dear {first_name},\n\nA parish administrator has created a member account for you on {site_name}.\n\nSign in at {login_url} using {email}.\n\nIf you did not expect this message, please contact the parish office.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{site_name}', '{login_url}', '{email}'],
            ],
            self::ACCOUNT_REJECTED => [
                'label' => 'Registration not approved',
                'subject' => 'Update on your {site_name} registration',
                'body' => "Dear {first_name},\n\nThank you for your interest in joining {site_name} online.\n\nUnfortunately we were unable to approve your registration at this time. If you believe this is a mistake, please contact the parish office.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{site_name}'],
            ],
            self::FAMILY_MEMBER_ADDED => [
                'label' => 'Added to a parish family',
                'subject' => 'You have been linked to the {family_name} household',
                'body' => "Dear {first_name},\n\nYou have been added to the {family_name} household on {site_name}.\n\nSign in at {login_url} to view your family profile and shared giving once your account is active.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{family_name}', '{site_name}', '{login_url}'],
            ],
            self::FAMILY_REQUEST_APPROVED => [
                'label' => 'Family member request approved',
                'subject' => 'Your family member request was approved',
                'body' => "Dear {first_name},\n\nYour request to add {member_name} to the {family_name} household was approved by {approver_name}.\n\nSign in at {login_url} to view your household.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{member_name}', '{family_name}', '{approver_name}', '{site_name}', '{login_url}'],
            ],
            self::PARISH_WELCOME => [
                'label' => 'Welcome after registration submitted',
                'subject' => 'We received your {site_name} registration',
                'body' => "Dear {first_name},\n\nThank you for registering with {site_name}.\n\nOur parish leadership team will review your details shortly. You will receive another email once your account is approved and you can sign in.\n\nWith blessings,\n{site_name}",
                'placeholders' => ['{first_name}', '{site_name}'],
            ],
        ];
    }

    public function seedDefaultsIfMissing(): void
    {
        $stored = $this->storedTemplates();
        $needsSave = false;
        $merged = [];

        foreach ($this->defaultTemplates() as $key => $defaults) {
            $saved = $stored[$key] ?? [];
            $subject = filled($saved['subject'] ?? null) ? $saved['subject'] : $defaults['subject'];
            $body = filled($saved['body'] ?? null) ? $saved['body'] : $defaults['body'];

            if (($saved['subject'] ?? null) !== $subject || ($saved['body'] ?? null) !== $body) {
                $needsSave = true;
            }

            $merged[$key] = [
                'subject' => $subject,
                'body' => $body,
            ];
        }

        if ($stored === [] || $needsSave) {
            $this->saveTemplates($merged);
        }
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
            'subject' => filled($template['subject'] ?? null)
                ? (string) $template['subject']
                : (string) ($defaults[$key]['subject'] ?? ''),
            'body' => filled($template['body'] ?? null)
                ? (string) $template['body']
                : (string) ($defaults[$key]['body'] ?? ''),
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

    public function sendRegistrationWelcome(User $user): void
    {
        if (! filled($user->email)) {
            return;
        }

        $this->send(self::PARISH_WELCOME, $user->email, [
            '{first_name}' => $user->first_name ?: $user->name ?: 'Member',
        ]);
    }

    public function sendAdminCreatedAccount(User $user): void
    {
        if (! filled($user->email)) {
            return;
        }

        $this->send(self::ACCOUNT_ADDED_BY_ADMIN, $user->email, [
            '{first_name}' => $user->first_name ?: $user->name ?: 'Member',
            '{email}' => (string) $user->email,
        ]);
    }

    public function sendFamilyMemberAdded(User $member, Family $family): void
    {
        if (! filled($member->email)) {
            return;
        }

        $this->send(self::FAMILY_MEMBER_ADDED, $member->email, [
            '{first_name}' => $member->first_name ?: $member->name ?: 'Member',
            '{family_name}' => $family->name,
        ]);
    }

    public function sendFamilyRequestApproved(User $requester, User $member, Family $family, User $approver): void
    {
        if (! filled($requester->email)) {
            return;
        }

        $this->send(self::FAMILY_REQUEST_APPROVED, $requester->email, [
            '{first_name}' => $requester->first_name ?: $requester->name ?: 'Member',
            '{member_name}' => $member->displayFullName(),
            '{family_name}' => $family->name,
            '{approver_name}' => $approver->displayFullName(),
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
            ->map(function (array $template, string $key) use ($stored): array {
                $saved = $stored[$key] ?? [];

                return array_merge($template, [
                    'subject' => filled($saved['subject'] ?? null) ? $saved['subject'] : $template['subject'],
                    'body' => filled($saved['body'] ?? null) ? $saved['body'] : $template['body'],
                ]);
            })
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
