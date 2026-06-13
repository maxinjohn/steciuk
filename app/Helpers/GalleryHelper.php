<?php

use App\Models\GalleryAlbum;
use App\Support\GalleryImageProcessor;
use App\Support\SitePaths;
use Illuminate\Support\Facades\Storage;

if (! function_exists('galleryPlaceholderUrl')) {
    function galleryPlaceholderUrl(string $variant = 'worship'): string
    {
        $placeholders = [
            'worship' => 'placeholder-worship.svg',
            'communion' => 'placeholder-communion.svg',
            'fellowship' => 'placeholder-fellowship.svg',
        ];

        $file = $placeholders[$variant] ?? $placeholders['worship'];

        return asset('images/gallery/'.$file);
    }
}

if (! function_exists('galleryResolvedPath')) {
    function galleryResolvedPath(?string $path, string $size = 'display'): ?string
    {
        if ($path === null || $path === '') {
            return null;
        }

        $normalized = ltrim($path, '/');

        if (str_starts_with($normalized, 'http')) {
            return $normalized;
        }

        $disk = Storage::disk('public');

        if ($size === 'display') {
            $thumb = GalleryImageProcessor::thumbPathFor($normalized);

            if ($disk->exists($thumb)) {
                return $thumb;
            }
        }

        return $disk->exists($normalized) ? $normalized : null;
    }
}

if (! function_exists('galleryPhotoUrl')) {
    function galleryPhotoUrl(?string $path, string $variant = 'worship', string $size = 'display'): string
    {
        $resolved = galleryResolvedPath($path, $size);

        if ($resolved !== null) {
            if (str_starts_with($resolved, 'http')) {
                return $resolved;
            }

            return public_upload_url($resolved) ?? SitePaths::publicStorageUrl($resolved);
        }

        return galleryPlaceholderUrl($variant);
    }
}

if (! function_exists('galleryCoverUrl')) {
    function galleryCoverUrl(?string $path, string $variant = 'worship', ?GalleryAlbum $album = null): string
    {
        if (($path === null || $path === '') && $album !== null) {
            $path = $album->resolvedCoverPath();
        }

        return galleryPhotoUrl($path, $variant, 'display');
    }
}
