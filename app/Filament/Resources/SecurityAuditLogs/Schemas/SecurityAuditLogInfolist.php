<?php

namespace App\Filament\Resources\SecurityAuditLogs\Schemas;

use App\Models\SecurityAuditLog;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SecurityAuditLogInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Event')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('When')
                            ->dateTime('d M Y H:i:s'),
                        TextEntry::make('severity')
                            ->badge()
                            ->color(fn (string $state): string => match ($state) {
                                'critical' => 'danger',
                                'warning' => 'warning',
                                default => 'gray',
                            }),
                        TextEntry::make('summary')
                            ->label('What happened')
                            ->columnSpanFull(),
                        TextEntry::make('action')
                            ->label('Action code')
                            ->formatStateUsing(fn (SecurityAuditLog $record): string => $record->actionLabel().' ('.$record->action.')'),
                    ]),
                Section::make('Who')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('actor_name')
                            ->label('Actor')
                            ->formatStateUsing(fn (SecurityAuditLog $record): string => $record->actorDisplayName()),
                        TextEntry::make('actor_role')
                            ->label('Role')
                            ->placeholder('—'),
                        TextEntry::make('user_id')
                            ->label('User ID')
                            ->placeholder('—'),
                        TextEntry::make('ip_address')
                            ->label('IP address')
                            ->placeholder('—'),
                        TextEntry::make('user_agent')
                            ->label('Browser / device')
                            ->columnSpanFull()
                            ->placeholder('—'),
                    ]),
                Section::make('Subject')
                    ->columns(2)
                    ->schema([
                        TextEntry::make('subject_label')
                            ->label('Subject')
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('subject_type')
                            ->label('Type')
                            ->placeholder('—'),
                        TextEntry::make('subject_id')
                            ->label('Record ID')
                            ->placeholder('—'),
                    ]),
                Section::make('Additional details')
                    ->schema([
                        TextEntry::make('metadata')
                            ->label('Context')
                            ->formatStateUsing(function (?array $state): string {
                                if ($state === null || $state === []) {
                                    return '—';
                                }

                                return json_encode($state, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '—';
                            })
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
