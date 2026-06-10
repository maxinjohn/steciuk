<?php

namespace App\Filament\Resources\Families;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\Families\Pages\CreateFamily;
use App\Filament\Resources\Families\Pages\EditFamily;
use App\Filament\Resources\Families\Pages\ListFamilies;
use App\Filament\Resources\Families\RelationManagers\MembersRelationManager;
use App\Filament\Resources\Families\Schemas\FamilyForm;
use App\Filament\Resources\Families\Tables\FamiliesTable;
use App\Models\Family;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class FamilyResource extends Resource
{
    protected static ?string $model = Family::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserGroup;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::People;

    protected static ?string $navigationLabel = 'Families';

    protected static ?string $modelLabel = 'Family';

    protected static ?string $pluralModelLabel = 'Families';

    protected static ?int $navigationSort = 2;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', Family::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Family::class) ?? false;
    }

    /**
     * @return Builder<Family>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with('admin')->withCount('members');
    }

    public static function form(Schema $schema): Schema
    {
        return FamilyForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return FamiliesTable::configure($table);
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
            'index' => ListFamilies::route('/'),
            'create' => CreateFamily::route('/create'),
            'edit' => EditFamily::route('/{record}/edit'),
        ];
    }
}
