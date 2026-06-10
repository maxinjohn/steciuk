<?php

namespace App\Support;

use App\Models\Donation;
use App\Models\Family;
use App\Models\User;

class SecurityAuditCatalog
{
    /**
     * @var array<string, string>
     */
    private const LABELS = [
        'user_login' => 'Admin sign-in',
        'user_logout' => 'Admin sign-out',
        'member_login' => 'Member sign-in',
        'member_logout' => 'Member sign-out',
        'login_failed' => 'Failed sign-in attempt',
        'login_panel_denied' => 'Admin access denied',
        'login_rate_limited' => 'Sign-in rate limited',
        'admin_session_expired' => 'Admin session expired',
        'member_registered' => 'Member registration submitted',
        'member_account_approved' => 'Member account approved',
        'member_account_rejected' => 'Member account rejected',
        'member_account_pending' => 'Member account set to pending',
        'member_account_deactivated' => 'Member account deactivated',
        'member_account_activated' => 'Member account activated',
        'user_role_updated' => 'User role changed',
        'user_deleted' => 'User account deleted',
        'family_created' => 'Family household created',
        'family_deleted' => 'Family household deleted',
        'family_deactivated' => 'Family household deactivated',
        'family_activated' => 'Family household activated',
        'family_admin_updated' => 'Family administrator changed',
        'family_member_created_by_admin' => 'Household member created (admin)',
        'household_member_added' => 'Household member added (portal)',
        'household_member_removed' => 'Household member removed',
        'household_member_email_updated' => 'Household member email updated',
        'user_linked_to_family' => 'User linked to family',
        'user_unlinked_from_family' => 'User unlinked from family',
        'donation_submitted' => 'Donation reported by member',
        'donation_approved' => 'Donation approved',
        'donation_rejected' => 'Donation rejected',
        'donation_pending' => 'Donation returned to pending',
        'donation_recorded' => 'Donation recorded manually',
        'donation_deleted' => 'Donation deleted',
        'donation_report_exported' => 'Giving statement exported',
        'profile_updated' => 'Member profile updated',
        'password_changed' => 'Password changed',
        'user_password_set_by_admin' => 'Password set by admin',
        'user_password_reset_link_sent' => 'Password reset link sent',
        'profile_photo_updated' => 'Profile photo updated',
        'gdpr_data_exported' => 'Personal data exported',
        'gdpr_erasure_requested' => 'Account deletion requested',
        'gdpr_user_anonymized' => 'User account anonymised',
        'gdpr_marketing_consent_updated' => 'Marketing consent updated',
        'role_created' => 'Role created',
        'role_permissions_updated' => 'Role permissions updated',
        'admin_record_created' => 'Admin record created',
        'admin_record_updated' => 'Admin record updated',
        'settings_updated' => 'Site settings updated',
        'form_submission' => 'Public form submitted',
        'honeypot_triggered' => 'Honeypot triggered',
        'form_rate_limited' => 'Form rate limited',
        'livewire_rate_limited' => 'Livewire rate limited',
        'blocked_suspicious_request' => 'Suspicious request blocked',
        'security_audit_log_purged' => 'Activity log cleaned',
    ];

    public static function label(string $action): string
    {
        return self::LABELS[$action] ?? str($action)->headline()->toString();
    }

