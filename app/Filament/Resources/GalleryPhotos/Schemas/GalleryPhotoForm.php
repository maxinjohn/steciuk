<?php

namespace App\Filament\Resources\GalleryPhotos\Schemas;

use App\Enums\PublishStatus;
use App\Filament\Support\SecureFileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class GalleryPhotoForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('gallery_album_id')
                    ->label('Album')
                    ->relationship('album', 'title')
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Choose which album this photo belongs to.'),
                TextInput::make('title')
                    ->helperText('Optional title shown in the gallery.'),
                Textarea::make('caption')
                    ->rows(3)
                    ->columnSpanFull(),
                SecureFileUpload::image('image_path', 'gallery/photos')->required(),
                SecureFileUpload::image('bulk_images', 'gallery/photos')
                    ->label('Additional photos')
                    ->multiple()
                    ->maxFiles(20)
                    ->dehydrated(false)
                    ->visibleOn('create')
                    ->helperText('Optional. Each extra file is saved as its own photo in this album.'),
                TextInput::make('alt_text')
                    ->label('Alt text')
                    ->helperText('Describe the image for accessibility.'),
                TextInput::make('sort_order')
                    ->label('Sort order')
                    ->required()
                    ->numeric()
                    ->default(0)
                    ->helperText('Lower numbers appear first.'),
                Select::make('status')
                    ->label('Status')
                    ->options(collect(PublishStatus::cases())->mapWithKeys(fn (PublishStatus $status) => [$status->value => $status->label()])->all())
                    ->default(PublishStatus::Draft->value)
                    ->required()
                    ->native(false),
            ]);
    }
}
