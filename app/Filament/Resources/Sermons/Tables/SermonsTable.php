<?php

namespace App\Filament\Resources\Sermons\Tables;

use App\Filament\Support\CompactTableActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class SermonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('preached_at', 'desc')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->wrap()
                    ->description(fn ($record): ?string => $record->speaker),
                TextColumn::make('preached_at')
                    ->label('Preached')
                    ->date('d M Y')
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->searchable(),
                TextColumn::make('bible_passage')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('category')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                CompactTableActions::editButton(),
            ], RecordActionsPosition::AfterColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
