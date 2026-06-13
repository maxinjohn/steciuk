<?php

namespace App\Filament\Support;

use Filament\Tables\Columns\ImageColumn;

class AdminMediaColumn
{
    public static function placeholderUrl(): string
    {
        return asset('images/admin/media-placeholder.svg');
    }

    public static function storageImage(string $name, ?string $label = null): ImageColumn
    {
        $column = ImageColumn::make($name)
            ->disk('public')
            ->square()
            ->imageSize(56)
            ->defaultImageUrl(fn (): string => self::placeholderUrl());

        if ($label !== null) {
            $column->label($label);
        }

        return $column;
    }
}
