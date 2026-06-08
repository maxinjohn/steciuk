<?php

namespace App\Filament\Resources\Sermons;

use App\Filament\Resources\Sermons\Pages\CreateSermon;
use App\Filament\Resources\Sermons\Pages\EditSermon;
use App\Filament\Resources\Sermons\Pages\ListSermons;
use App\Filament\Resources\Sermons\Schemas\SermonForm;
use App\Filament\Resources\Sermons\Tables\SermonsTable;
use App\Models\Sermon;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SermonResource extends Resource
{
    protected static ?string $model = Sermon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMicrophone;

    protected static string | \UnitEnum | null $navigationGroup = 'Worship';

    protected static ?string $navigationLabel = 'Sermons';

    protected static ?string $modelLabel = 'Sermon';

    protected static ?string $pluralModelLabel = 'Sermons';

    protected static ?int $navigationSort = 2;

    public static function form(Schema $schema): Schema
    {
        return SermonForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SermonsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSermons::route('/'),
            'create' => CreateSermon::route('/create'),
            'edit' => EditSermon::route('/{record}/edit'),
        ];
    }
}
