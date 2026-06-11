<?php

namespace App\Filament\Resources\GalleryPhotos\Pages;

use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\GalleryPhoto;
use Filament\Resources\Pages\EditRecord;

class EditGalleryPhoto extends EditRecord
{
    protected static string $resource = GalleryPhotoResource::class;

    protected function getHeaderActions(): array
    {
        $photo = fn (): GalleryPhoto => $this->getRecord();

        return PublishWorkflowActions::headerActions(
            $photo,
            fn (GalleryPhoto $record): ?string => $record->album?->slug
                ? route('gallery.show', $record->album->slug)
                : null,
        );
    }
}
