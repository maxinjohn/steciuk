<?php

namespace App\Filament\Support;

use App\Support\NavigationMenuCatalog;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;

final class MenuPlacementFields
{
    /**
     * @return list<\Filament\Forms\Components\Component>
     */
    public static function schema(string $defaultParent = ''): array
    {
        return [
            Section::make('Navigation menu')
                ->description('Control whether this item appears in the site header and mobile menus. Items not shown here remain available on overview pages (e.g. Ministries index).')
                ->schema([
                    Toggle::make('show_in_menu')
                        ->label('Show in main menu')
                        ->live()
                        ->helperText('When off, the page is hidden from header/mobile navigation but still reachable by URL and overview listings.'),
                    Select::make('menu_parent_seed_key')
                        ->label('Menu section')
                        ->options(NavigationMenuCatalog::parentLabels())
                        ->default($defaultParent)
                        ->visible(fn (Get $get): bool => (bool) $get('show_in_menu'))
                        ->helperText('Choose which dropdown this link appears under. Use “Top level” for items like Events or News.'),
                    TextInput::make('menu_label')
                        ->label('Menu label override')
                        ->maxLength(120)
                        ->visible(fn (Get $get): bool => (bool) $get('show_in_menu'))
                        ->helperText('Optional. Leave blank to use the page title.'),
                    TextInput::make('menu_sort_order')
                        ->label('Menu sort order')
                        ->numeric()
                        ->minValue(0)
                        ->visible(fn (Get $get): bool => (bool) $get('show_in_menu'))
                        ->helperText('Lower numbers appear first within the same menu section.'),
                ]),
        ];
    }
}
