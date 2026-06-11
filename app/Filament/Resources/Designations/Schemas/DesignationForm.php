<?php

namespace App\Filament\Resources\Designations\Schemas;

use App\Models\Designation;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DesignationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Designation details')
                ->description('Titles such as Vicar, Treasurer, or Churchwarden — assigned to users alongside their access role.')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(120)
                        ->disabled(fn (?Designation $record): bool => (bool) ($record?->isNameLocked() ?? false))
                        ->helperText(fn (?Designation $record): ?string => ($record?->isNameLocked() ?? false)
                            ? 'Built-in designation names stay fixed.'
                            : null)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (Set $set, ?string $state, ?string $operation): void {
                            if ($operation !== 'create') {
                                return;
                            }

                            $set('slug', Designation::slugFromName($state ?? ''));
                        }),
                    TextInput::make('slug')
                        ->required()
                        ->maxLength(80)
                        ->unique(ignoreRecord: true)
                        ->alphaDash()
                        ->disabled(fn (?Designation $record): bool => (bool) ($record?->is_system ?? false))
                        ->helperText(fn (?Designation $record): ?string => ($record?->is_system ?? false)
                            ? 'Built-in designation slugs stay fixed.'
                            : 'Lowercase letters, numbers, and dashes only.'),
                    Textarea::make('description')
                        ->rows(3)
                        ->columnSpanFull(),
                ]),
        ]);
    }
}
