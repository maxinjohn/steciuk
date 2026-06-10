<?php

namespace App\Filament\Support;

use App\Models\User;
use App\Services\UserPasswordService;
use Filament\Actions\Action;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Illuminate\Validation\Rules\Password;

class AdminUserPasswordActions
{
    public static function setPasswordTableAction(): Action
    {
        return self::configureSetPasswordAction(
            visible: fn (User $record): bool => self::canManageTarget($record),
            resolveTarget: fn (...$arguments): ?User => self::extractUser(...$arguments),
        );
    }

    public static function sendResetLinkTableAction(): Action
    {
        return self::configureSendResetLinkAction(
            visible: fn (User $record): bool => filled($record->email) && self::canManageTarget($record),
            resolveTarget: fn (...$arguments): ?User => self::extractUser(...$arguments),
        );
    }

    public static function setPasswordPageAction(User $target): Action
    {
        return self::configureSetPasswordAction(
            visible: fn (): bool => self::canManageTarget($target),
            resolveTarget: fn (): ?User => $target,
        );
    }

    public static function sendResetLinkPageAction(User $target): Action
    {
        return self::configureSendResetLinkAction(
            visible: fn (): bool => filled($target->email) && self::canManageTarget($target),
            resolveTarget: fn (): ?User => $target,
        );
    }

    /**
     * @param  callable(mixed...): bool  $visible
     * @param  callable(array<string, mixed>|mixed...): (?User)  $resolveTarget
     */
    private static function configureSetPasswordAction(callable $visible, callable $resolveTarget): Action
    {
        return Action::make('setPassword')
            ->label('Set password')
            ->icon('heroicon-o-key')
            ->color('gray')
            ->visible($visible)
            ->form([
                TextInput::make('password')
                    ->label('New password')
                    ->password()
                    ->revealable()
                    ->required()
                    ->confirmed()
                    ->rule(Password::defaults()),
                TextInput::make('password_confirmation')
                    ->label('Confirm password')
                    ->password()
                    ->revealable()
                    ->required(),
            ])
            ->modalHeading('Set a new password')
            ->modalDescription('The user will sign in with this password immediately. Share it with them through a secure channel.')
            ->action(function (array $data, ...$arguments) use ($resolveTarget): void {
                $actor = auth()->user();
                $target = $resolveTarget(...$arguments);

                if (! $actor || ! $target instanceof User) {
                    return;
                }

                app(UserPasswordService::class)->setPassword($actor, $target, $data['password']);

                Notification::make()
                    ->success()
                    ->title('Password updated')
                    ->body('The new password is active now.')
                    ->send();
            });
    }

    /**
     * @param  callable(mixed...): bool  $visible
     * @param  callable(array<string, mixed>|mixed...): (?User)  $resolveTarget
     */
    private static function configureSendResetLinkAction(callable $visible, callable $resolveTarget): Action
    {
        return Action::make('sendPasswordResetLink')
            ->label('Send reset link')
            ->icon('heroicon-o-envelope')
            ->color('gray')
            ->visible($visible)
            ->requiresConfirmation()
            ->modalHeading('Send password reset email')
            ->modalDescription(function (...$arguments) use ($resolveTarget): string {
                $target = $resolveTarget(...$arguments);

                return 'We will email a secure link to '.($target?->email ?? 'this user').' so they can choose a new password.';
            })
            ->action(function (...$arguments) use ($resolveTarget): void {
                $actor = auth()->user();
                $target = $resolveTarget(...$arguments);

                if (! $actor || ! $target instanceof User) {
                    return;
                }

                app(UserPasswordService::class)->sendPasswordResetLink($actor, $target);

                Notification::make()
                    ->success()
                    ->title('Reset link sent')
                    ->body('The password reset email has been sent.')
                    ->send();
            });
    }

    private static function canManageTarget(?User $target): bool
    {
        $actor = auth()->user();

        if (! $actor instanceof User || ! $target instanceof User) {
            return false;
        }

        if ((int) $target->id === (int) $actor->id) {
            return false;
        }

        if ($target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return $actor->can('update', $target);
    }

    private static function extractUser(mixed ...$arguments): ?User
    {
        foreach ($arguments as $argument) {
            if ($argument instanceof User) {
                return $argument;
            }
        }

        return null;
    }
}
