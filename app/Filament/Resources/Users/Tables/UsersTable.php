<?php

namespace App\Filament\Resources\Users\Tables;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
use App\Enums\UserRole;
use App\Filament\Resources\Families\FamilyResource;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\AdminTableSearch;
use App\Filament\Support\AdminUserTableActions;
use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use App\Services\MemberRegistrationService;
use App\Services\PermissionService;
use App\Support\FamilyLabel;
use App\Support\ParishPronouns;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Toggle;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function (Builder $query): Builder {
                return $query
                    ->orderByRaw("CASE WHEN account_status = ? AND role = ? THEN 0 ELSE 1 END", [
                        AccountStatus::Pending->value,
                        UserRole::Member->value,
                    ])
                    ->orderByDesc('created_at');
            })
            ->defaultSort('created_at', 'desc')
            ->searchable()
            ->searchDebounce('250ms')
            ->searchUsing(fn (Builder $query, string $search) => AdminTableSearch::applyUsers($query, $search))
            ->columns([
                TextColumn::make('last_name')
                    ->label('Member')
                    ->sortable(['last_name', 'first_name'])
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->displayFullName())
                    ->description(fn (User $record): ?string => $record->email)
                    ->copyable()
                    ->copyMessage('Email copied')
                    ->copyableState(fn (User $record): ?string => $record->email)
                    ->placeholder('—'),
                TextColumn::make('role')
                    ->label('Role')
                    ->formatStateUsing(fn (?string $state): string => Role::labelForSlug($state))
                    ->badge()
                    ->color(fn (?string $state): string => match ($state) {
                        UserRole::SuperAdmin->value => 'danger',
                        UserRole::Admin->value => 'warning',
                        UserRole::Vicar->value => 'primary',
                        UserRole::Editor->value => 'info',
                        default => 'success',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('designation.name')
                    ->label('Designation')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('panels.name')
                    ->label('Panels')
                    ->badge()
                    ->separator(', ')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('member_status')
                    ->label('Status')
                    ->badge()
                    ->state(function (User $record): string {
                        if (! $record->isActive()) {
                            return 'Inactive';
                        }

                        if ($record->isMember() && $record->accountStatus() === AccountStatus::Pending) {
                            return 'Approval pending';
                        }

                        if ($record->isMember() && $record->accountStatus() === AccountStatus::Rejected) {
                            return AccountStatus::Rejected->label();
                        }

                        if ($record->isMember() && $record->accountStatus() === AccountStatus::Approved) {
                            return 'Verified member';
                        }

                        return 'Active';
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'Inactive' => 'danger',
                        'Approval pending' => 'warning',
                        AccountStatus::Rejected->label() => 'danger',
                        'Verified member' => 'success',
                        default => 'gray',
                    }),
                TextColumn::make('family_id')
                    ->label('Family')
                    ->state(fn (User $record): array|string => $record->family
                        ? FamilyLabel::userFamilyTableLines($record)
                        : 'Not linked')
                    ->listWithLineBreaks()
                    ->placeholder('Not linked')
                    ->width('13rem')
                    ->sortable(query: function (Builder $query, string $direction): Builder {
                        return $query
                            ->leftJoin('families', 'users.family_id', '=', 'families.id')
                            ->orderBy('families.name', $direction)
                            ->select('users.*');
                    })
                    ->url(fn (User $record): ?string => $record->family_id
                        ? FamilyResource::getUrl('edit', ['record' => $record->family_id])
                        : null),
                TextColumn::make('first_name')
                    ->label('First name')
                    ->sortable()
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->displayFirstName())
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('Email address')
                    ->copyable()
                    ->placeholder('No email')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('pronouns')
                    ->label('Pronouns')
                    ->formatStateUsing(fn (?string $state): string => ParishPronouns::label($state) ?? ($state ?: '—'))
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('gender')
                    ->label('Gender')
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->formattedGender() ?? '—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('date_of_birth')
                    ->label('Date of birth')
                    ->date()
                    ->sortable()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('account_status')
                    ->label('Approval')
                    ->formatStateUsing(fn (?string $state): string => AccountStatus::tryFrom((string) $state)?->label() ?? 'Unknown')
                    ->badge()
                    ->color(fn (?string $state): string => match (AccountStatus::tryFrom((string) $state)) {
                        AccountStatus::Approved => 'success',
                        AccountStatus::Pending => 'warning',
                        AccountStatus::Rejected => 'danger',
                        default => 'gray',
                    })
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('preferred_worship_location')
                    ->label('Worship location')
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->preferred_worship_location
                        ?? $record->family?->preferred_worship_location
                        ?? '—')
                    ->sortable(),
                TextColumn::make('postcode')
                    ->label('Postcode')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email_verified_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('role')
                    ->options(Role::options()),
                SelectFilter::make('designation_id')
                    ->label('Designation')
                    ->options(fn (): array => \App\Models\Designation::options()),
                SelectFilter::make('panels')
                    ->label('Panel')
                    ->relationship('panels', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('account_status')
                    ->label('Approval status')
                    ->options([
                        AccountStatus::Pending->value => AccountStatus::Pending->label(),
                        AccountStatus::Approved->value => AccountStatus::Approved->label(),
                        AccountStatus::Rejected->value => AccountStatus::Rejected->label(),
                    ]),
                TernaryFilter::make('is_active')
                    ->label('Active accounts'),
                TernaryFilter::make('linked_to_family')
                    ->label('Linked to family')
                    ->nullable()
                    ->trueLabel('Linked')
                    ->falseLabel('Not linked')
                    ->queries(
                        true: fn (Builder $query) => $query->whereNotNull('family_id'),
                        false: fn (Builder $query) => $query->whereNull('family_id'),
                        blank: fn (Builder $query) => $query,
                    ),
                SelectFilter::make('family_id')
                    ->label('Family')
                    ->options(fn (): array => FamilyLabel::selectOptions())
                    ->searchable(),
                SelectFilter::make('family_active')
                    ->label('Family status')
                    ->options([
                        'active' => 'Active families only',
                        'inactive' => 'Deactivated families only',
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return match ($data['value'] ?? null) {
                            'active' => $query->where(function (Builder $query): void {
                                $query->whereNull('family_id')
                                    ->orWhereHas('family', fn (Builder $query) => $query->where('is_active', true));
                            }),
                            'inactive' => $query->whereHas('family', fn (Builder $query) => $query->where('is_active', false)),
                            default => $query,
                        };
                    }),
            ])
            ->recordActions(AdminUserTableActions::recordActions(), RecordActionsPosition::AfterColumns)
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]))
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('assignToFamily')
                        ->label('Assign to family')
                        ->icon('heroicon-o-user-group')
                        ->form([
                            Select::make('family_id')
                                ->label('Family household')
                                ->options(fn (): array => FamilyLabel::selectOptions())
                                ->searchable()
                                ->required(),
                            Select::make('relationship')
                                ->label('Relationship')
                                ->options(FamilyRelationship::options())
                                ->default(FamilyRelationship::Spouse->value)
                                ->required(),
                            Toggle::make('make_family_admin')
                                ->label('Set as family administrator')
                                ->helperText('Only applies when exactly one member is selected.'),
                            Toggle::make('force_move')
                                ->label('Move from another household if needed')
                                ->default(true),
                        ])
                        ->action(function (Collection $records, array $data): void {
                            $family = Family::query()->findOrFail($data['family_id']);
                            $relationship = FamilyRelationship::tryFromValue($data['relationship'] ?? null) ?? FamilyRelationship::Other;
                            $service = app(MemberRegistrationService::class);
                            $actor = auth()->user();

                            $makeAdmin = (bool) ($data['make_family_admin'] ?? false) && $records->count() === 1;

                            $records->each(function (User $record) use ($service, $actor, $family, $relationship, $data, $makeAdmin): void {
                                if (! $record->canBelongToHousehold() || ! $actor?->can('update', $record)) {
                                    return;
                                }

                                $service->assignUserToFamily(
                                    $actor,
                                    $record,
                                    $family,
                                    $relationship,
                                    makeFamilyAdmin: $makeAdmin,
                                    forceMove: (bool) ($data['force_move'] ?? true),
                                );
                            });
                        }),
                    BulkAction::make('deactivateSelected')
                        ->label('Deactivate selected')
                        ->icon('heroicon-o-no-symbol')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->action(function (Collection $records): void {
                            $service = app(MemberRegistrationService::class);
                            $actor = auth()->user();

                            $records->each(function (User $record) use ($service, $actor): void {
                                if ($record->isActive() && self::canManageAccount($record)) {
                                    $service->deactivateUser($record, $actor);
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

                            $records->each(function (User $record) use ($service, $actor): void {
                                if (! $record->isActive() && self::canManageAccount($record)) {
                                    $service->activateUser($record, $actor);
                                }
                            });
                        }),
                    DeleteBulkAction::make()
                        ->visible(fn (): bool => auth()->user() && app(PermissionService::class)->canResource(auth()->user(), 'users', 'delete')),
                ]),
            ]);
    }

    private static function canManageAccount(User $record): bool
    {
        $actor = auth()->user();

        if (! $actor?->can('update', $record)) {
            return false;
        }

        if ($record->id === $actor->id) {
            return false;
        }

        if ($record->isSuperAdmin() && ! $actor->isSuperAdmin()) {
            return false;
        }

        return true;
    }
}
