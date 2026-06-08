<?php

namespace App\Filament\Resources\Sermons\Concerns;

use Illuminate\Support\Facades\Storage;

trait HandlesSermonMedia
{
    protected function mutateFormDataBeforeFill(array $data): array
    {
        if ($this->record->getFirstMedia('audio')) {
            $data['audio_file'] = [$this->record->getFirstMedia('audio')->getPathRelativeToRoot()];
        }

        if ($this->record->getFirstMedia('pdf')) {
            $data['pdf_file'] = [$this->record->getFirstMedia('pdf')->getPathRelativeToRoot()];
        }

        return $data;
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        unset($data['audio_file'], $data['pdf_file']);

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        unset($data['audio_file'], $data['pdf_file']);

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->syncSermonMedia();
    }

    protected function afterSave(): void
    {
        $this->syncSermonMedia();
    }

    protected function syncSermonMedia(): void
    {
        $state = $this->form->getRawState();

        $this->syncMediaCollection('audio_file', 'audio', $state['audio_file'] ?? null);
        $this->syncMediaCollection('pdf_file', 'pdf', $state['pdf_file'] ?? null);
    }

    protected function syncMediaCollection(string $field, string $collection, mixed $files): void
    {
        if ($files === null) {
            return;
        }

        $paths = is_array($files) ? $files : [$files];

        if ($paths === []) {
            return;
        }

        $path = $paths[array_key_last($paths)];

        if (! is_string($path) || $path === '') {
            return;
        }

        if ($this->record->getFirstMedia($collection)?->getPathRelativeToRoot() === $path) {
            return;
        }

        $this->record->clearMediaCollection($collection);

        if (Storage::disk('public')->exists($path)) {
            $this->record
                ->addMedia(Storage::disk('public')->path($path))
                ->toMediaCollection($collection);
        }
    }
}
