<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Models\Page;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Support\Htmlable;

class ListPages extends ListRecords
{
    protected static string $resource = PageResource::class;

    protected static ?string $title = 'Pages';

    public function getHeading(): string|Htmlable
    {
        return static::$title ?? PageResource::getPluralModelLabel();
    }

    public function getSubheading(): string|Htmlable|null
    {
        return 'Manage site pages, URLs, and published content.';
    }

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('New page')
                ->visible(fn (): bool => auth()->user()?->can('create', Page::class) ?? false),
        ];
    }
}
