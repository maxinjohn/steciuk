<?php

namespace App\Filament\Support;

use App\Models\Panel;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;

class PanelMemberOptions
{
    /**
     * @return array<int, string>
     */
    public static function options(Panel $panel, ?string $search = null, int $limit = 50): array
    {
        return self::eligibleMembersQuery($panel, $search)
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => self::label($user),
            ])
            ->all();
    }

    public static function label(User $user): string
    {
        return trim($user->displayFullName().' · '.($user->email ?? 'no email'));
    }

    public static function labelForId(int $userId): ?string
    {
        $user = User::query()->find($userId);

        return $user instanceof User ? self::label($user) : null;
    }

    /**
     * @return Builder<User>
     */
    public static function eligibleMembersQuery(Panel $panel, ?string $search = null): Builder
    {
        return User::query()
            ->when(filled($search), function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->where('is_active', true)
            ->whereDoesntHave('panels', fn (Builder $query): Builder => $query->where('panels.id', $panel->id))
            ->orderBy('last_name')
            ->orderBy('first_name');
    }
}
