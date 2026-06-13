<?php

namespace App\Filament\Resources\Families\RelationManagers;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\AdminFamilyMemberHeaderActions;
use App\Filament\Support\AdminFamilyMemberTableActions;
use App\Models\Family;
use App\Models\User;
use App\Support\ParishPronouns;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\RecordActionsPosition;
use Filament\Tables\Table;

class MembersRelationManager extends RelationManager
{
    protected static string $relationship = 'members';

    protected static ?string $title = 'Household members';

    public function table(Table $table): Table
    {
        /** @var Family $family */
        $family = $this->getOwnerRecord();

        return $table
            ->defaultSort('name')
            ->emptyStateHeading('No members linked yet')
            ->emptyStateDescription('Add an existing parish account or create a new household member for this family.')
            ->headerActions([
                AdminFamilyMemberHeaderActions::addExistingMembers($family),
                AdminFamilyMemberHeaderActions::addNewMember($family),
            ])
            ->columns([
                TextColumn::make('last_name')
                    ->label('Member')
                    ->searchable(['first_name', 'last_name', 'name', 'email'])
                    ->sortable(['last_name', 'first_name'])
                    ->formatStateUsing(fn (?string $state, User $record): string => $record->displayFullName())
                    ->description(fn (User $record): ?string => $record->email)
                    ->placeholder('—'),
                TextColumn::make('family_relationship')
                    ->label('Relation to admin')
                    ->formatStateUsing(fn (?string $state): string => FamilyRelationship::options()[$state] ?? 'Member'),
                TextColumn::make('date_of_birth')
                    ->label('Date of birth')
                    ->date()
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('first_name')
                    ->label('First name')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('email')
                    ->label('Email')
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
                IconColumn::make('is_family_admin')
                    ->label('Family admin')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions(
                AdminFamilyMemberTableActions::recordActions($family, fn () => $this->resetTable()),
                RecordActionsPosition::AfterColumns,
            )
            ->recordUrl(fn (User $record): string => UserResource::getUrl('view', ['record' => $record]));
    }
}
