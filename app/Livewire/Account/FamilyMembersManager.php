<?php

namespace App\Livewire\Account;

use App\Enums\FamilyRelationship;
use App\Models\User;
use App\Services\DataProtectionService;
use App\Services\MemberRegistrationService;
use App\Services\UserPasswordService;
use App\Support\GdprConfig;
use App\Support\ParishGender;
use App\Support\ParishPronouns;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Validate;
use Livewire\Component;

class FamilyMembersManager extends Component
{
    #[Validate('required|string|max:120')]
    public string $first_name = '';

    #[Validate('nullable|string|max:120')]
    public string $last_name = '';

    #[Validate('nullable|string|max:50')]
    public string $pronouns = '';

    #[Validate('nullable|string|max:30')]
    public string $gender = '';

    #[Validate('nullable|email|max:255')]
    public string $email = '';

    #[Validate('nullable|string|max:30')]
    public string $phone = '';

    #[Validate('required|date|before:today|after:1900-01-01')]
    public string $date_of_birth = '';

    #[Validate('required|string|max:255')]
    public string $relationship = '';

    public bool $saved = false;

    public ?int $editingMemberId = null;

    public string $editEmail = '';

    public bool $emailUpdated = false;

    public bool $household_data_consent = false;

    #[Validate('nullable|string|max:120')]
    public string $family_name = '';

    public bool $householdCreated = false;

    public bool $passwordResetSent = false;

    public function mount(): void
    {
        $this->relationship = FamilyRelationship::Child->value;
    }

    public function createHousehold(MemberRegistrationService $registrationService): void
    {
        $member = Auth::user();

        abort_unless($member instanceof User && $member->canBelongToHousehold(), 403);
        abort_if($member->family_id !== null, 422);
        abort_unless($member->isAccountApproved() && $member->isActive(), 403);

        $this->validate([
            'family_name' => 'nullable|string|max:120',
        ]);

        $registrationService->createHouseholdForMember($member, $this->family_name);

        auth()->setUser($member->fresh());

        $this->reset(['family_name']);
        $this->householdCreated = true;
    }

    public function addMember(MemberRegistrationService $registrationService, DataProtectionService $dataProtectionService): void
    {
        $admin = Auth::user();

        abort_unless($admin instanceof User && $admin->canManageHouseholdOnPortal(), 403);

        $rules = [
            'first_name' => 'required|string|max:120',
            'last_name' => 'nullable|string|max:120',
            'pronouns' => ['nullable', 'string', 'max:50', Rule::in(array_keys(ParishPronouns::options()))],
            'gender' => ['nullable', 'string', 'max:30', Rule::in(array_keys(ParishGender::options()))],
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:30',
            'date_of_birth' => 'required|date|before:today|after:1900-01-01',
            'relationship' => 'required|string|in:'.implode(',', array_keys(collect(FamilyRelationship::options())->except([FamilyRelationship::Head->value])->all())),
        ];

        if ($admin->household_data_consent_at === null) {
            $rules['household_data_consent'] = 'accepted';
        }

        $validated = $this->validate($rules);

        $registrationService->addFamilyMember($admin, $validated);

        if ($admin->household_data_consent_at === null) {
            $dataProtectionService->recordHouseholdDataConsent($admin->fresh());
        }

        $this->reset(['first_name', 'last_name', 'pronouns', 'gender', 'email', 'phone', 'date_of_birth', 'household_data_consent']);
        $this->relationship = FamilyRelationship::Child->value;
        $this->saved = true;
        $this->emailUpdated = false;
    }

    public function startEditingEmail(int $memberId): void
    {
        $admin = Auth::user();

        abort_unless($admin instanceof User && $admin->canManageHouseholdOnPortal(), 403);

        $member = User::query()->findOrFail($memberId);

        abort_unless($member->family_id === $admin->family_id, 403);
        abort_if($member->isFamilyAdmin(), 403);

        $this->editingMemberId = $member->id;
        $this->editEmail = (string) ($member->email ?? '');
        $this->emailUpdated = false;
    }

    public function cancelEditingEmail(): void
    {
        $this->reset(['editingMemberId', 'editEmail']);
        $this->emailUpdated = false;
    }

    public function saveMemberEmail(MemberRegistrationService $registrationService): void
    {
        $admin = Auth::user();

        abort_unless($admin instanceof User && $admin->canManageHouseholdOnPortal(), 403);
        abort_unless($this->editingMemberId, 403);

        $member = User::query()->findOrFail($this->editingMemberId);

        $this->validate([
            'editEmail' => 'nullable|email|max:255',
        ]);

        $registrationService->updateHouseholdMemberEmail($admin, $member, $this->editEmail);

        $this->reset(['editingMemberId', 'editEmail']);
        $this->emailUpdated = true;
        $this->saved = false;
    }

    public function removeMember(int $memberId, MemberRegistrationService $registrationService): void
    {
        $admin = Auth::user();

        abort_unless($admin instanceof User && $admin->canManageHouseholdOnPortal(), 403);

        $member = User::query()->findOrFail($memberId);

        $registrationService->removeHouseholdMemberByFamilyAdmin($admin, $member);

        $this->reset(['editingMemberId', 'editEmail', 'saved', 'emailUpdated']);
    }

    public function sendMemberPasswordResetLink(int $memberId, UserPasswordService $passwordService): void
    {
        $admin = Auth::user();

        abort_unless($admin instanceof User && $admin->canManageHouseholdOnPortal(), 403);

        $member = User::query()->findOrFail($memberId);

        $passwordService->sendPasswordResetLinkForFamilyAdmin($admin, $member);

        $this->passwordResetSent = true;
        $this->reset(['editingMemberId', 'editEmail', 'saved', 'emailUpdated']);
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();

        $members = collect();

        if ($user?->family_id) {
            $members = User::query()
                ->where('family_id', $user->family_id)
                ->orderByRaw('CASE WHEN id = ? THEN 0 ELSE 1 END', [$user->id])
                ->orderBy('last_name')
                ->orderBy('first_name')
                ->get();
        }

        return view('livewire.account.family-members-manager', [
            'members' => $members,
            'family' => $user?->family?->loadMissing('admin'),
            'relationshipOptions' => FamilyRelationship::options(),
            'pronounOptions' => ParishPronouns::options(),
            'genderOptions' => ParishGender::options(),
            'canManage' => $user?->canManageHouseholdOnPortal(),
            'canCreateHousehold' => $user?->canBelongToHousehold()
                && $user->family_id === null
                && $user->isAccountApproved()
                && $user->isActive(),
            'needsHouseholdConsent' => $user?->household_data_consent_at === null,
            'privacyPolicyUrl' => GdprConfig::privacyPolicyUrl(),
        ]);
    }
}
