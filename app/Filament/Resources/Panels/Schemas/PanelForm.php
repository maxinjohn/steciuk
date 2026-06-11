<?php

namespace App\Filament\Resources\Panels\Schemas;

use App\Models\Panel;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;

class PanelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Panel details')
                ->description('Custom groups such as Parish Committee, Choir Members, or other ministry panels. Members must already be parish users.')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->disabled(fn (?Panel $record): bool => (bool) ($record?->isNameLocked() ?? false))
                        ->helperText(fn (?Panel $record): ?string => ($record?->isNameLocked() ?? false)
                            ? 'Built-in panel names stay fixed.'
                            : null)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, ?string $operation): void {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Panel::slugFromName($state ?? ''));
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->disabled(fn (?Panel $record): bool => (bool) ($record?->is_system ?? false))
                        ->helperText(fn (?Panel $record): ?string => ($record?->is_system ?? false)
                            ? 'Built-in panel slugs stay fixed.'
                            : 'Lowercase letters, numbers, and dashes only.'),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
