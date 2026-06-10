<?php

namespace App\Filament\Resources\Families\Pages;

use App\Enums\FamilyRelationship;
use App\Filament\Resources\Families\FamilyResource;
use App\Models\Family;
use App\Models\User;
use App\Services\MemberRegistrationService;
use App\Services\SecurityLogger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\EditRecord;

class EditFamily extends EditRecord
{
    protected static string $resource = FamilyResource::class;

    public function mount(int|string $record): void
    {
        parent::mount($record);

        /** @var Family $family */
        $family = $this->getRecord();
        app(MemberRegistrationService::class)->syncFamilyAdminState($family);
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['admin_user_id']);

        return $data;
    }

    protected function getHeaderActions(): array
    {
        /** @var Family $family */
        $family = $this->getRecord();

        return [
            Action::make('mergeMembers')
                ->label('Merge members')
                ->icon('heroicon-o-user-plus')
                ->visible(fn (): bool => auth()->user()?->can('update', $family) ?? false)
                ->form([
                    Select::make('user_ids')
                        ->label('Member accounts')
                        ->multiple()
                        ->required()
                        ->searchable()
                        ->getSearchResultsUsing(function (string $search) use ($family): array {
                            return User::query()
                                ->householdEligible()
                                ->where(function ($query) use ($family): void {
                                    $query->whereNull('family_id')
                                        ->orWhere('family_id', $family->id);
                                })
                                ->where(function ($query) use ($search): void {
                                    $query->where('name', 'like', "%{$search}%")
                                        ->orWhere('first_name', 'like', "%{$search}%")
                                        ->orWhere('last_name', 'like', "%{$search}%")
                                        ->orWhere('email', 'like', "%{$search}%");
                                })
                                ->orderBy('last_name')
                                ->orderBy('first_name')
                                ->limit(50)
                                ->get()
                                ->mapWithKeys(fn (User $user): array => [
                                    $user->id => trim($user->displayFullName().' · '.($user->email ?? 'no email').($user->family_id && (int) $user->family_id !== (int) $family->id ? ' · other household' : '')),
                                ])
                                ->all();
                        }),
                    Select::make('relationship')
                        ->label('Relationship in this household')
                        ->options(FamilyRelationship::householdAssignmentOptions())
                        ->default(FamilyRelationship::Spouse->value)
                        ->required(),
                    Toggle::make('force_move')
                        ->label('Move from another household if needed')
                        ->default(true)
                        ->helperText('Required when linking someone who was already assigned elsewhere.'),
                ])
                ->action(function (array $data) use ($family): void {
                    app(MemberRegistrationService::class)->mergeMembersIntoFamily(
                        auth()->user(),
                        $family,
                        $data['user_ids'] ?? [],
                        FamilyRelationship::tryFromValue($data['relationship'] ?? null) ?? FamilyRelationship::Other,
                        forceMove: (bool) ($data['force_move'] ?? true),
                    );
                }),
            Action::make('deactivate')
                ->label('Deactivate family')
                ->icon('heroicon-o-no-symbol')
                ->color('danger')
                ->visible(fn (): bool => $family->isActive() && auth()->user()?->can('update', $family))
                ->requiresConfirmation()
                ->modalHeading('Deactivate this family?')
                ->modalDescription('Every member in this household will be blocked from signing in until the family is reactivated.')
                ->action(function () use ($family): void {
                    app(MemberRegistrationService::class)->deactivateFamily($family, auth()->user());
                    $this->refreshFormData(['is_active']);
                }),
            Action::make('activate')
                ->label('Activate family')
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn (): bool => ! $family->isActive() && auth()->user()?->can('update', $family))
                ->requiresConfirmation()
                ->action(function () use ($family): void {
                    app(MemberRegistrationService::class)->activateFamily($family, auth()->user());
                    $this->refreshFormData(['is_active']);
                }),
            DeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->can('delete', $family) ?? false)
                ->requiresConfirmation()
                ->modalDescription('This permanently removes the family record. Member accounts will remain but will no longer be linked to this household.')
                ->before(function () use ($family): void {
                    SecurityLogger::audit(
                        'family_deleted',
                        actor: auth()->user(),
                        subject: $family,
                        context: [
                            'family_id' => $family->id,
                            'portal' => SecurityLogger::detectPortal(),
                        ],
                    );
                }),
        ];
    }
}
