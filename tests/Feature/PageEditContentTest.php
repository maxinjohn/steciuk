<?php

namespace Tests\Feature;

use App\Enums\ContentBlockType;
use App\Enums\UserRole;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\RelationManagers\ContentBlocksRelationManager;
use App\Models\Page;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class PageEditContentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_home_page_edit_explains_page_sections(): void
    {
        $admin = User::query()->where('email', 'admin@steciuk.org')->firstOrFail();
        $home = Page::query()->where('is_home', true)->with('contentBlocks')->firstOrFail();
        $hero = $home->contentBlocks->first(fn ($block) => $block->type === ContentBlockType::Hero);

        $this->assertNotNull($hero);
        $this->assertSame('Word · Worship · Witness', $hero->content['headline'] ?? null);

        $response = $this->actingAs($admin)->get(AdminPanelConfig::url('pages/'.$home->id.'/edit'));

        $response->assertOk();
        $response->assertSee('Page Sections', false);
        $response->assertSee('Hero Banner', false);
    }

    public function test_home_page_section_editor_shows_hero_fields(): void
    {
        $admin = User::factory()->create(['role' => UserRole::SuperAdmin]);
        $home = Page::query()->where('is_home', true)->firstOrFail();
        $hero = $home->contentBlocks()->where('type', ContentBlockType::Hero)->firstOrFail();

        Livewire::actingAs($admin)
            ->test(ContentBlocksRelationManager::class, [
                'ownerRecord' => $home,
                'pageClass' => EditPage::class,
            ])
            ->mountTableAction('edit', $hero)
            ->assertSchemaStateSet([
                'type' => 'hero',
                'content.headline' => 'Word · Worship · Witness',
                'content.eyebrow' => 'St. Thomas Evangelical Church of India',
            ]);
    }

    public function test_content_block_type_matching_handles_enum_values(): void
    {
        $hero = Page::query()
            ->where('is_home', true)
            ->firstOrFail()
            ->contentBlocks()
            ->where('type', ContentBlockType::Hero)
            ->firstOrFail();

        $this->assertSame(ContentBlockType::Hero, $hero->type);
        $this->assertNotEmpty($hero->content['headline'] ?? null);
        $this->assertNotEmpty($hero->content['stats'] ?? null);
    }
}
