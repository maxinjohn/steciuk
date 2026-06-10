<?php

namespace App\Filament\Resources\Pages\Tables;

use App\Filament\Support\CompactTableActions;
use App\Models\Page;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class PagesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')
                    ->searchable()
                    ->sortable()
                    ->wrap()
                    ->description(fn (Page $record): string => $record->slug),
                TextColumn::make('status')
                    ->badge()
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->searchable()
                    ->copyable()
                    ->copyMessage('Slug copied')
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('template')
                    ->badge()
                    ->color('gray')
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_home')
                    ->label('Home')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->numeric()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('deleted_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                CompactTableActions::editButton()
                    ->visible(fn (Page $record): bool => auth()->user()?->can('update', $record) ?? false),
                CompactTableActions::overflowMenu([
                    Action::make('viewPublic')
                        ->label('View live page')
                        ->icon(Heroicon::OutlinedEye)
                        ->color('gray')
                        ->url(fn (Page $record): string => $record->publicUrl())
                        ->openUrlInNewTab()
                        ->visible(fn (Page $record): bool => auth()->user()?->can('view', $record) ?? false),
                    DeleteAction::make()
                        ->visible(fn (Page $record): bool => auth()->user()?->can('delete', $record) ?? false),
                ]),
            ], RecordActionsPosition::AfterColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
                    ForceDeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
                    RestoreBulkAction::make()
                        ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
                ]),
            ]);
    }
}
