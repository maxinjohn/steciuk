<?php

namespace Tests\Feature;

use App\Database\ReferenceSiteContentMigrator;
use App\Enums\ContentBlockType;
use App\Models\ContentBlock;
use App\Models\Page;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class HomeNewsListBlockTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrator_upgrades_home_news_block_to_news_list(): void
    {
        $this->seed(ReferenceDataSeeder::class);

        $homeId = Page::query()->where('slug', 'home')->value('id');

        ContentBlock::query()
            ->where('page_id', $homeId)
            ->where('seed_key', 'news')
            ->update(['type' => ContentBlockType::TextImage]);

        ReferenceSiteContentMigrator::apply();

        $news = ContentBlock::query()
            ->where('page_id', $homeId)
            ->where('seed_key', 'news')
            ->first();

        $this->assertNotNull($news);
        $this->assertSame(ContentBlockType::NewsList, $news->type);
        $this->assertSame(3, $news->content['limit'] ?? null);
    }
}
