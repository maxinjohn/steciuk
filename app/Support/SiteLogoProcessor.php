<?php

namespace App\Support;

use App\Models\Setting;
use Illuminate\Support\Facades\Storage;
use Throwable;

class SiteLogoProcessor
{
    public const MARK_SUFFIX = '-mark';

    private const WHITE_THRESHOLD = 248;

    private const VERTICAL_ASPECT = 1.08;

    private const EMBLEM_HEIGHT_RATIO = 0.78;

    private const EMBLEM_MARK_BORDER = 18;

    public static function markPathFor(string $logoRelative): string
    {
        $logoRelative = ltrim($logoRelative, '/');
        $directory = pathinfo($logoRelative, PATHINFO_DIRNAME);
        $filename = pathinfo($logoRelative, PATHINFO_FILENAME);
        $extension = strtolower(pathinfo($logoRelative, PATHINFO_EXTENSION) ?: 'png');

        if (str_ends_with($filename, self::MARK_SUFFIX)) {
            return $logoRelative;
        }

        $relative = $filename.self::MARK_SUFFIX.'.'.$extension;

        return $directory !== '.' ? $directory.'/'.$relative : $relative;
    }

    public static function process(string $logoRelative): bool
    {
        $logoRelative = ltrim($logoRelative, '/');

        if ($logoRelative === '' || str_contains($logoRelative, self::MARK_SUFFIX.'.')) {
            return false;
        }

        if (! extension_loaded('gd')) {
            return self::processWithImageMagick($logoRelative);
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($logoRelative)) {
            return false;
        }

        try {
            $absolute = $disk->path($logoRelative);
            $source = self::loadImage($absolute);

            if ($source === null) {
                return self::processWithImageMagick($logoRelative);
            }

            self::stripNearWhiteBackground($source);
            $trimmed = self::trimToContent($source);
            imagedestroy($source);

            self::saveImage($trimmed, $absolute);

            $mark = self::extractHeaderMark($trimmed);
            imagedestroy($trimmed);

            $markRelative = self::markPathFor($logoRelative);
            $markAbsolute = $disk->path($markRelative);

            self::ensureDirectory(dirname($markAbsolute));
            self::saveImage($mark, $markAbsolute);
            imagedestroy($mark);

            return true;
        } catch (Throwable) {
            return self::processWithImageMagick($logoRelative);
        }
    }

    public static function usesHeaderLockup(?string $logoPath): bool
    {
        if ($logoPath === null || trim($logoPath) === '') {
            return SiteBrandingAssets::bundledMarkExists();
        }

        $path = ltrim(trim($logoPath), '/');

        if (str_ends_with(strtolower($path), '.svg')) {
            return false;
        }

        if (SiteBrandingAssets::isParishLogo($path)) {
            return true;
        }

        if (! str_starts_with($path, 'settings/branding/')) {
            return false;
        }

        if (str_contains($path, self::MARK_SUFFIX.'.')) {
            return false;
        }

        return ! str_ends_with(strtolower($path), '.svg');
    }

    public static function headerMarkUrl(?string $logoPath): ?string
    {
        $logoPath = ltrim((string) ($logoPath ?? ''), '/');

        if ($logoPath !== '') {
            $markPath = self::markPathFor($logoPath);
            $disk = Storage::disk('public');

            if ($disk->exists($markPath)) {
                return Setting::assetUrl($markPath)
                    ?? SitePaths::publicStorageUrl($markPath);
            }

            if (self::process($logoPath)) {
                return self::headerMarkUrl($logoPath);
            }
        }

        return null;
    }

    private static function processWithImageMagick(string $logoRelative): bool
    {
        if (! self::imageMagickAvailable()) {
            return false;
        }

        $disk = Storage::disk('public');

        if (! $disk->exists($logoRelative)) {
            return false;
        }

        $absolute = $disk->path($logoRelative);
        $markRelative = self::markPathFor($logoRelative);
        $markAbsolute = $disk->path($markRelative);

        self::ensureDirectory(dirname($markAbsolute));

        $cropRatio = (int) round(self::EMBLEM_HEIGHT_RATIO * 100);

        $logoCommand = sprintf(
            'magick %s -alpha set -channel A -evaluate set 100%% +channel -fuzz 14%% -transparent white -fuzz 6%% -transparent "#F5F5F5" PNG32:%s',
            escapeshellarg($absolute),
            escapeshellarg($absolute),
        );

        $markCommand = sprintf(
            'magick %s -gravity North -crop 100%%x%d%%+0+0 +repage -background none -gravity center -extent %%[fx:max(w,h)]x%%[fx:max(w,h)] -bordercolor none -border %dx%d PNG32:%s',
            escapeshellarg($absolute),
            $cropRatio,
            self::EMBLEM_MARK_BORDER,
            self::EMBLEM_MARK_BORDER,
            escapeshellarg($markAbsolute),
        );

        exec($logoCommand, $logoOutput, $logoCode);
        exec($markCommand, $markOutput, $markCode);

        return $logoCode === 0 && $markCode === 0 && is_file($markAbsolute);
    }

