<?php

namespace App\Filament\Support;

use App\Models\Family;
use App\Models\User;
use App\Support\FamilyLabel;
use Illuminate\Database\Eloquent\Builder;

class HouseholdMemberOptions
{
    /**
     * @return array<int, string>
     */
    public static function options(Family $family, ?string $search = null, int $limit = 100): array
    {
        return self::linkableQuery($family, $search)
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => self::label($user, $family),
            ])
            ->all();
    }

    /**
     * @return array<int, string>
     */
    public static function unlinkedOptions(?string $search = null, int $limit = 50): array
    {
        return User::query()
            ->householdEligible()
            ->whereNull('family_id')
            ->when(filled($search), function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => trim($user->displayFullName().' · '.($user->email ?? 'no email')),
            ])
            ->all();
    }

    public static function label(User $user, Family $family): string
    {
        $base = trim($user->displayFullName().' · '.($user->email ?? 'no email'));

        if ($user->family_id === null) {
            return $base.' · Available to link';
        }

        if ((int) $user->family_id === (int) $family->id) {
            return $base.' · Already in this household';
        }

        $user->loadMissing('family.admin');
        $otherLabel = $user->family
            ? FamilyLabel::forAdmin($user->family)
            : 'Household #'.$user->family_id;

        return $base.' · In '.$otherLabel.' — unlink first or enable move below';
    }

    public static function labelForId(int $userId, Family $family): ?string
    {
        $user = User::query()->with('family.admin')->find($userId);

        return $user instanceof User ? self::label($user, $family) : null;
    }

    public static function isBlocked(int $userId, Family $family, bool $forceMove): bool
    {
        $user = User::query()->find($userId);

        if (! $user instanceof User) {
            return true;
        }

        if ($user->family_id === null) {
            return false;
        }

        if ((int) $user->family_id === (int) $family->id) {
            return true;
        }

        return ! $forceMove;
    }

    /**
     * @return Builder<User>
     */
    public static function linkableQuery(Family $family, ?string $search = null): Builder
    {
        return User::query()
            ->householdEligible()
            ->with(['family.admin'])
            ->where(function (Builder $query) use ($family): void {
                $query->whereNull('family_id')
                    ->orWhere('family_id', '!=', $family->id);
            })
            ->when(filled($search), function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderByRaw('CASE WHEN family_id IS NULL THEN 0 ELSE 1 END')
            ->orderBy('last_name')
            ->orderBy('first_name');
    }
}
