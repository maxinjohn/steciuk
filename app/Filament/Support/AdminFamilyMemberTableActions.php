<?php

namespace App\Filament\Support;

use App\Enums\AccountStatus;
use App\Filament\Resources\Users\UserResource;
use App\Models\Family;
use App\Models\User;
use App\Services\MemberRegistrationService;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\EditAction;
use Filament\Notifications\Notification;

class AdminFamilyMemberTableActions
{
    /**
     * @return array<EditAction|ActionGroup>
     */
    public static function recordActions(Family $family, callable $afterAdminChange): array
    {
        return [
            self::editAction(),
            CompactTableActions::overflowMenu([
                self::setFamilyAdminAction($family, $afterAdminChange),
                ActionGroup::make([
                    AdminUserPasswordActions::setPasswordTableAction(),
                    AdminUserPasswordActions::sendResetLinkTableAction(),
                ])->dropdown(false),
                ActionGroup::make([
                    self::approveAction(),
                    self::unlinkAction($family),
                    self::deactivateAction(),
                    self::activateAction(),
                ])->dropdown(false),
            ]),
        ];
    }

    public static function editAction(): EditAction
    {
        return CompactTableActions::editButton()
            ->url(fn (User $record): string => UserResource::getUrl('edit', ['record' => $record]));
    }

    private static function setFamilyAdminAction(Family $family, callable $afterAdminChange): Action
    {
        return Action::make('setFamilyAdmin')
            ->label('Set as family admin')
            ->icon('heroicon-o-star')
            ->color('warning')
            ->visible(fn (User $record): bool => ! $record->isFamilyAdmin() && (auth()->user()?->can('update', $family) ?? false))
            ->requiresConfirmation()
            ->modalHeading('Set primary family account')
            ->modalDescription(function (User $record): string {
                $emailNote = filled($record->email)
                    ? ''
                    : ' This member must have an email address before they can sign in.';

                return "{$record->displayFullName()} will become the only household member who can sign in on behalf of this family on the member portal. The previous primary account will lose sign-in access.{$emailNote}";
            })
            ->action(function (User $record) use ($family, $afterAdminChange): void {
                app(MemberRegistrationService::class)->setFamilyAdmin(auth()->user(), $family, $record);

                Notification::make()
                    ->success()
                    ->title('Primary family account updated')
                    ->body("{$record->displayFullName()} is now the primary family account.")
                    ->send();

                $afterAdminChange();
            });
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

    private static function unlinkAction(Family $family): Action
    {
        return Action::make('unlink')
            ->label('Unlink from household')
            ->icon('heroicon-o-arrow-right-start-on-rectangle')
            ->color('gray')
            ->requiresConfirmation()
            ->modalDescription('The member account stays active but is removed from this household.')
            ->action(fn (User $record) => app(MemberRegistrationService::class)->removeUserFromFamily(auth()->user(), $record));
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
