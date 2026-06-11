<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\AccountStatus;
use App\Enums\UserRole;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\UserSignatureUpload;
use App\Models\User;
use App\Support\UserName;
use App\Support\UserProfileAttributes;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /** @var array{family_id: int|null, family_relationship: string|null, is_family_admin: bool}|null */
    protected ?array $householdFormData = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data = UserName::normalize($data);
        $data = UserProfileAttributes::normalize($data);
        $data['role'] = auth()->user()?->resolveRoleForCreate($data['role'] ?? UserRole::Member->value)
            ?? UserRole::Member->value;
        $data['account_status'] = AccountStatus::Approved->value;
        $data['approved_at'] = now();
        $data['approved_by'] = auth()->id();
        $data['email_verified_at'] = now();

        if (blank($data['email'] ?? null)) {
            return $data;
        }

        $data['email'] = strtolower(trim((string) $data['email']));

        if (filled($data['family_id'] ?? null) || filled($data['family_relationship'] ?? null) || ($data['is_family_admin'] ?? false)) {
            $this->householdFormData = [
                'family_id' => filled($data['family_id'] ?? null) ? (int) $data['family_id'] : null,
                'family_relationship' => $data['family_relationship'] ?? null,
                'is_family_admin' => (bool) ($data['is_family_admin'] ?? false),
            ];

            unset($data['family_id'], $data['family_relationship'], $data['is_family_admin']);
        }

        unset($data['signature_upload']);

        return $data;
    }

    protected function afterCreate(): void
    {
        /** @var User $member */
        $member = $this->getRecord()->fresh();

        UserSignatureUpload::persist($member, $this->form->getState()['signature_upload'] ?? null);

        if ($this->householdFormData === null || blank($this->householdFormData['family_id'] ?? null)) {
            return;
        }

        /** @var \App\Models\User $member */
        $member = $this->getRecord()->fresh();
        $actor = auth()->user();

        if (! $actor || ! $member->canBelongToHousehold()) {
            return;
        }

        $family = \App\Models\Family::query()->findOrFail((int) $this->householdFormData['family_id']);
        $relationship = \App\Enums\FamilyRelationship::tryFromValue($this->householdFormData['family_relationship'])
            ?? \App\Enums\FamilyRelationship::Other;

        app(\App\Services\MemberRegistrationService::class)->assignUserToFamily(
            $actor,
            $member,
            $family,
            $relationship,
            makeFamilyAdmin: $this->householdFormData['is_family_admin'] || $relationship === \App\Enums\FamilyRelationship::Head,
            forceMove: true,
        );
    }
}
