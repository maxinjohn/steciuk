<?php

namespace App\Filament\Resources\Families\RelationManagers;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\AdminFamilyMemberTableActions;
use App\Models\Family;
use App\Models\User;
use App\Services\MemberRegistrationService;
use App\Support\ParishGender;
use App\Support\ParishPronouns;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
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
            ->emptyStateDescription('Link an existing member account or add a new household member to this family.')
            ->headerActions([
                Action::make('addMember')
                    ->label('Add new member')
                    ->icon('heroicon-o-user-plus')
                    ->visible(fn (): bool => auth()->user()?->can('update', $family) ?? false)
                    ->form([
                        TextInput::make('first_name')
                            ->label('First name')
                            ->required()
                            ->maxLength(120),
                        TextInput::make('last_name')
                            ->label('Last name')
                            ->maxLength(120),
                        Select::make('pronouns')
                            ->label('Pronouns')
                            ->options(ParishPronouns::options())
                            ->nullable()
                            ->searchable(),
                        Select::make('gender')
                            ->label('Gender')
                            ->options(ParishGender::options())
                            ->nullable()
                            ->searchable(),
                        TextInput::make('email')
                            ->label('Email')
                            ->email()
                            ->maxLength(255)
                            ->helperText('Optional for children without their own login.'),
                        TextInput::make('phone')
                            ->label('Phone')
                            ->tel()
                            ->maxLength(30),
                        DatePicker::make('date_of_birth')
                            ->label('Date of birth')
                            ->required()
                            ->maxDate(now()->subDay())
                            ->native(false),
                        Select::make('relationship')
                            ->label('Relationship')
                            ->options(collect(FamilyRelationship::options())->except([FamilyRelationship::Head->value])->all())
                            ->default(FamilyRelationship::Child->value)
                            ->required(),
                        Select::make('account_status')
                            ->label('Account status')
                            ->options([
                                AccountStatus::Pending->value => AccountStatus::Pending->label(),
                                AccountStatus::Approved->value => AccountStatus::Approved->label(),
                            ])
                            ->default(AccountStatus::Pending->value)
                            ->required()
                            ->helperText('Pending members cannot sign in until approved.'),
                    ])
                    ->action(function (array $data) use ($family): void {
                        app(MemberRegistrationService::class)->adminCreateFamilyMember(
                            auth()->user(),
                            $family,
                            $data,
                        );
                    }),
                Action::make('linkMember')
                    ->label('Link existing member')
                    ->icon('heroicon-o-link')
                    ->form([
                        Select::make('user_id')
                            ->label('Member account')
                            ->required()
                            ->searchable()
                            ->getSearchResultsUsing(function (string $search) use ($family): array {
                                return User::query()
                                    ->householdEligible()
                                    ->where(function ($query) use ($family): void {
                                        $query->whereNull('family_id')
                                            ->orWhere('family_id', $family->id);
                                    })
                                    ->where(function ($query) use ($search): void {
                                        $query->where('name', 'like', "%{$search}%")
                                            ->orWhere('first_name', 'like', "%{$search}%")
                                            ->orWhere('last_name', 'like', "%{$search}%")
                                            ->orWhere('email', 'like', "%{$search}%");
                                    })
                                    ->orderBy('last_name')
                                    ->orderBy('first_name')
                                    ->limit(50)
                                    ->get()
                                    ->mapWithKeys(fn (User $user): array => [
                                        $user->id => trim($user->displayFullName().' · '.($user->email ?? 'no email')),
                                    ])
                                    ->all();
                            }),
                        Select::make('relationship')
                            ->label('Relationship')
                            ->options(FamilyRelationship::options())
                            ->default(FamilyRelationship::Spouse->value)
                            ->required(),
                        Toggle::make('make_family_admin')
                            ->label('Set as family administrator')
                            ->helperText('Only one administrator per household. Usually the head of household.'),
                        Toggle::make('force_move')
                            ->label('Move from another household if needed')
                            ->default(true),
                    ])
                    ->action(function (array $data) use ($family): void {
                        $member = User::query()->findOrFail($data['user_id']);
                        $relationship = FamilyRelationship::tryFromValue($data['relationship'] ?? null) ?? FamilyRelationship::Other;

                        app(MemberRegistrationService::class)->assignUserToFamily(
                            auth()->user(),
                            $member,
                            $family,
                            $relationship,
                            makeFamilyAdmin: (bool) ($data['make_family_admin'] ?? false),
                            forceMove: (bool) ($data['force_move'] ?? true),
                        );
                    }),
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
