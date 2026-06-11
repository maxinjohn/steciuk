<?php

namespace App\Filament\Resources\Designations\Pages;

use App\Filament\Resources\Designations\DesignationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListDesignations extends ListRecords
{
    protected static string $resource = DesignationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->visible(fn (): bool => auth()->user()?->can('create', DesignationResource::getModel()) ?? false),
        ];
    }
}
