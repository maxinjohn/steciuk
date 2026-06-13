<?php

namespace App\Filament\Resources\GalleryPhotos\Pages;

use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleryPhotos extends ListRecords
{
    protected static string $resource = GalleryPhotoResource::class;

    public function getEmptyStateHeading(): ?string
    {
        return 'No gallery photos yet';
    }

    public function getEmptyStateDescription(): ?string
    {
        return 'Choose an album, upload images, and set a sort order. Photos appear on the public gallery once published.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
