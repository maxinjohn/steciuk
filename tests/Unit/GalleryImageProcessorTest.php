<?php

namespace Tests\Unit;

use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Support\GalleryImageProcessor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class GalleryImageProcessorTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    public function test_process_photo_creates_optimized_and_thumbnail_files(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required for gallery image processing.');
        }

        $path = 'gallery/photos/sample.jpg';
        $this->storeSampleJpeg($path, 2400, 1600);

        $processed = GalleryImageProcessor::processPhoto($path);

        $this->assertTrue($processed);
        Storage::disk('public')->assertExists($path);
        Storage::disk('public')->assertExists(GalleryImageProcessor::thumbPathFor($path));

        [$width, $height] = getimagesize(Storage::disk('public')->path($path));
        $this->assertLessThanOrEqual(2048, max($width, $height));

        [$thumbWidth, $thumbHeight] = getimagesize(
            Storage::disk('public')->path(GalleryImageProcessor::thumbPathFor($path)),
        );
        $this->assertLessThanOrEqual(720, max($thumbWidth, $thumbHeight));
    }

    public function test_gallery_photo_model_processes_upload_on_save(): void
    {
        if (! extension_loaded('gd')) {
            $this->markTestSkipped('GD extension is required for gallery image processing.');
        }

        $album = GalleryAlbum::factory()->create();
        $path = 'gallery/photos/event.jpg';
        $this->storeSampleJpeg($path, 1800, 1200);

        GalleryPhoto::query()->create([
            'gallery_album_id' => $album->id,
            'image_path' => $path,
            'sort_order' => 0,
            'status' => 'published',
        ]);

        Storage::disk('public')->assertExists(GalleryImageProcessor::thumbPathFor($path));
    }

    public function test_gallery_cover_url_falls_back_to_first_photo(): void
    {
        $album = GalleryAlbum::factory()->create([
            'cover_image' => null,
        ]);

        GalleryPhoto::query()->create([
            'gallery_album_id' => $album->id,
            'image_path' => 'gallery/photos/first.jpg',
            'sort_order' => 0,
            'status' => 'published',
        ]);

        Storage::disk('public')->put('gallery/photos/first.jpg', 'photo');

        $album->load(['photos' => fn ($query) => $query->published()->orderBy('sort_order')->limit(1)]);

        $this->assertSame('gallery/photos/first.jpg', $album->resolvedCoverPath());
        $this->assertStringContainsString(
            'gallery/photos/first.jpg',
            galleryCoverUrl(null, 'worship', $album),
        );
    }

    public function test_gallery_photo_url_prefers_thumbnail_for_display_size(): void
    {
        Storage::disk('public')->put('gallery/photos/photo.jpg', 'full');
        Storage::disk('public')->put('gallery/photos/photo-thumb.jpg', 'thumb');

        $display = galleryPhotoUrl('gallery/photos/photo.jpg', 'worship', 'display');
        $full = galleryPhotoUrl('gallery/photos/photo.jpg', 'worship', 'full');

        $this->assertStringContainsString('photo-thumb.jpg', $display);
        $this->assertStringContainsString('photo.jpg', $full);
        $this->assertStringNotContainsString('photo-thumb.jpg', $full);
    }

    private function storeSampleJpeg(string $path, int $width, int $height): void
    {
        $image = imagecreatetruecolor($width, $height);
        $background = imagecolorallocate($image, 40, 80, 120);
        imagefilledrectangle($image, 0, 0, $width, $height, $background);

        $absolute = Storage::disk('public')->path($path);
        $directory = dirname($absolute);

        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }

        imagejpeg($image, $absolute, 90);
        imagedestroy($image);
    }
}
