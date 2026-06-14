<?php

namespace Tests\Feature;

use App\Support\SitePaths;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicUploadSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_upload_rejects_path_traversal(): void
    {
        SitePaths::ensurePublicDiskConfigured();

        $response = $this->get('/storage/uploads/../.env');

        $response->assertForbidden();
    }

    public function test_public_upload_rejects_null_byte_in_path(): void
    {
        $response = $this->get('/storage/uploads/logo.png%00.jpg');

        $response->assertNotFound();
    }
}
