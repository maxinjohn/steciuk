<?php

namespace App\Http\Controllers;

use App\Support\SitePaths;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PublicUploadController extends Controller
{
    public function __invoke(string $path): BinaryFileResponse
    {
        $path = str_replace('\\', '/', $path);

        if ($path === '' || str_contains($path, '..') || str_contains($path, "\0")) {
            abort(404);
        }

        SitePaths::ensurePublicDiskConfigured();

        $root = realpath(SitePaths::publicUploadsRoot());

        if ($root === false) {
            abort(404);
        }

        $absolute = realpath($root.'/'.$path);

        if ($absolute === false || ! str_starts_with($absolute, $root.DIRECTORY_SEPARATOR)) {
            abort(404);
        }

        if (! is_file($absolute) || ! is_readable($absolute)) {
            abort(404);
        }

        $mime = mime_content_type($absolute) ?: 'application/octet-stream';

        return response()->file($absolute, [
            'Content-Type' => $mime,
            'Cache-Control' => 'public, max-age=604800',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
