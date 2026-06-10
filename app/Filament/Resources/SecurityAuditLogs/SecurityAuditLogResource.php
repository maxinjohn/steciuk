<?php

namespace App\Filament\Resources\SecurityAuditLogs;

use App\Enums\AdminNavigationGroup;
use App\Enums\AdminPermission;
use App\Filament\Resources\SecurityAuditLogs\Pages\ListSecurityAuditLogs;
use App\Filament\Resources\SecurityAuditLogs\Pages\ViewSecurityAuditLog;
use App\Filament\Resources\SecurityAuditLogs\Schemas\SecurityAuditLogInfolist;
use App\Filament\Resources\SecurityAuditLogs\Tables\SecurityAuditLogsTable;
use App\Models\SecurityAuditLog;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class SecurityAuditLogResource extends Resource
{
    protected static ?string $model = SecurityAuditLog::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static ?string $navigationLabel = 'Activity log';

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Security;

    protected static ?int $navigationSort = 1;

    public static function canCreate(): bool
    {
        return false;
    }

    public static function canEdit(Model $record): bool
    {
        return false;
    }

    public static function canDelete(Model $record): bool
    {
        return false;
    }

    public static function canViewAny(): bool
    {
        $user = auth()->user();

        return $user?->hasFullPanelAccess()
            || $user?->hasAdminPermission(AdminPermission::SecurityAuditLog);
    }

    public static function canView(Model $record): bool
    {
        return static::canViewAny();
    }

    public static function infolist(Schema $schema): Schema
    {
        return SecurityAuditLogInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SecurityAuditLogsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSecurityAuditLogs::route('/'),
            'view' => ViewSecurityAuditLog::route('/{record}'),
        ];
    }
}
