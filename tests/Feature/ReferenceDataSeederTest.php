<?php

namespace Tests\Feature;

use App\Enums\PublishStatus;
use App\Models\MenuItem;
use App\Models\News;
use App\Models\Setting;
use App\Models\User;
use App\Support\SeedConfig;
use Database\Seeders\ReferenceDataSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReferenceDataSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_bootstrap_creates_reference_data(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);

        $this->seed(ReferenceDataSeeder::class);

        $this->assertDatabaseHas('users', ['email' => 'admin@steciuk.org']);
        $this->assertDatabaseHas('pages', ['slug' => 'home']);
        $this->assertDatabaseHas('settings', ['key' => 'church_name']);
        $this->assertGreaterThan(20, MenuItem::query()->count());
    }

    public function test_sync_preserves_prod_only_news_and_settings(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $adminId = User::query()->where('email', 'admin@steciuk.org')->value('id');

        News::query()->create([
            'title' => 'Prod-only announcement',
            'slug' => 'prod-only-announcement',
            'excerpt' => 'Created in production only',
            'content' => '<p>Prod content</p>',
            'category' => 'Announcements',
            'published_at' => now(),
            'status' => PublishStatus::Published,
            'created_by' => $adminId,
            'updated_by' => $adminId,
        ]);

        Setting::query()->where('key', 'church_name')->update(['value' => 'Custom Prod Church Name']);

        $admin = User::query()->where('email', 'admin@steciuk.org')->first();
        $admin->password = 'prod-secret-password';
        $admin->save();

        config(['site.seed.mode' => SeedConfig::MODE_SYNC]);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertDatabaseHas('news', ['slug' => 'prod-only-announcement']);
        $this->assertSame('Custom Prod Church Name', Setting::query()->where('key', 'church_name')->value('value'));
        $admin = User::query()->where('email', 'admin@steciuk.org')->first();
        $this->assertTrue(Hash::check('prod-secret-password', $admin->password));
        $this->assertDatabaseHas('pages', ['slug' => 'welcome']);
    }

    public function test_sync_updates_seeded_news_from_dev_definitions(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        $original = News::query()->where('slug', 'welcome-new-website')->first();
        $this->assertNotNull($original);

        config(['site.seed.mode' => SeedConfig::MODE_SYNC]);
        $this->seed(ReferenceDataSeeder::class);

        $updated = News::query()->where('slug', 'welcome-new-website')->first();
        $this->assertNotNull($updated);
        $this->assertSame('Welcome to Our New Website', $updated->title);
    }

    public function test_sync_does_not_delete_custom_menu_items(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_BOOTSTRAP]);
        $this->seed(ReferenceDataSeeder::class);

        MenuItem::query()->create([
            'label' => 'Custom Prod Link',
            'url' => '/custom',
            'menu_location' => 'header',
            'target' => '_self',
            'sort_order' => 999,
            'is_visible' => true,
            'is_external' => false,
        ]);

        $countBefore = MenuItem::query()->count();

        config(['site.seed.mode' => SeedConfig::MODE_SYNC]);
        $this->seed(ReferenceDataSeeder::class);

        $this->assertSame($countBefore, MenuItem::query()->count());
        $this->assertDatabaseHas('menu_items', ['label' => 'Custom Prod Link', 'url' => '/custom']);
    }

    public function test_database_seeder_skips_when_mode_off(): void
    {
        config(['site.seed.mode' => SeedConfig::MODE_OFF]);

        $this->seed();

        $this->assertDatabaseCount('pages', 0);
    }
}
