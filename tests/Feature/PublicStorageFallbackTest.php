<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PublicStorageFallbackTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_storage_route_serves_uploaded_files(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('settings/branding/steci-parish-logo.png', 'logo-bytes');

        $this->get('/storage/settings/branding/steci-parish-logo.png')
            ->assertOk();
    }

    public function test_public_storage_route_rejects_path_traversal(): void
    {
        $this->get('/storage/../.env')->assertForbidden();
    }

    public function test_public_storage_route_uses_configured_disk_root(): void
    {
        $root = storage_path('framework/testing/public-route-'.bin2hex(random_bytes(4)));

        config(['filesystems.disks.public.root' => $root]);

        File::ensureDirectoryExists($root.'/settings/branding');
        file_put_contents($root.'/settings/branding/steci-parish-logo.png', 'logo-bytes');

        $this->get('/storage/settings/branding/steci-parish-logo.png')
            ->assertOk();

        File::deleteDirectory($root);
    }
}
