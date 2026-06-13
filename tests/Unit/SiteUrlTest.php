<?php

namespace Tests\Unit;

use App\Support\SafeUrl;
use App\Support\SiteUrl;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class SiteUrlTest extends TestCase
{
    public function test_resolve_includes_app_url_port(): void
    {
        Config::set('app.url', 'http://localhost:8000');

        SiteUrl::configureRootUrl();

        $this->assertSame('http://localhost:8000/events', SafeUrl::resolve('/events'));
        $this->assertSame('http://localhost:8000', SafeUrl::resolve('/'));
    }

    public function test_menu_style_relative_paths_include_port(): void
    {
        Config::set('app.url', 'http://localhost:8765');

        SiteUrl::configureRootUrl();

        $this->assertSame('http://localhost:8765/news', SafeUrl::resolve('/news'));
    }
}
