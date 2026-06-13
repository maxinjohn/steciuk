<?php

namespace App\Http\Controllers;

use App\Support\SitePaths;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicUploadController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '' || str_contains($path, '..')) {
            abort(404);
        }

        SitePaths::ensurePublicDiskConfigured();

        $absolute = SitePaths::publicUploadsRoot().'/'.$path;

        if (! is_file($absolute) || ! is_readable($absolute)) {
            abort(404);
        }

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800',
        ]);
    }
}
