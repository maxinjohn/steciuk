<?php

namespace App\Filament\Resources\MenuItems\Schemas;

use App\Enums\MenuLocation;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class MenuItemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required(),
                Select::make('menu_location')
                    ->options(MenuLocation::class)
                    ->required(),
                Select::make('parent_id')
                    ->label('Parent menu item')
                    ->relationship('parent', 'label')
                    ->searchable()
                    ->preload(),
                Select::make('page_id')
                    ->label('Linked page')
                    ->relationship('page', 'title')
                    ->searchable()
                    ->preload(),
                TextInput::make('url')
                    ->url()
                    ->label('Custom URL'),
                Toggle::make('is_external'),
                TextInput::make('target')
                    ->default('_self')
                    ->required(),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Toggle::make('is_visible')
                    ->default(true)
                    ->required(),
            ]);
    }
}
