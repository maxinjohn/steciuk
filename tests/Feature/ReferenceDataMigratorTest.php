<?php

namespace Tests\Feature;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\User;
use App\Models\Setting;
use App\Enums\PublishStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ReferenceDataMigratorTest extends TestCase
{
    use RefreshDatabase;

    public function test_migrate_applies_reference_gallery_and_pages(): void
    {
        $this->assertDatabaseHas('pages', ['slug' => 'home']);
        $this->assertDatabaseHas('gallery_albums', ['slug' => 'parish-worship-services']);
        $this->assertSame(8, GalleryPhoto::query()->count());
    }

    public function test_migrate_sync_preserves_prod_only_content_on_subsequent_migrate(): void
    {
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

        Setting::query()->updateOrCreate(
            ['key' => 'church_name'],
            ['value' => 'Custom Prod Church Name', 'group' => 'general'],
        );

        $admin = User::query()->where('email', 'admin@steciuk.org')->firstOrFail();
        $admin->password = 'prod-secret-password';
        $admin->save();

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('news', ['slug' => 'prod-only-announcement']);
        $this->assertSame('Custom Prod Church Name', Setting::query()->where('key', 'church_name')->value('value'));
        $this->assertTrue(Hash::check('prod-secret-password', $admin->fresh()->password));
        $this->assertDatabaseHas('gallery_albums', ['slug' => 'fellowship-community-events']);
    }

    public function test_migrate_sync_adds_missing_reference_album_without_deleting_custom_gallery(): void
    {
        $customAlbum = GalleryAlbum::query()->create([
            'title' => 'Custom Parish Album',
            'slug' => 'custom-parish-album',
            'description' => 'Added in production',
            'sort_order' => 99,
            'status' => 'published',
        ]);

        GalleryAlbum::query()->where('slug', 'parish-worship-services')->delete();

        $this->artisan('migrate', ['--force' => true])->assertSuccessful();

        $this->assertDatabaseHas('gallery_albums', ['slug' => 'parish-worship-services']);
        $this->assertDatabaseHas('gallery_albums', ['slug' => 'custom-parish-album', 'id' => $customAlbum->id]);
    }
}
