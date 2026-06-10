<?php

namespace App\Filament\Support;

use App\Models\Family;
use App\Services\MemberRegistrationService;
use App\Services\SecurityLogger;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;

class AdminFamilyTableActions
{
    /**
     * @return array<EditAction|ActionGroup>
     */
    public static function recordActions(): array
    {
        return CompactTableActions::editWithMenu([
            ActionGroup::make([
                self::deactivateAction(),
                self::activateAction(),
            ])->dropdown(false),
            DeleteAction::make()
                ->visible(fn (Family $record): bool => auth()->user()?->can('delete', $record) ?? false)
                ->requiresConfirmation()
                ->modalDescription('This permanently removes the family record. Member accounts remain but are no longer linked to this household.')
                ->before(function (Family $record): void {
                    SecurityLogger::audit(
                        'family_deleted',
                        actor: auth()->user(),
                        subject: $record,
                        context: [
                            'family_id' => $record->id,
                            'portal' => SecurityLogger::detectPortal(),
                        ],
                    );
                }),
        ]);
    }

    private static function deactivateAction(): Action
    {
        return Action::make('deactivate')
            ->label('Deactivate')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->visible(fn (Family $record): bool => $record->isActive() && auth()->user()?->can('update', $record))
            ->requiresConfirmation()
            ->modalDescription('Every member in this household will be blocked from signing in until the family is reactivated.')
            ->action(fn (Family $record) => app(MemberRegistrationService::class)->deactivateFamily($record, auth()->user()));
    }

    private static function activateAction(): Action
    {
        return Action::make('activate')
            ->label('Activate')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Family $record): bool => ! $record->isActive() && auth()->user()?->can('update', $record))
            ->requiresConfirmation()
            ->action(fn (Family $record) => app(MemberRegistrationService::class)->activateFamily($record, auth()->user()));
    }
}
