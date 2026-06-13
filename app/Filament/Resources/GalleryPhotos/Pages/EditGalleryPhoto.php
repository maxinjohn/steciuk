<?php

namespace App\Filament\Resources\GalleryPhotos\Pages;

use App\Filament\Resources\GalleryPhotos\GalleryPhotoResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\GalleryPhoto;
use Filament\Resources\Pages\EditRecord;

class EditGalleryPhoto extends EditRecord
{
    protected static string $resource = GalleryPhotoResource::class;

    /** @var list<string> */
    protected array $extraImagePaths = [];

    protected function mutateFormDataBeforeSave(array $data): array
    {
        $state = $this->form->getRawState();
        $bulk = $state['bulk_images'] ?? [];

        if (is_array($bulk)) {
            $this->extraImagePaths = array_values(array_filter($bulk, fn ($path): bool => filled($path)));
        }

        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->extraImagePaths === []) {
            return;
        }

        /** @var GalleryPhoto $photo */
        $photo = $this->getRecord();
        $sortOrder = (int) $photo->sort_order;
        $status = $photo->status?->value ?? (string) $photo->getRawOriginal('status');

        foreach ($this->extraImagePaths as $index => $path) {
            GalleryPhoto::query()->create([
                'gallery_album_id' => $photo->gallery_album_id,
                'image_path' => $path,
                'sort_order' => $sortOrder + $index + 1,
                'status' => $status,
            ]);
        }
    }

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
