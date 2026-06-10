<?php

namespace App\Filament\Resources\SecurityAuditLogs\Tables;

use App\Filament\Support\CompactTableActions;
use App\Support\SecurityAuditCatalog;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class SecurityAuditLogsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('created_at')
                    ->label('When')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
                TextColumn::make('severity')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'critical' => 'danger',
                        'warning' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('summary')
                    ->label('Event')
                    ->searchable(['summary', 'actor_name', 'actor_email', 'subject_label'])
                    ->wrap()
                    ->limit(80)
                    ->description(fn ($record): ?string => $record->actor_name
                        ? trim($record->actor_name.' · '.($record->actor_email ?? ''))
                        : 'System / guest'),
                TextColumn::make('action')
                    ->label('Action')
                    ->formatStateUsing(fn (string $state): string => SecurityAuditCatalog::label($state))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('subject_label')
                    ->label('Subject')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->placeholder('—'),
                TextColumn::make('ip_address')
                    ->label('IP')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('severity')
                    ->options([
                        'info' => 'Info',
                        'warning' => 'Warning',
                        'critical' => 'Critical',
                    ]),
                SelectFilter::make('action')
                    ->label('Action type')
                    ->options(SecurityAuditCatalog::actionOptions()),
            ])
            ->recordActions([
                CompactTableActions::viewButton(),
            ], RecordActionsPosition::AfterColumns);
    }
}
