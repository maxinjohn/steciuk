<?php

namespace App\Support;

use App\Filament\Pages\Dashboard;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\Pages\PageResource;
use App\Filament\Resources\Services\ServiceResource;
use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Resource;

class AdminMobileDock
{
    /**
     * @return list<array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu'}>
     */
    public static function items(): array
    {
        return [
            self::homeItem(),
            ...self::slotItems(),
            self::menuItem(),
        ];
    }

    /**
     * @return array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu'}
     */
    private static function homeItem(): array
    {
        return [
            'id' => 'home',
            'label' => 'Home',
            'url' => Dashboard::getUrl(),
            'type' => 'link',
            'icon' => 'home',
            'isActive' => request()->path() === AdminPanelConfig::path(),
        ];
    }

    /**
     * @return list<array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu'}>
     */
    private static function slotItems(): array
    {
        $preferred = [
            ['id' => 'worship', 'label' => 'Worship', 'resource' => ServiceResource::class, 'patterns' => ['services*', 'sermons*']],
            ['id' => 'events', 'label' => 'Events', 'resource' => EventResource::class, 'patterns' => ['events*']],
        ];

        $fallback = [
            ['id' => 'inbox', 'label' => 'Inbox', 'resource' => ConversationResource::class, 'patterns' => ['conversations*', 'form-submissions*']],
            ['id' => 'people', 'label' => 'People', 'resource' => UserResource::class, 'patterns' => ['users*', 'families*']],
            ['id' => 'site', 'label' => 'Site', 'resource' => PageResource::class, 'patterns' => ['pages*', 'menu-items*', 'news*']],
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
     * @param  array{id: string, label: string, resource: class-string<Resource>, patterns: list<string>}  $candidate
     * @return array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu'}|null
     */
    private static function candidateToSlot(array $candidate): ?array
    {
        /** @var class-string<Resource> $resourceClass */
        $resourceClass = $candidate['resource'];

        if (! $resourceClass::canViewAny()) {
            return null;
        }

        return [
            'id' => $candidate['id'],
            'label' => $candidate['label'],
            'url' => $resourceClass::getUrl('index'),
            'type' => 'link',
            'icon' => $candidate['id'],
            'isActive' => self::pathIsActive($candidate['patterns']),
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
     * @return array{id: string, label: string, url: string|null, icon: string, isActive: bool, type: 'link'|'menu'}
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