    /**
     * @return list<string>
     */
    public static function actionOptions(): array
    {
        $options = [];

        foreach (self::LABELS as $key => $label) {
            $options[$key] = $label;
        }

        asort($options);

        return $options;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function summarize(string $action, array $context = []): string
    {
        $actor = self::actorPhrase($context);
        $target = self::targetPhrase($context);
        $subject = self::subjectPhrase($context);
        $portal = isset($context['portal']) ? (string) $context['portal'] : null;
        $scope = self::string($context, 'scope', 'giving');

        return match ($action) {
            'user_login' => "{$actor} signed in to the ".self::string($context, 'portal', 'website').'.',
            'user_logout' => "{$actor} signed out of the ".self::string($context, 'portal', 'website').'.',
            'member_login' => "{$actor} signed in to the member portal.",
            'member_logout' => "{$actor} signed out of the member portal.",
            'login_failed' => 'Failed sign-in attempt for '.self::string($context, 'email', 'unknown email').'.',
            'login_panel_denied' => self::string($context, 'target_user_name', 'A user').' authenticated but was denied admin panel access.',
            'login_rate_limited', 'form_rate_limited', 'livewire_rate_limited' => 'Rate limit triggered on '.self::string($context, 'route', 'a route').'.',
            'admin_session_expired' => "{$actor} admin session expired due to inactivity.",
            'member_registered' => self::string($context, 'target_user_name', 'A person').' submitted a '.($context['family'] ?? false ? 'family' : 'individual').' registration'.self::via($context).'.',
            'member_account_approved' => "{$actor} approved {$target}.",
            'member_account_rejected' => "{$actor} rejected {$target}.",
            'member_account_pending' => "{$actor} set {$target} to pending approval.",
            'member_account_deactivated' => "{$actor} deactivated {$target}.",
            'member_account_activated' => "{$actor} reactivated {$target}.",
            'user_role_updated' => "{$actor} changed {$target} role to ".self::string($context, 'role', 'unknown').'.',
            'user_deleted' => "{$actor} deleted {$target}.",
            'family_created' => "{$actor} created family household {$subject}.",
            'family_deleted' => "{$actor} deleted family household {$subject}.",
            'family_deactivated' => "{$actor} deactivated family household {$subject}.",
            'family_activated' => "{$actor} reactivated family household {$subject}.",
            'family_admin_updated' => "{$actor} set {$target} as administrator of {$subject}.",
            'family_member_created_by_admin' => "{$actor} created household member {$target} in {$subject}.",
            'household_member_added' => "{$actor} added household member {$target} via the member portal.",
            'household_member_removed' => "{$actor} removed {$target} from a household".(($context['deleted_profile'] ?? false) ? ' and deleted the pending profile' : '').'.',
            'household_member_email_updated' => "{$actor} updated email for {$target}.",
            'user_linked_to_family' => "{$actor} linked {$target} to {$subject} as ".self::string($context, 'relationship', 'member').'.',
            'user_unlinked_from_family' => "{$actor} unlinked {$target} from their household.",
            'donation_submitted' => "{$actor} reported a donation of ".self::string($context, 'amount', 'unknown amount').'.',
            'donation_approved' => "{$actor} approved donation ".self::string($context, 'amount', '')." for {$target}.",
            'donation_rejected' => "{$actor} rejected donation ".self::string($context, 'amount', '')." for {$target}.",
            'donation_pending' => "{$actor} returned donation ".self::string($context, 'amount', '')." for {$target} to pending verification.",
            'donation_recorded' => "{$actor} manually recorded donation ".self::string($context, 'amount', '')." for {$target}.",
            'donation_deleted' => "{$actor} deleted donation record #".self::string($context, 'donation_id', '?').'.',
            'donation_report_exported' => "{$actor} exported a {$scope} giving PDF for ".self::string($context, 'period', 'the selected period').'.',
            'profile_updated' => "{$actor} updated their member profile.",
            'password_changed' => "{$actor} changed their account password.",
            'profile_photo_updated' => "{$actor} ".self::string($context, 'change', 'updated').' their profile photo.',
            'gdpr_data_exported' => "{$actor} downloaded a copy of their personal data.",
            'gdpr_erasure_requested' => "{$actor} requested deletion of their parish account data.",
            'gdpr_user_anonymized' => "{$actor} anonymised {$target} for GDPR erasure.",
            'gdpr_marketing_consent_updated' => "{$actor} ".(($context['marketing_consent'] ?? false) ? 'opted in to' : 'opted out of').' marketing emails.',
            'role_created' => "{$actor} created role ".self::string($context, 'role', 'unknown').'.',
            'role_permissions_updated' => "{$actor} updated role permissions for ".self::string($context, 'role', 'a role').'.',
            'admin_record_created' => "{$actor} created ".self::resourcePhrase($context).' in the admin panel.',
            'admin_record_updated' => "{$actor} updated ".self::resourcePhrase($context).' in the admin panel.',
            'settings_updated' => "{$actor} saved ".self::string($context, 'settings_page', 'site settings').' in the admin panel.',
            'form_submission' => 'Public '.self::string($context, 'form', 'form').' submitted'.self::via($context).'.',
            'honeypot_triggered' => 'Honeypot field filled on '.self::string($context, 'type', 'a form').self::via($context).'.',
            'blocked_suspicious_request' => 'Blocked suspicious request to '.self::string($context, 'path', 'unknown path').'.',
            'security_audit_log_purged' => "{$actor} cleaned the activity log, removing ".self::string($context, 'purged_count', '0').' entries on or before '.self::string($context, 'before_date', 'the selected date').'.',
            default => "{$actor} performed ".self::label($action).'.',
        };
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function enrichContext(array $context): array
    {
        if (isset($context['target_user_id']) && ! isset($context['target_user_name'])) {
            $user = User::query()->find($context['target_user_id']);

            if ($user) {
                $context['target_user_name'] = $user->displayFullName();
                $context['target_user_email'] = $user->email;
                $context['target_user_role'] = $user->roleSlug();
            }
        }

        if (isset($context['family_id']) && ! isset($context['family_name'])) {
            $family = Family::query()->find($context['family_id']);

            if ($family) {
                $context['family_name'] = $family->name;
            }
        }

        if (isset($context['donation_id']) && ! isset($context['amount'])) {
            $donation = Donation::query()->find($context['donation_id']);

            if ($donation) {
                $context['amount'] = $donation->formattedAmount();
                $context['target_user_id'] ??= $donation->user_id;
            }
        }

        return $context;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function actorPhrase(array $context): string
    {
        if (filled($context['actor_name'] ?? null)) {
            $email = filled($context['actor_email'] ?? null) ? ' ('.$context['actor_email'].')' : '';

            return $context['actor_name'].$email;
        }

        return 'Someone';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function targetPhrase(array $context): string
    {
        $name = self::string($context, 'target_user_name', 'a user');
        $email = filled($context['target_user_email'] ?? null) ? ' ('.$context['target_user_email'].')' : '';

        return $name.$email;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function subjectPhrase(array $context): string
    {
        if (filled($context['family_name'] ?? null)) {
            return '"'.$context['family_name'].'"';
        }

        if (filled($context['subject_label'] ?? null)) {
            return '"'.$context['subject_label'].'"';
        }

        return 'a household';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function resourcePhrase(array $context): string
    {
        $label = self::string($context, 'subject_label', '');

        if ($label !== '') {
            return $label;
        }

        $resource = self::string($context, 'resource', 'record');
        $id = self::string($context, 'resource_id', '');

        return $id !== '' ? "{$resource} #{$id}" : $resource;
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function via(array $context): string
    {
        $ip = self::string($context, 'ip', '');

        return $ip !== '' ? " from {$ip}" : '';
    }

    /**
     * @param  array<string, mixed>  $context
     */
    private static function string(array $context, string $key, string $default = ''): string
    {
        $value = $context[$key] ?? $default;

        return is_scalar($value) ? trim((string) $value) : $default;
    }
}
