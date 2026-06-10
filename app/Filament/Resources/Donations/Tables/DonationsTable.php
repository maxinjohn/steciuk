<?php

namespace App\Filament\Resources\Donations\Tables;

use App\Enums\DonationMethod;
use App\Enums\DonationStatus;
use App\Filament\Resources\Donations\DonationResource;
use App\Filament\Support\AdminDonationTableActions;
use App\Models\Donation;
use App\Services\DonationService;
use App\Services\PermissionService;
use App\Support\FamilyLabel;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class DonationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('donated_on', 'desc')
            ->columns([
                TextColumn::make('user.name')
                    ->label('Donor')
                    ->searchable(['users.name', 'users.first_name', 'users.last_name', 'users.email'])
                    ->sortable()
                    ->formatStateUsing(fn (?string $state, Donation $record): string => $record->user?->displayFullName() ?? '—')
                    ->description(fn (Donation $record): ?string => $record->user?->email),
                TextColumn::make('amount')
                    ->label('Amount')
                    ->money('GBP')
                    ->sortable()
                    ->summarize(
                        Sum::make()
                            ->money('GBP')
                            ->label('Approved total')
                            ->query(fn (Builder $query): Builder => $query->where('status', DonationStatus::Approved->value))
                    ),
                TextColumn::make('donated_on')
                    ->label('Gift date')
                    ->date()
                    ->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => DonationStatus::tryFrom((string) $state)?->label() ?? 'Unknown')
                    ->color(fn (?string $state): string => DonationStatus::tryFrom((string) $state)?->badgeColor() ?? 'gray'),
                TextColumn::make('family_id')
                    ->label('Family')
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->width('11rem')
                    ->state(fn (Donation $record): array|string => $record->family
                        ? FamilyLabel::familyTableLines($record->family)
                        : '—')
                    ->listWithLineBreaks()
                    ->placeholder('—'),
                TextColumn::make('method')
                    ->formatStateUsing(fn (?string $state): string => DonationMethod::tryFrom((string) $state)?->label() ?? 'Unknown')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('reference')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('donated_on')
                    ->label('Gift date range')
                    ->form([
                        DatePicker::make('from')
                            ->label('From')
                            ->native(false),
                        DatePicker::make('to')
                            ->label('To')
                            ->native(false),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when($data['from'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('donated_on', '>=', $date))
                            ->when($data['to'] ?? null, fn (Builder $query, $date): Builder => $query->whereDate('donated_on', '<=', $date));
                    }),
                SelectFilter::make('status')
                    ->options([
                        DonationStatus::Pending->value => DonationStatus::Pending->label(),
                        DonationStatus::Approved->value => DonationStatus::Approved->label(),
                        DonationStatus::Rejected->value => DonationStatus::Rejected->label(),
                    ]),
                SelectFilter::make('method')
                    ->options(DonationMethod::options()),
                SelectFilter::make('family_id')
                    ->label('Family')
                    ->options(fn (): array => FamilyLabel::selectOptions())
                    ->searchable(),
            ])
            ->recordActions(AdminDonationTableActions::recordActions(), RecordActionsPosition::AfterColumns)
            ->recordUrl(fn (Donation $record): string => DonationResource::getUrl('edit', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user() && app(PermissionService::class)->canResource(auth()->user(), 'users', 'delete'))
                        ->action(function (Collection $records): void {
                            $service = app(DonationService::class);
                            $admin = auth()->user();

                            $records->each(fn (Donation $record) => $service->deleteFromAdmin($admin, $record));
                        }),
                ]),
            ]);
    }
}
