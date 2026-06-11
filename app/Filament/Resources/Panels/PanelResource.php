<?php

namespace App\Filament\Resources\Panels;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\Panels\Pages\CreatePanel;
use App\Filament\Resources\Panels\Pages\EditPanel;
use App\Filament\Resources\Panels\Pages\ListPanels;
use App\Filament\Resources\Panels\RelationManagers\MembersRelationManager;
use App\Filament\Resources\Panels\Schemas\PanelForm;
use App\Filament\Resources\Panels\Tables\PanelsTable;
use App\Models\Panel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class PanelResource extends Resource
{
    protected static ?string $model = Panel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::People;

    protected static ?string $navigationLabel = 'Panels';

    protected static ?string $modelLabel = 'Panel';

    protected static ?string $pluralModelLabel = 'Panels';

    protected static ?int $navigationSort = 4;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', Panel::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Panel::class) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return PanelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PanelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            MembersRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPanels::route('/'),
            'create' => CreatePanel::route('/create'),
            'edit' => EditPanel::route('/{record}/edit'),
        ];
    }
}
