<?php

namespace App\Filament\Support;

use App\Models\Donation;
use App\Services\DonationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;

class AdminDonationTableActions
{
    /**
     * @return array<EditAction|ActionGroup>
     */
    public static function recordActions(): array
    {
        return CompactTableActions::editWithMenu([
            ActionGroup::make([
                self::approveAction(),
                self::rejectAction(),
            ])->dropdown(false),
            DeleteAction::make()
                ->visible(fn (Donation $record): bool => auth()->user()?->can('delete', $record) ?? false)
                ->action(fn (Donation $record) => app(DonationService::class)->deleteFromAdmin(auth()->user(), $record)),
        ]);
    }

    private static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (Donation $record): bool => $record->isPending())
            ->form([
                Textarea::make('admin_note')
                    ->label('Admin note')
                    ->default(fn (Donation $record): ?string => $record->admin_note),
            ])
            ->action(fn (Donation $record, array $data) => app(DonationService::class)->approve($record, auth()->user(), $data['admin_note'] ?? null));
    }

    private static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (Donation $record): bool => $record->isPending())
            ->requiresConfirmation()
            ->form([
                Textarea::make('admin_note')
                    ->label('Reason / admin note'),
            ])
            ->action(fn (Donation $record, array $data) => app(DonationService::class)->reject($record, auth()->user(), $data['admin_note'] ?? null));
    }
}
