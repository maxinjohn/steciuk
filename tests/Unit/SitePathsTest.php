<?php

namespace Tests\Unit;

use App\Support\SitePaths;
use Tests\TestCase;

class SitePathsTest extends TestCase
{
    public function test_relative_path_resolves_from_project_root(): void
    {
        $resolved = SitePaths::resolve('../site_data/database/database.sqlite');

        $this->assertSame(
            base_path('../site_data/database/database.sqlite'),
            $resolved,
        );
    }

    public function test_absolute_path_is_unchanged(): void
    {
        $path = '/var/lib/steciuk/database/database.sqlite';

        $this->assertSame($path, SitePaths::resolve($path));
    }
}
