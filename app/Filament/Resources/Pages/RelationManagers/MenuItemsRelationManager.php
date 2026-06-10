<?php

namespace App\Filament\Resources\Pages\RelationManagers;

use App\Enums\MenuLocation;
use App\Filament\Support\CompactTableActions;
use App\Models\MenuItem;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class MenuItemsRelationManager extends RelationManager
{
    protected static string $relationship = 'menuItems';

    protected static ?string $title = 'Menu Placement';

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('label')
                    ->required()
                    ->helperText('Text shown in the navigation menu.'),
                Select::make('menu_location')
                    ->label('Menu')
                    ->options(MenuLocation::class)
                    ->required()
                    ->live(),
                Select::make('parent_id')
                    ->label('Parent item (submenu under)')
                    ->options(function (callable $get, ?MenuItem $record): array {
                        $location = $get('menu_location');

                        if (! $location) {
                            return [];
                        }

                        return MenuItem::query()
                            ->where('menu_location', $location)
                            ->whereNull('parent_id')
                            ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                            ->orderBy('sort_order')
                            ->pluck('label', 'id')
                            ->all();
                    })
                    ->searchable()
                    ->helperText('Leave empty for a top-level menu item.'),
                TextInput::make('url')
                    ->label('Custom URL override')
                    ->helperText('Leave blank to link to this page automatically.'),
                Toggle::make('is_external')
                    ->default(false),
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

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('label')
                    ->searchable(),
                TextColumn::make('menu_location')
                    ->badge(),
                TextColumn::make('parent.label')
                    ->label('Parent')
                    ->placeholder('Top level'),
                TextColumn::make('sort_order')
                    ->sortable(),
                IconColumn::make('is_visible')
                    ->boolean(),
            ])
            ->defaultSort('sort_order')
            ->headerActions([
                CreateAction::make()
                    ->mutateFormDataUsing(function (array $data): array {
                        $data['page_id'] = $this->getOwnerRecord()->getKey();

                        if (empty($data['label'])) {
                            $data['label'] = $this->getOwnerRecord()->title;
                        }

                        if (empty($data['url'])) {
                            $data['url'] = '/'.$this->getOwnerRecord()->slug;
                        }

                        return $data;
                    }),
            ])
            ->recordActions(CompactTableActions::editWithDelete(), RecordActionsPosition::AfterColumns);
    }
}
