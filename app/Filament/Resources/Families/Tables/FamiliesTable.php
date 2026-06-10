<?php

namespace App\Filament\Resources\Families\Tables;

use App\Filament\Resources\Families\FamilyResource;
use App\Filament\Support\AdminFamilyTableActions;
use App\Filament\Support\AdminTableSearch;
use App\Models\Family;
use App\Services\MemberRegistrationService;
use App\Services\PermissionService;
use App\Support\FamilyLabel;
use App\Support\ParishWorshipLocations;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class FamiliesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('name')
            ->searchable()
            ->searchDebounce('250ms')
            ->searchUsing(fn (Builder $query, string $search) => AdminTableSearch::applyFamilies($query, $search))
            ->columns([
                TextColumn::make('name')
                    ->label('Household')
                    ->state(fn (Family $record): array => FamilyLabel::familyTableLines($record))
                    ->listWithLineBreaks()
                    ->sortable()
                    ->width('12rem')
                    ->description(fn (Family $record): ?string => FamilyLabel::tableSummary($record)),
                TextColumn::make('members_count')
                    ->label('Members')
                    ->counts('members')
                    ->sortable()
                    ->badge()
                    ->color('gray'),
                TextColumn::make('admin.name')
                    ->label('Primary account')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('preferred_worship_location')
                    ->label('Worship location')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active families'),
                SelectFilter::make('preferred_worship_location')
                    ->label('Worship location')
                    ->options(ParishWorshipLocations::options()),
            ])
            ->recordActions(AdminFamilyTableActions::recordActions(), RecordActionsPosition::AfterColumns)
            ->recordUrl(fn (Family $record): string => FamilyResource::getUrl('edit', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('deactivateSelected')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $service = app(MemberRegistrationService::class);
                            $actor = auth()->user();

                            $records->each(function (Family $family) use ($service, $actor): void {
                                if ($family->isActive() && $actor?->can('update', $family)) {
                                    $service->deactivateFamily($family, $actor);
                                }
                            });
                        }),
                    BulkAction::make('activateSelected')
                        ->label('Activate selected')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $service = app(MemberRegistrationService::class);
                            $actor = auth()->user();

                            $records->each(function (Family $family) use ($service, $actor): void {
                                if (! $family->isActive() && $actor?->can('update', $family)) {
                                    $service->activateFamily($family, $actor);
                                }
                            });
                        }),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user() && app(PermissionService::class)->canResource(auth()->user(), 'users', 'delete')),
                ]),
            ]);
    }
}
