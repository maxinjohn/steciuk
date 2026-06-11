<?php

namespace App\Services;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
use App\Enums\UserRole;
use App\Models\Family;
use App\Models\User;
use App\Support\UserName;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class MemberRegistrationService
{
    public static function defaultFamilyName(string $personName): string
    {
        $personName = trim($personName);

        if ($personName === '') {
            return 'Parish household';
        }

        $parts = preg_split('/\s+/u', $personName) ?: [];
        $surname = trim((string) (array_pop($parts) ?: $personName));

        return $surname.' family';
    }

    public function assertEmailAvailable(string $email): void
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            return;
        }

        $existing = User::query()->where('email', $normalized)->first();

        if (! $existing) {
            return;
        }

        if ($existing->isLinkedHouseholdMember()) {
            throw ValidationException::withMessages([
                'email' => $existing->householdMemberRegistrationMessage(),
            ]);
        }

        if ($existing->isAccountPending()) {
            throw ValidationException::withMessages([
                'email' => 'A registration with this email address is already awaiting parish approval.',
            ]);
        }

        throw ValidationException::withMessages([
            'email' => 'An account with this email address already exists. Please sign in instead.',
        ]);
    }

    /**
     * @param  array<string, mixed>  $primary
     * @param  list<array<string, mixed>>  $householdMembers
     */
    public function register(array $primary, ?string $familyName = null, array $householdMembers = [], array $consents = []): User
    {
        $email = strtolower(trim((string) ($primary['email'] ?? '')));
        $this->assertEmailAvailable($email);

        foreach ($householdMembers as $index => $member) {
            $memberEmail = strtolower(trim((string) ($member['email'] ?? '')));

            if ($memberEmail === '' || $memberEmail === $email) {
                continue;
            }

            try {
                $this->assertEmailAvailable($memberEmail);
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages([
                    "householdMembers.{$index}.email" => $exception->errors()['email'][0] ?? 'This email is already registered.',
                ]);
            }
        }

        if ($familyName !== null) {
            $familyName = trim($familyName);

            if ($familyName === '') {
                $personName = UserName::fromParts($primary['first_name'] ?? null, $primary['last_name'] ?? null)
                    ?: trim((string) ($primary['name'] ?? ''));
                $familyName = self::defaultFamilyName($personName);
            }
        }

        return DB::transaction(function () use ($primary, $familyName, $householdMembers, $email, $consents): User {
            $family = null;

            if ($familyName !== null) {
                $family = Family::query()->create([
                    'name' => $familyName,
                    'preferred_worship_location' => $primary['preferred_worship_location'] ?? null,
                ]);
            }

            $head = $this->createMemberUser(array_merge($primary, [
                'email' => $email,
                'family_id' => $family?->id,
                'is_family_admin' => $family !== null,
                'family_relationship' => $family ? FamilyRelationship::Head->value : null,
            ]));

            if ($family) {
                $family->update(['admin_user_id' => $head->id]);
            }

            foreach ($householdMembers as $member) {
                if (blank($member['name'] ?? null)) {
                    continue;
                }

                $memberEmail = strtolower(trim((string) ($member['email'] ?? '')));

                if ($memberEmail !== '' && $memberEmail === $email) {
                    continue;
                }

                if ($memberEmail !== '') {
                    $this->assertEmailAvailable($memberEmail);
                }

                $this->createMemberUser([
                    'name' => trim((string) $member['name']),
                    'email' => $memberEmail !== '' ? $memberEmail : null,
                    'phone' => $member['phone'] ?? null,
                    'date_of_birth' => $member['date_of_birth'] ?? null,
                    'preferred_worship_location' => $primary['preferred_worship_location'] ?? null,
                    'family_id' => $family?->id,
                    'is_family_admin' => false,
                    'family_relationship' => $member['relationship'] ?? FamilyRelationship::Other->value,
                    'address_line_1' => $primary['address_line_1'] ?? null,
                    'address_line_2' => $primary['address_line_2'] ?? null,
                    'city' => $primary['city'] ?? null,
                    'county' => $primary['county'] ?? null,
                    'postcode' => $primary['postcode'] ?? null,
                    'password' => Str::password(24),
                ]);
            }

            app(DataProtectionService::class)->recordRegistrationConsents($head, $consents);

            SecurityLogger::audit(
                'member_registered',
                actor: $head,
                subject: $family,
                context: [
                    'target_user_id' => $head->id,
                    'target_user_name' => $head->displayFullName(),
                    'target_user_email' => $head->email,
                    'family' => $family !== null,
                    'family_id' => $family?->id,
                    'family_name' => $family?->name,
                    'household_member_count' => count($householdMembers),
                    'portal' => SecurityLogger::detectPortal(),
                    'ip' => request()->ip(),
                ],
            );

            return $head;
        });
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function addFamilyMember(User $admin, array $data): User
    {
        abort_unless($admin->canManageHouseholdOnPortal(), 403);

        $relationship = FamilyRelationship::tryFromValue($data['relationship'] ?? null) ?? FamilyRelationship::Other;

        abort_if($relationship === FamilyRelationship::Head, 422, 'The head of household is managed through the primary account.');

        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($email !== '') {
            $this->assertEmailAvailable($email);
        }

        $person = UserName::normalize([
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'name' => $data['name'] ?? '',
        ]);

        $member = $this->createMemberUser([
            'name' => $person['name'],
            'first_name' => $person['first_name'],
            'last_name' => $person['last_name'],
            'pronouns' => $data['pronouns'] ?? null,
            'gender' => $data['gender'] ?? null,
            'email' => $email !== '' ? $email : null,
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'preferred_worship_location' => $admin->preferred_worship_location,
            'family_id' => $admin->family_id,
            'is_family_admin' => false,
            'family_relationship' => $relationship->value,
            'address_line_1' => $admin->address_line_1,
            'address_line_2' => $admin->address_line_2,
            'city' => $admin->city,
            'county' => $admin->county,
            'postcode' => $admin->postcode,
            'password' => Str::password(24),
        ]);

        SecurityLogger::audit(
            'household_member_added',
            actor: $admin,
            subject: $member,
            context: [
                'target_user_id' => $member->id,
                'family_id' => $admin->family_id,
                'relationship' => $relationship->value,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $member;
    }

    public function createHouseholdForMember(User $member, ?string $familyName = null): Family
    {
        abort_unless($member->canBelongToHousehold(), 403);
        abort_if($member->family_id !== null, 422, 'You are already linked to a household.');
        abort_unless($member->isAccountApproved() && $member->isActive(), 403);

        return DB::transaction(function () use ($member, $familyName): Family {
            $name = trim((string) ($familyName ?? ''));

            if ($name === '') {
                $name = self::defaultFamilyName($member->displayFullName());
            }

            $family = Family::query()->create([
                'name' => $name,
                'preferred_worship_location' => $member->preferred_worship_location,
                'admin_user_id' => $member->id,
            ]);

            $member->update([
                'family_id' => $family->id,
                'is_family_admin' => true,
                'family_relationship' => FamilyRelationship::Head->value,
            ]);

            SecurityLogger::audit(
                'family_created',
                actor: $member,
                subject: $family,
                context: [
                    'family_id' => $family->id,
                    'family_name' => $family->name,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );

            return $family;
        });
    }

    public function updateHouseholdMemberEmail(User $admin, User $member, ?string $email): User
    {
        abort_unless($admin->canManageHouseholdOnPortal(), 403);
        abort_unless($member->family_id === $admin->family_id, 403);
        abort_if($member->isFamilyAdmin(), 403, 'Update the head of household email from the profile tab.');
        abort_if($member->id === $admin->id, 403);

        $normalized = strtolower(trim((string) $email));
        $normalized = $normalized !== '' ? $normalized : null;
        $current = $member->email ? strtolower(trim((string) $member->email)) : null;

        if ($normalized === $current) {
            return $member;
        }

        if ($normalized !== null) {
            $this->assertEmailAvailable($normalized);
        }

        $member->update(['email' => $normalized]);

        if ($member->isAccountApproved()) {
            $this->setPending($member, $admin);
        }

        SecurityLogger::audit(
            'household_member_email_updated',
            actor: $admin,
            subject: $member,
            context: [
                'target_user_id' => $member->id,
                'target_user_email' => $normalized,
                'previous_email' => $current,
                'family_id' => $admin->family_id,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $member->fresh();
    }

    public function removeHouseholdMemberByFamilyAdmin(User $admin, User $member): void
    {
        abort_unless($admin->canManageHouseholdOnPortal(), 403);
        abort_unless((int) $member->family_id === (int) $admin->family_id, 403);
        abort_if($member->isFamilyAdmin(), 403, 'The household administrator cannot be removed here.');
        abort_if($member->id === $admin->id, 403, 'You cannot remove your own household profile.');

        DB::transaction(function () use ($admin, $member): void {
            $shouldDeleteProfile = $member->email === null
                && $member->isMember()
                && $member->isAccountPending();

            $this->detachUserFromFamily($member);

            if ($shouldDeleteProfile) {
                $member->delete();
            }

            SecurityLogger::audit(
                'household_member_removed',
                actor: $admin,
                subject: $member,
                context: [
                    'target_user_id' => $member->id,
                    'family_id' => $admin->family_id,
                    'deleted_profile' => $shouldDeleteProfile,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );
        });
    }

    public function approve(User $user, User $approver): void
    {
        $user->update([
            'account_status' => AccountStatus::Approved->value,
            'approved_at' => now(),
            'approved_by' => $approver->id,
            'email_verified_at' => $user->email_verified_at ?? now(),
        ]);

        SecurityLogger::audit(
            'member_account_approved',
            actor: $approver,
            subject: $user,
            context: [
                'target_user_id' => $user->id,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        app(ParishEmailService::class)->sendAccountApproved($user);
    }

    public function reject(User $user, User $approver): void
    {
        $user->update([
            'account_status' => AccountStatus::Rejected->value,
            'approved_at' => null,
            'approved_by' => $approver->id,
        ]);

        SecurityLogger::audit(
            'member_account_rejected',
            actor: $approver,
            subject: $user,
            context: [
                'target_user_id' => $user->id,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );
    }

    public function setPending(User $user, ?User $actor = null): void
    {
        $user->update([
            'account_status' => AccountStatus::Pending->value,
            'approved_at' => null,
            'approved_by' => null,
            'email_verified_at' => null,
        ]);

        if ($actor) {
            SecurityLogger::audit(
                'member_account_pending',
                actor: $actor,
                subject: $user,
                context: [
                    'target_user_id' => $user->id,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );
        }
    }

    public function deactivateUser(User $user, User $actor): void
    {
        abort_if($user->id === $actor->id, 403, 'You cannot deactivate your own account.');

        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            abort(403, 'Only a super admin can deactivate this account.');
        }

        $user->update(['is_active' => false]);

        SecurityLogger::audit(
            'member_account_deactivated',
            actor: $actor,
            subject: $user,
            context: ['target_user_id' => $user->id, 'portal' => SecurityLogger::detectPortal()],
        );
    }

    public function activateUser(User $user, User $actor): void
    {
        if ($user->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            abort(403, 'Only a super admin can activate this account.');
        }

        $user->update(['is_active' => true]);

        SecurityLogger::audit(
            'member_account_activated',
            actor: $actor,
            subject: $user,
            context: ['target_user_id' => $user->id, 'portal' => SecurityLogger::detectPortal()],
        );
    }

    public function deactivateFamily(Family $family, User $actor): void
    {
        $family->update(['is_active' => false]);

        SecurityLogger::audit(
            'family_deactivated',
            actor: $actor,
            subject: $family,
            context: ['family_id' => $family->id, 'portal' => SecurityLogger::detectPortal()],
        );
    }

    public function activateFamily(Family $family, User $actor): void
    {
        $family->update(['is_active' => true]);

        SecurityLogger::audit(
            'family_activated',
            actor: $actor,
            subject: $family,
            context: ['family_id' => $family->id, 'portal' => SecurityLogger::detectPortal()],
        );
    }

    /**
     * @param  array{name?: string|null, preferred_worship_location?: string|null, is_active?: bool, account_status?: string|null}  $data
     */
    public function adminCreateFamilyMember(User $actor, Family $family, array $data): User
    {
        abort_unless($actor->can('update', $family), 403);

        $relationship = FamilyRelationship::tryFromValue($data['relationship'] ?? null) ?? FamilyRelationship::Other;

        abort_if($relationship === FamilyRelationship::Head, 422, 'Use “Link existing member” or set a head of household when creating the family.');

        $email = strtolower(trim((string) ($data['email'] ?? '')));

        if ($email !== '') {
            $this->assertEmailAvailable($email);
        }

        $status = AccountStatus::tryFrom((string) ($data['account_status'] ?? '')) ?? AccountStatus::Pending;
        $approved = $status === AccountStatus::Approved;

        $person = UserName::normalize([
            'first_name' => $data['first_name'] ?? '',
            'last_name' => $data['last_name'] ?? '',
            'name' => $data['name'] ?? '',
        ]);

        $member = $this->createMemberUser([
            'name' => $person['name'],
            'first_name' => $person['first_name'],
            'last_name' => $person['last_name'],
            'pronouns' => $data['pronouns'] ?? null,
            'gender' => $data['gender'] ?? null,
            'email' => $email !== '' ? $email : null,
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'preferred_worship_location' => $data['preferred_worship_location'] ?? $family->preferred_worship_location,
            'family_id' => $family->id,
            'is_family_admin' => false,
            'family_relationship' => $relationship->value,
            'account_status' => $status->value,
            'approved_at' => $approved ? now() : null,
            'approved_by' => $approved ? $actor->id : null,
            'password' => Str::password(24),
        ]);

        SecurityLogger::audit(
            'family_member_created_by_admin',
            actor: $actor,
            subject: $member,
            context: [
                'family_id' => $family->id,
                'target_user_id' => $member->id,
                'relationship' => $relationship->value,
                'account_status' => $status->value,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $member;
    }

    public function createFamily(User $actor, array $data, ?User $head = null): Family
    {
        abort_unless($actor->can('create', Family::class), 403);

        $name = trim((string) ($data['name'] ?? ''));

        if ($name === '' && $head) {
            $name = self::defaultFamilyName(
                UserName::fromParts($head->displayFirstName(), $head->displayLastName()) ?: $head->name
            );
        } elseif ($name === '') {
            $name = 'Parish household';
        }

        return DB::transaction(function () use ($actor, $data, $head, $name): Family {
            $family = Family::query()->create([
                'name' => $name,
                'preferred_worship_location' => $data['preferred_worship_location'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ]);

            if ($head) {
                $this->assignUserToFamily(
                    $actor,
                    $head,
                    $family,
                    FamilyRelationship::Head,
                    makeFamilyAdmin: true,
                    forceMove: true,
                );
            }

            SecurityLogger::audit(
                'family_created',
                actor: $actor,
                subject: $family,
                context: [
                    'family_id' => $family->id,
                    'family_name' => $family->name,
                    'head_user_id' => $head?->id,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );

            return $family;
        });
    }

    public function assignUserToFamily(
        User $actor,
        User $member,
        Family $family,
        FamilyRelationship $relationship,
        bool $makeFamilyAdmin = false,
        bool $forceMove = false,
    ): User {
        abort_unless($actor->can('update', $family), 403);
        abort_unless($member->canBelongToHousehold(), 422, 'This account role cannot be linked to a household.');

        if ($member->family_id && (int) $member->family_id !== (int) $family->id && ! $forceMove) {
            throw ValidationException::withMessages([
                'user_id' => "{$member->displayFullName()} is already linked to another household. Confirm the move to continue.",
            ]);
        }

        return DB::transaction(function () use ($actor, $member, $family, $relationship, $makeFamilyAdmin): User {
            if ($member->family_id && (int) $member->family_id !== (int) $family->id) {
                $this->detachUserFromFamily($member);
            }

            if ($relationship === FamilyRelationship::Head || $makeFamilyAdmin) {
                $relationship = FamilyRelationship::Head;
                $makeFamilyAdmin = true;
                $this->demoteExistingHead($family, $member->id);
                $this->clearFamilyAdminFlags($family);
            }

            $member->update([
                'family_id' => $family->id,
                'family_relationship' => $relationship->value,
                'is_family_admin' => $makeFamilyAdmin,
            ]);

            if ($makeFamilyAdmin) {
                $family->update(['admin_user_id' => $member->id]);
            } elseif ((int) $family->admin_user_id === $member->id) {
                $family->update(['admin_user_id' => null]);
            }

            SecurityLogger::audit(
                'user_linked_to_family',
                actor: $actor,
                subject: $member,
                context: [
                    'target_user_id' => $member->id,
                    'family_id' => $family->id,
                    'relationship' => $relationship->value,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );

            return $member->fresh();
        });
    }

    /**
     * @param  list<int>  $userIds
     */
    public function mergeMembersIntoFamily(
        User $actor,
        Family $family,
        array $userIds,
        FamilyRelationship $relationship,
        bool $forceMove = true,
    ): void {
        foreach ($userIds as $userId) {
            $member = User::query()->findOrFail($userId);
            $this->assignUserToFamily($actor, $member, $family, $relationship, forceMove: $forceMove);
        }
    }

    public function removeUserFromFamily(User $actor, User $member): User
    {
        abort_unless($actor->can('update', $member), 403);
        abort_if(! $member->family_id, 422, 'This person is not linked to a household.');

        DB::transaction(function () use ($actor, $member): void {
            $this->detachUserFromFamily($member);

            SecurityLogger::audit(
                'user_unlinked_from_family',
                actor: $actor,
                subject: $member,
                context: [
                    'target_user_id' => $member->id,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );
        });

        return $member->fresh();
    }

    public function setFamilyAdmin(User $actor, Family $family, User $admin): void
    {
        abort_unless($actor->can('update', $family), 403);
        abort_unless((int) $admin->family_id === (int) $family->id, 422, 'Choose a member of this household.');

        if ($admin->isMember() && ! filled($admin->email)) {
            throw ValidationException::withMessages([
                'admin' => 'The primary family account must have an email address so they can sign in to the member portal.',
            ]);
        }

        DB::transaction(function () use ($actor, $family, $admin): void {
            $this->clearFamilyAdminFlags($family);
            $this->demoteExistingHead($family, $admin->id);

            $admin->update([
                'is_family_admin' => true,
                'family_relationship' => FamilyRelationship::Head->value,
            ]);

            $family->update(['admin_user_id' => $admin->id]);

            SecurityLogger::audit(
                'family_admin_updated',
                actor: $actor,
                subject: $family,
                context: [
                    'family_id' => $family->id,
                    'target_user_id' => $admin->id,
                    'portal' => SecurityLogger::detectPortal(),
                ],
            );
        });
    }

    public function syncFamilyAdminState(Family $family): void
    {
        $family->refresh();

        $admins = User::query()
            ->where('family_id', $family->id)
            ->where('is_family_admin', true)
            ->orderBy('id')
            ->get();

        if ($admins->count() > 1) {
            $primary = $admins->first();

            User::query()
                ->where('family_id', $family->id)
                ->where('id', '!=', $primary->id)
                ->where('is_family_admin', true)
                ->update(['is_family_admin' => false]);

            $admins = collect([$primary]);
        }

        $admin = $admins->first();

        if ($admin) {
            if ((int) $family->admin_user_id !== (int) $admin->id) {
                $family->update(['admin_user_id' => $admin->id]);
            }

            return;
        }

        if ($family->admin_user_id) {
            $family->update(['admin_user_id' => null]);
        }
    }

    private function detachUserFromFamily(User $member): void
    {
        $family = $member->family;

        if ($family && (int) $family->admin_user_id === $member->id) {
            $replacement = User::query()
                ->where('family_id', $family->id)
                ->where('id', '!=', $member->id)
                ->orderByRaw('CASE WHEN family_relationship = ? THEN 0 ELSE 1 END', [FamilyRelationship::Head->value])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->first();

            if ($replacement) {
                $replacement->update([
                    'is_family_admin' => true,
                    'family_relationship' => FamilyRelationship::Head->value,
                ]);
                $family->update(['admin_user_id' => $replacement->id]);
            } else {
                $family->update(['admin_user_id' => null]);
            }
        }

        $member->update([
            'family_id' => null,
            'family_relationship' => null,
            'is_family_admin' => false,
        ]);
    }

    private function clearFamilyAdminFlags(Family $family): void
    {
        User::query()
            ->where('family_id', $family->id)
            ->update(['is_family_admin' => false]);
    }

    private function demoteExistingHead(Family $family, int $exceptUserId): void
    {
        User::query()
            ->where('family_id', $family->id)
            ->where('id', '!=', $exceptUserId)
            ->where('family_relationship', FamilyRelationship::Head->value)
            ->update(['family_relationship' => FamilyRelationship::Spouse->value]);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function createMemberUser(array $data): User
    {
        $data = UserName::normalize($data);
        $status = AccountStatus::tryFrom((string) ($data['account_status'] ?? '')) ?? AccountStatus::Pending;

        return User::query()->create([
            'name' => $data['name'],
            'first_name' => $data['first_name'] ?? null,
            'last_name' => $data['last_name'] ?? null,
            'pronouns' => filled($data['pronouns'] ?? null) ? (string) $data['pronouns'] : null,
            'gender' => filled($data['gender'] ?? null) ? (string) $data['gender'] : null,
            'email' => $data['email'] ?? null,
            'password' => Hash::make((string) ($data['password'] ?? Str::password(24))),
            'role' => UserRole::Member->value,
            'account_status' => $status->value,
            'approved_at' => $data['approved_at'] ?? null,
            'approved_by' => $data['approved_by'] ?? null,
            'phone' => $data['phone'] ?? null,
            'date_of_birth' => $data['date_of_birth'] ?? null,
            'preferred_worship_location' => $data['preferred_worship_location'] ?? null,
            'address_line_1' => $data['address_line_1'] ?? null,
            'address_line_2' => $data['address_line_2'] ?? null,
            'city' => $data['city'] ?? null,
            'county' => $data['county'] ?? null,
            'postcode' => $data['postcode'] ?? null,
            'family_id' => $data['family_id'] ?? null,
            'is_family_admin' => (bool) ($data['is_family_admin'] ?? false),
            'family_relationship' => $data['family_relationship'] ?? null,
            'email_verified_at' => null,
        ]);
    }
}
