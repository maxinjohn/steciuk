<?php

namespace App\Filament\Resources\Roles\Schemas;

use App\Enums\AdminPermission;
use App\Models\Role;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class RoleForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Role details')
                    ->description('Rename built-in roles or create a custom role for your parish team.')
                    ->schema([
                        TextInput::make('name')
                            ->required()
                            ->maxLength(120)
                            ->disabled(fn (?Role $record): bool => (bool) ($record?->isNameLocked() ?? false))
                            ->helperText(fn (?Role $record): ?string => ($record?->isNameLocked() ?? false)
                                ? 'This predefined role name is fixed.'
                                : null)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (Set $set, ?string $state, ?string $operation): void {
                                if ($operation !== 'create') {
                                    return;
                                }

                                $set('slug', Str::slug($state ?? ''));
                            }),
                        TextInput::make('slug')
                            ->required()
                            ->maxLength(80)
                            ->unique(ignoreRecord: true)
                            ->alphaDash()
                            ->disabled(fn (?Role $record): bool => (bool) ($record?->is_system ?? false))
                            ->helperText(fn (?Role $record): ?string => ($record?->is_system ?? false)
                                ? 'System role slugs stay fixed so permissions remain stable.'
                                : 'Used internally — lowercase letters, numbers, and dashes only.'),
                        Textarea::make('description')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
                Section::make('Permissions')
                    ->description('Super Admin always has full access. Configure privileges for every other role here.')
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label('Allowed actions')
                            ->options(AdminPermission::labels())
                            ->columns(2)
                            ->bulkToggleable()
                            ->searchable()
                            ->visible(fn (?Role $record): bool => ! ($record?->grants_full_access ?? false))
                            ->helperText('Users with this role receive only the actions you enable.'),
                    ]),
            ]);
    }
}
