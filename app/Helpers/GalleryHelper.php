<?php

use Illuminate\Support\Facades\Storage;

if (! function_exists('galleryPhotoUrl')) {
    function galleryPhotoUrl(?string $path, string $variant = 'worship'): string
    {
        if ($path) {
            $normalized = ltrim($path, '/');

            if (str_starts_with($normalized, 'http')) {
                return $normalized;
            }

            if (Storage::disk('public')->exists($normalized)) {
                return asset('storage/'.$normalized);
            }
        }

        $placeholders = [
            'worship' => 'placeholder-worship.svg',
            'communion' => 'placeholder-communion.svg',
            'fellowship' => 'placeholder-fellowship.svg',
        ];

        $file = $placeholders[$variant] ?? $placeholders['worship'];

        return asset('images/gallery/'.$file);
    }
}

if (! function_exists('galleryCoverUrl')) {
    function galleryCoverUrl(?string $path, string $variant = 'worship'): string
    {
        return galleryPhotoUrl($path, $variant);
    }
}
