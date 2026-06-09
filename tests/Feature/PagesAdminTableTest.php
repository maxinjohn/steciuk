<?php

namespace Tests\Feature;

use App\Enums\UserRole;
use App\Models\Page;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesAdminTableTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_pages_list_shows_row_actions_for_super_admin(): void
    {
        $admin = User::query()->where('email', 'admin@steciuk.org')->firstOrFail();

        $response = $this->actingAs($admin)->get(AdminPanelConfig::url('pages'));

        $response->assertOk();
        $response->assertSee('Actions', false);
        $response->assertSee('viewPublic', false);
        $response->assertSee('fi-header-heading', false);
        $response->assertSee('Manage site pages, URLs, and published content.', false);
    }

    public function test_pages_list_shows_view_only_actions_for_editor(): void
    {
        $editor = User::query()->where('email', 'editor@steciuk.org')->firstOrFail();
        $page = Page::query()->firstOrFail();

        $response = $this->actingAs($editor)->get(AdminPanelConfig::url('pages'));

        $response->assertOk();
        $response->assertSee($page->title, false);
        $response->assertSee('viewPublic', false);
        $response->assertDontSee('pages/'.$page->id.'/edit', false);
    }

    public function test_page_public_url_uses_home_route_for_homepage(): void
    {
        $home = Page::query()->where('is_home', true)->firstOrFail();

        $this->assertSame(url('/'), $home->publicUrl());
    }
}
