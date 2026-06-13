<?php

namespace App\Filament\Resources\FormSubmissions\Tables;

use App\Filament\Support\CompactTableActions;
use App\Models\FormSubmission;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class FormSubmissionsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->searchable()
            ->searchDebounce('250ms')
            ->searchUsing(function (Builder $query, string $search): void {
                $like = '%'.$search.'%';

                $query->where(function (Builder $query) use ($like): void {
                    $query
                        ->where('form_type', 'like', $like)
                        ->orWhere('data', 'like', $like);
                });
            })
            ->columns([
                TextColumn::make('submitter')
                    ->label('From')
                    ->state(fn (FormSubmission $record): string => $record->submitterName())
                    ->description(function (FormSubmission $record): ?string {
                        $email = $record->submitterEmail();
                        $preview = $record->previewText();

                        if ($email && $preview) {
                            return $email.' · '.Str::limit($preview, 72);
                        }

                        return $email ?? ($preview ? Str::limit($preview, 72) : null);
                    })
                    ->wrap(),
                TextColumn::make('form_type')
                    ->label('Form')
                    ->badge()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Submitted')
                    ->dateTime()
                    ->sortable(),
                IconColumn::make('is_read')
                    ->boolean()
                    ->label('Read'),
                TextColumn::make('ip_address')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
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
