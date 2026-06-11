<?php

namespace App\Filament\Resources\GalleryAlbums\Schemas;

use App\Enums\PublishStatus;
use App\Filament\Support\SecureFileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GalleryAlbumForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('description')
                    ->columnSpanFull(),
                SecureFileUpload::image('cover_image', 'gallery/albums'),
                TextInput::make('sort_order')
                    ->required()
                    ->numeric()
                    ->default(0),
                Select::make('status')
                    ->label('Status')
                    ->options(collect(PublishStatus::cases())->mapWithKeys(fn (PublishStatus $status) => [$status->value => $status->label()])->all())
                    ->default(PublishStatus::Draft->value)
                    ->required()
                    ->native(),
            ]);
    }
}
