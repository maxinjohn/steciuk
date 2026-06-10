<?php

namespace App\Filament\Support;

use App\Enums\AccountStatus;
use App\Models\User;
use App\Services\DataProtectionService;
use App\Services\MemberRegistrationService;
use App\Services\SecurityLogger;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Select;

class AdminUserTableActions
{
    /**
     * @return array<EditAction|ActionGroup>
     */
    public static function recordActions(): array
    {
        return [
            CompactTableActions::editButton(),
            self::overflowMenu(),
        ];
    }

    public static function overflowMenu(): ActionGroup
    {
        return CompactTableActions::overflowMenu([
            ViewAction::make()
                ->label('View summary'),
            ActionGroup::make([
                AdminUserPasswordActions::setPasswordTableAction(),
                AdminUserPasswordActions::sendResetLinkTableAction(),
            ])->dropdown(false),
            ActionGroup::make([
                self::approveAction(),
                self::rejectAction(),
                self::setPendingAction(),
                self::activateAction(),
                self::deactivateAction(),
            ])->dropdown(false),
            self::changeRoleAction(),
            ActionGroup::make([
                self::anonymizeAction(),
                DeleteAction::make()
                    ->visible(fn (User $record): bool => auth()->user()?->can('delete', $record) && self::canManageAccount($record))
                    ->before(function (User $record): void {
                        SecurityLogger::audit(
                            'user_deleted',
                            actor: auth()->user(),
                            subject: $record,
                            context: [
                                'target_user_id' => $record->id,
                                'portal' => SecurityLogger::detectPortal(),
                            ],
                        );
                    }),
            ])->dropdown(false),
        ]);
    }

    private static function approveAction(): Action
    {
        return Action::make('approve')
            ->label('Approve')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (User $record): bool => $record->isMember() && $record->accountStatus() === AccountStatus::Pending)
            ->requiresConfirmation()
            ->action(fn (User $record) => app(MemberRegistrationService::class)->approve($record, auth()->user()));
    }

    private static function rejectAction(): Action
    {
        return Action::make('reject')
            ->label('Reject')
            ->icon('heroicon-o-x-circle')
            ->color('danger')
            ->visible(fn (User $record): bool => $record->isMember() && $record->accountStatus() === AccountStatus::Pending)
            ->requiresConfirmation()
            ->action(fn (User $record) => app(MemberRegistrationService::class)->reject($record, auth()->user()));
    }

    private static function setPendingAction(): Action
    {
        return Action::make('setPending')
            ->label('Set pending')
            ->icon('heroicon-o-clock')
            ->visible(fn (User $record): bool => $record->isMember() && $record->accountStatus() !== AccountStatus::Pending)
            ->requiresConfirmation()
            ->action(fn (User $record) => app(MemberRegistrationService::class)->setPending($record, auth()->user()));
    }

    private static function deactivateAction(): Action
    {
        return Action::make('deactivate')
            ->label('Deactivate')
            ->icon('heroicon-o-no-symbol')
            ->color('danger')
            ->visible(fn (User $record): bool => $record->isActive() && self::canManageAccount($record))
            ->requiresConfirmation()
            ->action(fn (User $record) => app(MemberRegistrationService::class)->deactivateUser($record, auth()->user()));
    }

    private static function activateAction(): Action
    {
        return Action::make('activate')
            ->label('Activate')
            ->icon('heroicon-o-check-circle')
            ->color('success')
            ->visible(fn (User $record): bool => ! $record->isActive() && self::canManageAccount($record))
            ->requiresConfirmation()
            ->action(fn (User $record) => app(MemberRegistrationService::class)->activateUser($record, auth()->user()));
    }

    private static function changeRoleAction(): Action
    {
        return Action::make('promote')
            ->label('Change role')
            ->icon('heroicon-o-arrow-up-circle')
            ->visible(fn (User $record): bool => auth()->user()?->canChangeRoleOf($record) ?? false)
            ->form([
                Select::make('role')
                    ->label('Role')
                    ->options(fn (): array => auth()->user()?->assignableRoleOptions() ?? [])
                    ->required()
                    ->default(fn (User $record): string => $record->roleSlug()),
            ])
            ->action(function (User $record, array $data): void {
                $actor = auth()->user();

                if (! $actor) {
                    return;
                }

                if (! $actor->canChangeRoleOf($record)) {
                    return;
                }

                $role = $actor->resolveRoleForUpdate(
                    (string) ($data['role'] ?? $record->roleSlug()),
                    $record,
                );

                if ($role === $record->roleSlug()) {
                    return;
                }

                $record->update(['role' => $role]);

                SecurityLogger::audit('user_role_updated', actor: $actor, subject: $record, context: [
                    'target_user_id' => $record->id,
                    'role' => $role,
                    'portal' => SecurityLogger::detectPortal(),
                ]);
            });
    }

    private static function anonymizeAction(): Action
    {
        return Action::make('anonymize')
            ->label('Anonymise account')
            ->icon('heroicon-o-shield-exclamation')
            ->color('danger')
            ->visible(fn (User $record): bool => $record->isMember()
                && ! $record->isAnonymized()
                && self::canManageAccount($record))
            ->requiresConfirmation()
            ->modalHeading('Anonymise member account')
            ->modalDescription(fn (User $record): string => $record->hasErasureRequest()
                ? 'This member requested deletion. Anonymising removes personal data while retaining anonymised giving records where required by law.'
                : 'This permanently removes personal identifiers from the account. Use when processing a deletion request or retiring duplicate records.')
            ->action(fn (User $record) => app(DataProtectionService::class)->anonymizeUser($record, auth()->user()));
    }

    private static function canManageAccount(User $record): bool
    {
        $actor = auth()->user();

        if (! $actor?->can('update', $record)) {
            return false;
        }

        if ($record->id === $actor->id) {
            return false;
        }

        if ($record->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
