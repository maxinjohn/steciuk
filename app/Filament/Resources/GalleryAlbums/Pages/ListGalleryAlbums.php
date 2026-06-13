<?php

namespace App\Filament\Resources\GalleryAlbums\Pages;

use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListGalleryAlbums extends ListRecords
{
    protected static string $resource = GalleryAlbumResource::class;

    public function getEmptyStateHeading(): ?string
    {
        return 'No gallery albums yet';
    }

    public function getEmptyStateDescription(): ?string
    {
        return 'Albums group photos on the public gallery page. Add a cover image, description, and publish when ready.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
