<?php

namespace App\Filament\Resources\Sermons\Pages;

use App\Filament\Resources\Sermons\Concerns\HandlesSermonMedia;
use App\Filament\Resources\Sermons\SermonResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSermon extends EditRecord
{
    use HandlesSermonMedia;

    protected static string $resource = SermonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
