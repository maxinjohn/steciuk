<?php

namespace App\Filament\Resources\Users\Pages;

use App\Enums\FamilyRelationship;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\AdminUserPasswordActions;
use App\Filament\Support\UserSignatureUpload;
use App\Models\Family;
use App\Models\User;
use App\Services\DataProtectionService;
use App\Services\MemberRegistrationService;
use App\Services\PanelMembershipService;
use App\Support\UserName;
use App\Support\UserProfileAttributes;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UserResource::class;

    /** @var array{family_id: int|null, family_relationship: string|null, is_family_admin: bool}|null */
    protected ?array $householdFormData = null;

    /** @var list<int>|null */
    protected ?array $panelIds = null;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        $actor = auth()->user();
        $target = $this->getRecord();

        if (
            $target instanceof User
            && $target->isSuperAdmin()
            && $actor
            && ! $actor->isSuperAdmin()
        ) {
            abort(403, 'Only a super admin can edit this account.');
        }
    }

    protected function getHeaderActions(): array
    {
        /** @var User $target */
        $target = $this->getRecord();

        return [
            AdminUserPasswordActions::setPasswordPageAction($target),
            AdminUserPasswordActions::sendResetLinkPageAction($target),
            Action::make('anonymize')
                ->label('Anonymise account')
                ->icon('heroicon-o-shield-exclamation')
                ->color('danger')
                ->visible(fn (): bool => $this->getRecord() instanceof User
                    && $this->getRecord()->isMember()
                    && ! $this->getRecord()->isAnonymized()
                    && $this->canManageTargetAccount())
                ->requiresConfirmation()
                ->modalHeading('Anonymise member account')
                ->modalDescription(fn (): string => $this->getRecord()->hasErasureRequest()
                    ? 'This member requested deletion. Anonymising removes personal data while retaining anonymised giving records where required by law.'
                    : 'This permanently removes personal identifiers from the account.')
                ->action(function (): void {
                    $actor = auth()->user();
                    $target = $this->getRecord();

                    if ($actor && $target instanceof User) {
                        app(DataProtectionService::class)->anonymizeUser($target, $actor);
                    }
                }),
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->can('delete', $this->getRecord()) && $this->canManageTargetAccount()),
        ];
    }

    private function canManageTargetAccount(): bool
    {
        $actor = auth()->user();
        $target = $this->getRecord();

        if (! $target instanceof User || ! $actor) {
            return false;
        }

        if ($target->id === $actor->id) {
            return false;
        }

        if ($target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return true;
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $target = $this->getRecord();

        if ($target instanceof User) {
            $data = UserSignatureUpload::fillFormData($target, $data);
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $data = UserName::normalize($data);
        $data = UserProfileAttributes::normalize($data);

        if (auth()->user()?->canChangeRoleOf($this->getRecord())) {
            $data['role'] = auth()->user()->resolveRoleForUpdate(
                $data['role'] ?? $this->getRecord()->roleSlug(),
                $this->getRecord(),
            );
        } else {
            unset($data['role']);
        }

        if (filled($data['email'] ?? null)) {
            $data['email'] = strtolower(trim((string) $data['email']));
        }

        if ($this->getRecord()->canBelongToHousehold() && auth()->user()?->can('update', $this->getRecord())) {
            $this->householdFormData = [
                'family_id' => filled($data['family_id'] ?? null) ? (int) $data['family_id'] : null,
                'family_relationship' => $data['family_relationship'] ?? null,
                'is_family_admin' => (bool) ($data['is_family_admin'] ?? false),
            ];

            unset($data['family_id'], $data['family_relationship'], $data['is_family_admin']);
        }

        unset($data['signature_upload']);

        if (array_key_exists('panels', $data) && auth()->user()?->can('update', User::class)) {
            $this->panelIds = collect($data['panels'] ?? [])
                ->filter(fn ($id): bool => filled($id))
                ->map(fn ($id): int => (int) $id)
                ->values()
                ->all();
            unset($data['panels']);
        }

        return $data;
    }

    protected function afterSave(): void
    {
        /** @var User $member */
        $member = $this->getRecord()->fresh();

        UserSignatureUpload::persist($member, $this->form->getState()['signature_upload'] ?? null);

        if ($this->panelIds !== null) {
            app(PanelMembershipService::class)->syncUserPanels($member, $this->panelIds);
            $this->panelIds = null;
        }

        if ($this->householdFormData === null) {
            return;
        }

        /** @var User $member */
        $member = $this->getRecord()->fresh();
        $actor = auth()->user();

        if (! $actor || ! $member->canBelongToHousehold()) {
            return;
        }

        $service = app(MemberRegistrationService::class);
        $familyId = $this->householdFormData['family_id'];
        $relationship = FamilyRelationship::tryFromValue($this->householdFormData['family_relationship']);
        $makeAdmin = $this->householdFormData['is_family_admin'];

        if ($familyId === null) {
            if ($member->family_id) {
                $service->removeUserFromFamily($actor, $member);
            }

            return;
        }

        $family = Family::query()->findOrFail($familyId);

        if ((int) $member->family_id !== $familyId
            || $member->family_relationship !== $relationship?->value
            || $member->is_family_admin !== $makeAdmin) {
            $service->assignUserToFamily(
                $actor,
                $member,
                $family,
                $relationship ?? FamilyRelationship::Other,
                makeFamilyAdmin: $makeAdmin || $relationship === FamilyRelationship::Head,
                forceMove: true,
            );
        }
    }
}
