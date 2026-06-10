<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\User;
use App\Support\GdprConfig;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DataProtectionService
{
    /**
     * @return array<string, mixed>
     */
    public function exportPersonalData(User $user): array
    {
        $user->loadMissing(['family', 'donations']);

        return [
            'exported_at' => now()->toIso8601String(),
            'privacy_policy_version' => GdprConfig::privacyPolicyVersion(),
            'data_controller' => 'St. Thomas Evangelical Church of India – UK Parish (Charity 1143030)',
            'profile' => [
                'first_name' => $user->displayFirstName(),
                'last_name' => $user->displayLastName(),
                'full_name' => $user->displayFullName(),
                'pronouns' => $user->formattedPronouns(),
                'email' => $user->email,
                'phone' => $user->phone,
                'date_of_birth' => $user->date_of_birth?->format('Y-m-d'),
                'role' => $user->roleSlug(),
                'account_status' => $user->accountStatus()->value,
                'address' => [
                    'line_1' => $user->address_line_1,
                    'line_2' => $user->address_line_2,
                    'city' => $user->city,
                    'county' => $user->county,
                    'postcode' => $user->postcode,
                ],
                'preferred_worship_location' => $user->preferred_worship_location,
                'family' => $user->family?->only(['id', 'name']),
                'family_relationship' => $user->family_relationship,
                'is_family_admin' => $user->is_family_admin,
            ],
            'consents' => [
                'privacy_policy_accepted_at' => $user->privacy_policy_accepted_at?->toIso8601String(),
                'privacy_policy_version' => $user->privacy_policy_version,
                'terms_accepted_at' => $user->terms_accepted_at?->toIso8601String(),
                'household_data_consent_at' => $user->household_data_consent_at?->toIso8601String(),
                'marketing_consent' => (bool) $user->marketing_consent,
                'marketing_consent_at' => $user->marketing_consent_at?->toIso8601String(),
            ],
            'donations' => $user->donations
                ->map(fn (Donation $donation): array => [
                    'amount' => (float) $donation->amount,
                    'currency' => $donation->currency,
                    'method' => $donation->method,
                    'status' => $donation->status,
                    'donated_on' => $donation->donated_on?->format('Y-m-d'),
                    'reference' => $donation->reference,
                    'member_note' => $donation->member_note,
                    'created_at' => $donation->created_at?->toIso8601String(),
                ])
                ->values()
                ->all(),
        ];
    }

    public function requestErasure(User $user): User
    {
        abort_if($user->anonymized_at !== null, 422, 'This account has already been anonymised.');

        $user->update(['erasure_requested_at' => now()]);

        SecurityLogger::audit(
            'gdpr_erasure_requested',
            actor: $user,
            subject: $user,
            context: [
                'target_user_id' => $user->id,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $user->fresh();
    }

    public function anonymizeUser(User $user, User $actor): User
    {
        abort_unless($actor->can('update', $user), 403);

        return DB::transaction(function () use ($user, $actor): User {
            $user->clearMediaCollection('profile_photo');

            $placeholderEmail = 'deleted-user-'.$user->id.'@anonymized.steciuk.local';

            User::withoutEvents(function () use ($user, $placeholderEmail): void {
                $user->update([
                    'name' => 'Deleted parish account #'.$user->id,
                    'first_name' => 'Deleted',
                    'last_name' => 'Account',
                    'pronouns' => null,
                    'email' => $user->isMember() ? $placeholderEmail : $user->email,
                    'phone' => null,
                    'date_of_birth' => null,
                    'address_line_1' => null,
                    'address_line_2' => null,
                    'city' => null,
                    'county' => null,
                    'postcode' => null,
                    'preferred_worship_location' => null,
                    'password' => Hash::make(Str::password(32)),
                    'is_active' => false,
                    'is_family_admin' => false,
                    'family_relationship' => null,
                    'marketing_consent' => false,
                    'marketing_consent_at' => null,
                    'anonymized_at' => now(),
                    'erasure_requested_at' => $user->erasure_requested_at ?? now(),
                ]);
            });

            if ($user->family && (int) $user->family->admin_user_id === (int) $user->id) {
                $user->family->update(['admin_user_id' => null]);
            }

            $user->update(['family_id' => null]);

            Donation::query()
                ->where('user_id', $user->id)
                ->update(['member_note' => null]);

            SecurityLogger::audit(
                'gdpr_user_anonymized',
                actor: $actor,
                subject: $user,
                context: [
                    'target_user_id' => $user->id,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );

            return $user->fresh();
        });
    }

    /**
     * @param  array<string, mixed>  $consents
     */
    public function recordRegistrationConsents(User $user, array $consents): void
    {
        $user->update([
            'privacy_policy_accepted_at' => now(),
            'privacy_policy_version' => GdprConfig::privacyPolicyVersion(),
            'terms_accepted_at' => now(),
            'household_data_consent_at' => ($consents['household_data_consent'] ?? false) ? now() : null,
            'marketing_consent' => (bool) ($consents['marketing_consent'] ?? false),
            'marketing_consent_at' => ($consents['marketing_consent'] ?? false) ? now() : null,
        ]);
    }

    public function recordHouseholdDataConsent(User $user): void
    {
        if ($user->household_data_consent_at === null) {
            $user->update(['household_data_consent_at' => now()]);
        }
    }
}
