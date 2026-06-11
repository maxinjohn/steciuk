<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class UserPasswordService
{
    public function setPassword(User $actor, User $target, string $password): void
    {
        $this->assertCanManagePassword($actor, $target);

        $target->update(['password' => $password]);

        SecurityLogger::audit(
            'user_password_set_by_admin',
            actor: $actor,
            subject: $target,
            context: [
                'target_user_id' => $target->id,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );
    }

    public function sendPasswordResetLink(User $actor, User $target): void
    {
        $this->assertCanManagePassword($actor, $target);

        if (! filled($target->email)) {
            throw ValidationException::withMessages([
                'email' => 'This account does not have an email address, so a reset link cannot be sent.',
            ]);
        }

        $status = Password::sendResetLink([
            'email' => strtolower(trim((string) $target->email)),
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            throw ValidationException::withMessages([
                'email' => __($status),
            ]);
        }

        SecurityLogger::audit(
            'user_password_reset_link_sent',
            actor: $actor,
            subject: $target,
            context: [
                'target_user_id' => $target->id,
                'email' => $target->email,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );
    }

    public function requestPublicPasswordResetLink(string $email): void
    {
        $normalized = strtolower(trim($email));

        if ($normalized === '') {
            return;
        }

        $user = User::query()->where('email', $normalized)->first();

        if (! $user instanceof User) {
            return;
        }

        Password::sendResetLink(['email' => $normalized]);
    }

    private function assertCanManagePassword(User $actor, User $target): void
    {
        if ((int) $target->id === (int) $actor->id) {
            abort(403, 'Use your profile or the forgot password page to change your own password.');
        }

        if ($target->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            abort(403, 'Only a super admin can manage this account password.');
        }

        abort_unless($actor->can('update', $target), 403);
    }
}
