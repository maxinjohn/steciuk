<?php

namespace App\Filament\Resources\Conversations\Tables;

use App\Filament\Support\CompactTableActions;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class ConversationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('subject')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('participantName')
                    ->label('From')
                    ->state(fn ($record) => $record->participantName())
                    ->searchable(query: function ($query, string $search) {
                        $query->where(function ($inner) use ($search): void {
                            $inner->where('guest_name', 'like', "%{$search}%")
                                ->orWhere('guest_email', 'like', "%{$search}%")
                                ->orWhereHas('user', fn ($userQuery) => $userQuery
                                    ->where('first_name', 'like', "%{$search}%")
                                    ->orWhere('last_name', 'like', "%{$search}%")
                                    ->orWhere('email', 'like', "%{$search}%"));
                        });
                    }),
                TextColumn::make('source')
                    ->label('Source')
                    ->badge()
                    ->formatStateUsing(fn ($record) => $record->sourceLabel()),
                TextColumn::make('updated_at')
                    ->label('Updated')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('unread_by_admin')
                    ->boolean()
                    ->label('Unread'),
            ])
            ->defaultSort('updated_at', 'desc')
            ->recordActions([
                CompactTableActions::viewButton(),
            ], RecordActionsPosition::AfterColumns)
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
