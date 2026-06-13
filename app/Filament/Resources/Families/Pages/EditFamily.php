<?php

namespace App\Filament\Resources\Families\Pages;

use App\Filament\Resources\Families\FamilyResource;
use App\Models\Family;
use App\Services\MemberRegistrationService;
use App\Services\SecurityLogger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
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
