<?php

namespace App\Filament\Support;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
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
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Support\Enums\Width;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AdminFamilyMemberHeaderActions
{
    public static function addNewMember(Family $family): Action
    {
        return Action::make('addMember')
            ->label('Add new member')
            ->icon('heroicon-o-user-plus')
            ->visible(fn (): bool => auth()->user()?->can('update', $family) ?? false)
            ->slideOver()
            ->modalWidth(Width::TwoExtraLarge)
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalHeading('Add new household member')
            ->modalDescription('Create a new parish account and link it to this household. Use a slide-over panel so every field stays reachable on mobile and desktop.')
            ->form([
                Grid::make(['default' => 1, 'md' => 2])
                    ->schema([
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
                            ->helperText('Optional for children without their own login.')
                            ->columnSpanFull(),
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
                    ]),
            ])
            ->action(function (array $data) use ($family): void {
                app(MemberRegistrationService::class)->adminCreateFamilyMember(
                    auth()->user(),
                    $family,
                    $data,
                );

                Notification::make()
                    ->title('Household member added')
                    ->success()
                    ->send();
            });
    }

    public static function addExistingMembers(Family $family): Action
    {
        return Action::make('addExistingMembers')
            ->label('Add existing members')
            ->icon('heroicon-o-link')
            ->visible(fn (): bool => auth()->user()?->can('update', $family) ?? false)
            ->slideOver()
            ->modalWidth(Width::TwoExtraLarge)
            ->stickyModalHeader()
            ->stickyModalFooter()
            ->modalHeading('Add existing members to this household')
            ->modalDescription('Choose one or more parish accounts to link here. Members already assigned elsewhere stay disabled until you turn on “Move from another household”.')
            ->form([
                Select::make('user_ids')
                    ->label('Member accounts')
                    ->multiple()
                    ->required()
                    ->searchable()
                    ->preload()
                    ->options(fn (): array => HouseholdMemberOptions::options($family))
                    ->getSearchResultsUsing(fn (string $search): array => HouseholdMemberOptions::options($family, $search))
                    ->getOptionLabelUsing(fn ($value): ?string => HouseholdMemberOptions::labelForId((int) $value, $family))
                    ->disableOptionWhen(
                        fn ($value, $label, Get $get): bool => HouseholdMemberOptions::isBlocked(
                            (int) $value,
                            $family,
                            (bool) ($get('force_move') ?? false),
                        ),
                    )
                    ->helperText('Available members load automatically. Search to narrow the list. Accounts linked to another household stay disabled until you enable the move option below.')
                    ->live(),
                Select::make('relationship')
                    ->label('Relationship in this household')
                    ->options(FamilyRelationship::householdAssignmentOptions())
                    ->default(FamilyRelationship::Spouse->value)
                    ->required(),
                Toggle::make('make_family_admin')
                    ->label('Set as family administrator')
                    ->helperText('Only available when linking one person. Usually the head of household.')
                    ->visible(fn (Get $get): bool => count((array) ($get('user_ids') ?? [])) === 1)
                    ->live(),
                Toggle::make('force_move')
                    ->label('Move from another household if needed')
                    ->helperText('Required when linking someone who is still assigned elsewhere. They will be unlinked from their current household first.')
                    ->default(false)
                    ->live(),
            ])
            ->action(function (array $data) use ($family): void {
                $userIds = array_values(array_unique(array_map('intval', (array) ($data['user_ids'] ?? []))));
                $forceMove = (bool) ($data['force_move'] ?? false);
                $relationship = FamilyRelationship::tryFromValue($data['relationship'] ?? null) ?? FamilyRelationship::Other;
                $makeFamilyAdmin = count($userIds) === 1 && (bool) ($data['make_family_admin'] ?? false);

                foreach ($userIds as $userId) {
                    if (HouseholdMemberOptions::isBlocked($userId, $family, $forceMove)) {
                        throw ValidationException::withMessages([
                            'user_ids' => 'One or more selected members cannot be linked. Enable “Move from another household” or choose different members.',
                        ]);
                    }
                }

                $service = app(MemberRegistrationService::class);
                $actor = auth()->user();

                DB::transaction(function () use ($service, $actor, $family, $userIds, $relationship, $makeFamilyAdmin, $forceMove): void {
                    foreach ($userIds as $userId) {
                        $member = User::query()->findOrFail($userId);

                        $service->assignUserToFamily(
                            $actor,
                            $member,
                            $family,
                            $relationship,
                            makeFamilyAdmin: $makeFamilyAdmin,
                            forceMove: $forceMove,
                        );
                    }
                });

                Notification::make()
                    ->title(count($userIds) === 1 ? 'Member linked to household' : count($userIds).' members linked to household')
                    ->success()
                    ->send();
            });
    }
}
