<?php

namespace App\Services;

use App\Models\Panel;
use App\Models\User;
use Illuminate\Support\Collection;

class PanelMembershipService
{
    public function attachMember(Panel $panel, User $user, ?string $notes = null): void
    {
        if ($panel->members()->whereKey($user->id)->exists()) {
            return;
        }

        $panel->members()->attach($user->id, [
            'notes' => $notes,
            'sort_order' => $panel->members()->count() + 1,
        ]);
    }

    public function detachMember(Panel $panel, User $user): void
    {
        $panel->members()->detach($user->id);
    }

    /**
     * @param  list<int|string>|Collection<int, int|string>  $panelIds
     */
    public function syncUserPanels(User $user, array|Collection $panelIds): void
    {
        $ids = collect($panelIds)
            ->filter(fn ($id): bool => filled($id))
            ->map(fn ($id): int => (int) $id)
            ->unique()
            ->values();

        $existing = $user->panels()
            ->get()
            ->keyBy('id');

        $sync = [];
        $order = 1;

        foreach ($ids as $panelId) {
            $panel = $existing->get($panelId);

            $sync[$panelId] = [
                'sort_order' => (int) ($panel?->pivot?->sort_order ?? $order),
                'notes' => $panel?->pivot?->notes,
            ];

            $order++;
        }

        $user->panels()->sync($sync);
    }
}
