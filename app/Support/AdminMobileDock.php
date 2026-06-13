<?php

namespace App\Support;

use App\Models\Conversation;
use Illuminate\Support\Facades\Cache;

class AdminMobileDock
{
    /**
     * @return list<array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu', badge: int|null}>
     */
    public static function items(): array
    {
        return [
            self::homeItem(),
            ...self::slotItems(),
            self::menuItem(),
        ];
    }

    public static function unreadCount(): int
    {
        return (int) Cache::remember('admin.dock.unread.v1', 30, static fn (): int => Conversation::query()
            ->where('unread_by_admin', true)
            ->count());
    }

    public static function mobileHint(): string
    {
        $slots = collect(self::slotItems())->pluck('label')->filter()->values();

        if ($slots->isEmpty()) {
            return 'On phones and tablets, use the bottom bar for Home and Menu. Tap Menu for the full sidebar.';
        }

        return 'On phones and tablets, use the bottom bar for Home, '.$slots->join(', ', ' and ').', and Menu.';
    }

    /**
     * @return array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu', badge: int|null}
     */
    private static function homeItem(): array
    {
        return [
            'id' => 'home',
            'label' => 'Home',
            'url' => \App\Filament\Pages\Dashboard::getUrl(),
            'type' => 'link',
            'icon' => 'home',
            'isActive' => request()->path() === AdminPanelConfig::path(),
            'badge' => null,
        ];
    }

    /**
     * @return list<array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu', badge: int|null}>
     */
    private static function slotItems(): array
    {
        $preferred = [
            ['id' => 'worship', 'label' => 'Worship', 'resource' => \App\Filament\Resources\Services\ServiceResource::class, 'patterns' => ['services*', 'sermons*']],
            ['id' => 'events', 'label' => 'Events', 'resource' => \App\Filament\Resources\Events\EventResource::class, 'patterns' => ['events*']],
        ];

        $fallback = [
            ['id' => 'inbox', 'label' => 'Inbox', 'resource' => \App\Filament\Resources\Conversations\ConversationResource::class, 'patterns' => ['conversations*', 'form-submissions*']],
            ['id' => 'people', 'label' => 'People', 'resource' => \App\Filament\Resources\Users\UserResource::class, 'patterns' => ['users*', 'families*']],
            ['id' => 'site', 'label' => 'Site', 'resource' => \App\Filament\Resources\Pages\PageResource::class, 'patterns' => ['pages*', 'menu-items*', 'news*']],
        ];

        $slots = [];

        foreach ($preferred as $candidate) {
            $slot = self::candidateToSlot($candidate);

            if ($slot !== null) {
                $slots[] = $slot;
            }
        }

        foreach ($fallback as $candidate) {
            if (count($slots) >= 2) {
                break;
            }

            $slot = self::candidateToSlot($candidate);

            if ($slot !== null && ! self::hasId($slots, $slot['id'])) {
                $slots[] = $slot;
            }
        }

        return array_slice($slots, 0, 2);
    }

    /**
     * @param  array{id: string, label: string, resource: class-string<\Filament\Resources\Resource>, patterns: list<string>}  $candidate
     * @return array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu', badge: int|null}|null
     */
    private static function candidateToSlot(array $candidate): ?array
    {
        /** @var class-string<\Filament\Resources\Resource> $resourceClass */
        $resourceClass = $candidate['resource'];

        if (! $resourceClass::canViewAny()) {
            return null;
        }

        $badge = $candidate['id'] === 'inbox' ? self::unreadCount() : null;

        return [
            'id' => $candidate['id'],
            'label' => $candidate['label'],
            'url' => $resourceClass::getUrl('index'),
            'type' => 'link',
            'icon' => $candidate['id'],
            'isActive' => self::pathIsActive($candidate['patterns']),
            'badge' => $badge > 0 ? $badge : null,
        ];
    }

    /**
     * @param  list<array{id: string}>  $slots
     */
    private static function hasId(array $slots, string $id): bool
    {
        foreach ($slots as $slot) {
            if ($slot['id'] === $id) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu', badge: int|null}
     */
    private static function menuItem(): array
    {
        return [
            'id' => 'menu',
            'label' => 'Menu',
            'url' => null,
            'type' => 'menu',
            'icon' => 'menu',
            'isActive' => false,
            'badge' => null,
        ];
    }

    /**
     * @param  list<string>  $patterns
     */
    private static function pathIsActive(array $patterns): bool
    {
        $prefix = AdminPanelConfig::path();

        foreach ($patterns as $pattern) {
            if (request()->is($prefix.'/'.$pattern)) {
                return true;
            }
        }

        return false;
    }
}
