<?php

namespace App\Support;

use Illuminate\Support\Facades\Storage;
use Throwable;

class GalleryImageProcessor
{
    public const THUMB_SUFFIX = '-thumb';

    private const PHOTO_MAX_EDGE = 2048;

    private const COVER_MAX_EDGE = 1600;

    private const THUMB_MAX_EDGE = 720;

    private const JPEG_QUALITY = 85;

    public static function thumbPathFor(string $relative): string
    {
        $relative = ltrim($relative, '/');
        $directory = pathinfo($relative, PATHINFO_DIRNAME);
        $filename = pathinfo($relative, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION) ?: 'jpg');

        if (str_ends_with($filename, self::THUMB_SUFFIX)) {
            return $relative;
        }

        $thumb = $filename.self::THUMB_SUFFIX.'.'.$extension;

        return $directory !== '.' ? $directory.'/'.$thumb : $thumb;
    }

    public static function processPhoto(string $relative): bool
    {
        return static::process($relative, self::PHOTO_MAX_EDGE);
    }

    public static function processCover(string $relative): bool
    {
        return static::process($relative, self::COVER_MAX_EDGE);
    }

    public static function deleteDerivatives(?string $relative): void
    {
        if ($relative === null || $relative === '') {
            return;
        }

        $disk = Storage::disk('public');
        $thumb = static::thumbPathFor($relative);

        if ($disk->exists($thumb)) {
            $disk->delete($thumb);
        }
    }

    public static function deleteStoredImage(?string $relative): void
    {
        if ($relative === null || $relative === '') {
            return;
        }

        $disk = Storage::disk('public');

        static::deleteDerivatives($relative);

        if ($disk->exists($relative)) {
            $disk->delete($relative);
        }
    }

    private static function process(string $relative, int $maxEdge): bool
    {
        $relative = ltrim($relative, '/');

        if ($relative === '' || str_contains($relative, self::THUMB_SUFFIX.'.')) {
            return false;
        }

        if (! extension_loaded('gd')) {
            return false;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($relative)) {
            return false;
        }

        $extension = strtolower(pathinfo($relative, PATHINFO_EXTENSION));

        if ($extension === 'gif' || $extension === 'svg') {
            return false;
        }

        try {
            $absolute = $disk->path($relative);
            $source = static::loadImage($absolute, $extension);

            if ($source === null) {
                return false;
            }

            $width = imagesx($source);
            $height = imagesy($source);
            $optimized = static::resizeImage($source, $width, $height, $maxEdge, $maxEdge);
            imagedestroy($source);

            static::saveImage($optimized, $absolute, $extension);
            imagedestroy($optimized);

            $thumbRelative = static::thumbPathFor($relative);
            $thumbAbsolute = $disk->path($thumbRelative);
            static::ensureDirectory(dirname($thumbAbsolute));

            $sourceForThumb = static::loadImage($absolute, $extension);

            if ($sourceForThumb === null) {
                return true;
            }

            $thumbWidth = imagesx($sourceForThumb);
            $thumbHeight = imagesy($sourceForThumb);
            $thumb = static::resizeImage(
                $sourceForThumb,
                $thumbWidth,
                $thumbHeight,
                self::THUMB_MAX_EDGE,
                self::THUMB_MAX_EDGE,
            );
            imagedestroy($sourceForThumb);

            static::saveImage($thumb, $thumbAbsolute, $extension);
            imagedestroy($thumb);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * @return \GdImage|null
     */
    private static function loadImage(string $absolute, string $extension): mixed
    {
        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($absolute) ?: null,
            'webp' => function_exists('imagecreatefromwebp') ? (@imagecreatefromwebp($absolute) ?: null) : null,
            'png' => @imagecreatefrompng($absolute) ?: null,
            default => null,
        };
    }

    /**
     * @param  \GdImage  $source
     * @return \GdImage
     */
    private static function resizeImage($source, int $width, int $height, int $maxWidth, int $maxHeight)
    {
        $scale = min($maxWidth / $width, $maxHeight / $height, 1);
        $targetWidth = max(1, (int) round($width * $scale));
        $targetHeight = max(1, (int) round($height * $scale));

        $canvas = imagecreatetruecolor($targetWidth, $targetHeight);
        imagealphablending($canvas, false);
        imagesavealpha($canvas, true);
        $transparent = imagecolorallocatealpha($canvas, 0, 0, 0, 127);
        imagefilledrectangle($canvas, 0, 0, $targetWidth, $targetHeight, $transparent);
        imagecopyresampled(
            $canvas,
            $source,
            0,
            0,
            0,
            0,
            $targetWidth,
            $targetHeight,
            $width,
            $height,
        );

        return $canvas;
    }

    /**
     * @param  \GdImage  $image
     */
    private static function saveImage($image, string $absolute, string $extension): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);

        match ($extension) {
            'jpg', 'jpeg' => imagejpeg($image, $absolute, self::JPEG_QUALITY),
            'webp' => function_exists('imagewebp')
                ? imagewebp($image, $absolute, self::JPEG_QUALITY)
                : imagepng($image, $absolute, 6),
            default => imagepng($image, $absolute, 6),
        };
    }

    private static function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
