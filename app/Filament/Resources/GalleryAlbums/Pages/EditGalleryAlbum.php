<?php

namespace App\Filament\Resources\GalleryAlbums\Pages;

use App\Filament\Resources\GalleryAlbums\GalleryAlbumResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\GalleryAlbum;
use Filament\Resources\Pages\EditRecord;

class EditGalleryAlbum extends EditRecord
{
    protected static string $resource = GalleryAlbumResource::class;

    protected function getHeaderActions(): array
    {
        return PublishWorkflowActions::headerActions(
            fn (): GalleryAlbum => $this->getRecord(),
            fn (GalleryAlbum $album): string => route('gallery.show', $album->slug),
        );
    }
}
