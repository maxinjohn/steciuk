<?php

namespace App\Filament\Resources\Designations;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\Designations\Pages\CreateDesignation;
use App\Filament\Resources\Designations\Pages\EditDesignation;
use App\Filament\Resources\Designations\Pages\ListDesignations;
use App\Filament\Resources\Designations\Schemas\DesignationForm;
use App\Filament\Resources\Designations\Tables\DesignationsTable;
use App\Models\Designation;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class DesignationResource extends Resource
{
    protected static ?string $model = Designation::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::People;

    protected static ?string $navigationLabel = 'Designations';

    protected static ?string $modelLabel = 'Designation';

    protected static ?string $pluralModelLabel = 'Designations';

    protected static ?int $navigationSort = 3;

    public static function canViewAny(): bool
    {
        return auth()->user()?->can('viewAny', Designation::class) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can('create', Designation::class) ?? false;
    }

    /**
     * @return Builder<Designation>
     */
    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withCount('users');
    }

    public static function form(Schema $schema): Schema
    {
        return DesignationForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DesignationsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDesignations::route('/'),
            'create' => CreateDesignation::route('/create'),
            'edit' => EditDesignation::route('/{record}/edit'),
        ];
    }
}
