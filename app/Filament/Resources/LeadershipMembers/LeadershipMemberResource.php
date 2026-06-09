<?php

namespace App\Filament\Resources\LeadershipMembers;

use App\Enums\AdminNavigationGroup;
use App\Filament\Resources\LeadershipMembers\Pages\CreateLeadershipMember;
use App\Filament\Resources\LeadershipMembers\Pages\EditLeadershipMember;
use App\Filament\Resources\LeadershipMembers\Pages\ListLeadershipMembers;
use App\Filament\Resources\LeadershipMembers\Schemas\LeadershipMemberForm;
use App\Filament\Resources\LeadershipMembers\Tables\LeadershipMembersTable;
use App\Models\LeadershipMember;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class LeadershipMemberResource extends Resource
{
    protected static ?string $model = LeadershipMember::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string | \UnitEnum | null $navigationGroup = AdminNavigationGroup::Worship;

    protected static ?string $navigationLabel = 'Leadership';

    protected static ?string $modelLabel = 'Leadership Member';

    protected static ?string $pluralModelLabel = 'Leadership Members';

    protected static ?int $navigationSort = 5;

    public static function form(Schema $schema): Schema
    {
        return LeadershipMemberForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return LeadershipMembersTable::configure($table);
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
            'index' => ListLeadershipMembers::route('/'),
            'create' => CreateLeadershipMember::route('/create'),
            'edit' => EditLeadershipMember::route('/{record}/edit'),
        ];
    }
}
