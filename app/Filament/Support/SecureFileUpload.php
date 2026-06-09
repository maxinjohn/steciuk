<?php

namespace App\Filament\Support;

use Filament\Forms\Components\FileUpload;

class SecureFileUpload
{
    /**
     * @param  list<string>  $types
     */
    public static function image(
        string $name,
        string $directory,
        int $maxSizeKb = 5120,
        array $types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'],
    ): FileUpload {
        return FileUpload::make($name)
            ->image()
            ->disk('public')
            ->directory($directory)
            ->maxSize($maxSizeKb)
            ->acceptedFileTypes($types);
    }

    /**
     * @param  list<string>  $types
     */
    public static function file(
        string $name,
        string $directory,
        int $maxSizeKb,
        array $types,
    ): FileUpload {
        return FileUpload::make($name)
            ->disk('public')
            ->directory($directory)
            ->maxSize($maxSizeKb)
            ->acceptedFileTypes($types);
    }
}
