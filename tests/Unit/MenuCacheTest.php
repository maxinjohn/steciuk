<?php

namespace Tests\Unit;

use App\Enums\MenuLocation;
use App\Services\MenuCache;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MenuCacheTest extends TestCase
{
    public function test_load_all_rebuilds_invalid_cached_menu_payload(): void
    {
        Cache::put('menu.trees.all.v4', ['header' => 'broken'], now()->addHour());

        $trees = MenuCache::loadAll();

        $this->assertArrayHasKey(MenuLocation::Header->value, $trees);
        $this->assertArrayHasKey(MenuLocation::Mobile->value, $trees);
        $this->assertInstanceOf(\Illuminate\Support\Collection::class, $trees[MenuLocation::Header->value]);
    }
}