    private static function imageMagickAvailable(): bool
    {
        exec('command -v magick 2>/dev/null', $output, $code);

        return $code === 0;
    }

    /**
     * @return \GdImage|null
     */
    private static function loadImage(string $absolute): mixed
    {
        $extension = strtolower(pathinfo($absolute, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => @imagecreatefromjpeg($absolute),
            'webp' => function_exists('imagecreatefromwebp') ? @imagecreatefromwebp($absolute) : null,
            default => @imagecreatefrompng($absolute),
        };
    }

    /**
     * @param  \GdImage  $image
     */
    private static function stripNearWhiteBackground($image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $rgba = imagecolorat($image, $x, $y);
                $red = ($rgba >> 16) & 0xFF;
                $green = ($rgba >> 8) & 0xFF;
                $blue = $rgba & 0xFF;
                $alpha = ($rgba & 0x7F000000) >> 24;

                if ($alpha === 127) {
                    continue;
                }

                if ($red >= self::WHITE_THRESHOLD && $green >= self::WHITE_THRESHOLD && $blue >= self::WHITE_THRESHOLD) {
                    $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
                    imagesetpixel($image, $x, $y, $transparent);
                }
            }
        }
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private static function trimToContent($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $minX = $width;
        $minY = $height;
        $maxX = 0;
        $maxY = 0;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $alpha = (imagecolorat($image, $x, $y) & 0x7F000000) >> 24;

                if ($alpha < 127) {
                    $minX = min($minX, $x);
                    $minY = min($minY, $y);
                    $maxX = max($maxX, $x);
                    $maxY = max($maxY, $y);
                }
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            return self::cloneImage($image);
        }

        $padding = 2;
        $minX = max(0, $minX - $padding);
        $minY = max(0, $minY - $padding);
        $maxX = min($width - 1, $maxX + $padding);
        $maxY = min($height - 1, $maxY + $padding);
        $cropWidth = $maxX - $minX + 1;
        $cropHeight = $maxY - $minY + 1;

        $trimmed = imagecreatetruecolor($cropWidth, $cropHeight);
        imagealphablending($trimmed, false);
        imagesavealpha($trimmed, true);
        $transparent = imagecolorallocatealpha($trimmed, 0, 0, 0, 127);
        imagefilledrectangle($trimmed, 0, 0, $cropWidth, $cropHeight, $transparent);
        imagecopy($trimmed, $image, 0, 0, $minX, $minY, $cropWidth, $cropHeight);

        return $trimmed;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private static function extractHeaderMark($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $originX = 0;
        $originY = 0;
        $cropWidth = $width;
        $cropHeight = $height;

        if ($height > $width * self::VERTICAL_ASPECT) {
            $cropHeight = (int) min($height, max($width, round($height * self::EMBLEM_HEIGHT_RATIO)));
        } elseif ($width > $height * self::VERTICAL_ASPECT) {
            $cropWidth = $height;
            $originX = (int) max(0, floor(($width - $cropWidth) / 2));
        }

        $cropped = imagecreatetruecolor($cropWidth, $cropHeight);
        imagealphablending($cropped, false);
        imagesavealpha($cropped, true);
        $transparent = imagecolorallocatealpha($cropped, 0, 0, 0, 127);
        imagefilledrectangle($cropped, 0, 0, $cropWidth, $cropHeight, $transparent);
        imagecopy($cropped, $image, 0, 0, $originX, $originY, $cropWidth, $cropHeight);

        $square = max($cropWidth, $cropHeight);
        $padding = self::EMBLEM_MARK_BORDER;
        $canvasSize = $square + ($padding * 2);
        $mark = imagecreatetruecolor($canvasSize, $canvasSize);
        imagealphablending($mark, false);
        imagesavealpha($mark, true);
        imagefilledrectangle($mark, 0, 0, $canvasSize, $canvasSize, $transparent);
        $offsetX = (int) floor(($canvasSize - $cropWidth) / 2);
        $offsetY = (int) floor(($canvasSize - $cropHeight) / 2);
        imagecopy($mark, $cropped, $offsetX, $offsetY, 0, 0, $cropWidth, $cropHeight);
        imagedestroy($cropped);

        return $mark;
    }

    /**
     * @param  \GdImage  $image
     * @return \GdImage
     */
    private static function cloneImage($image)
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $clone = imagecreatetruecolor($width, $height);
        imagealphablending($clone, false);
        imagesavealpha($clone, true);
        imagecopy($clone, $image, 0, 0, 0, 0, $width, $height);

        return $clone;
    }

    /**
     * @param  \GdImage  $image
     */
    private static function saveImage($image, string $absolute): void
    {
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagepng($image, $absolute, 6);
    }

    private static function ensureDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            mkdir($directory, 0755, true);
        }
    }
}
