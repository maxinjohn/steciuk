<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\AccountStatus;
use App\Enums\FamilyRelationship;
use App\Enums\UserRole;
use App\Filament\Support\ResourceFormTabs;
use App\Filament\Support\UkAddressFormSchema;
use App\Models\Family;
use App\Models\Role;
use App\Models\User;
use App\Support\ParishGender;
use App\Support\ParishPronouns;
use App\Support\ParishWorshipLocations;
use App\Support\UserProfileAttributes;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ResourceFormTabs::make('Member', [
                    Tab::make('Account')
                        ->icon(Heroicon::OutlinedIdentification)
                        ->schema([
                            Section::make('Login & access')
                                ->description('Sign-in details, role, and account status.')
                                ->icon(Heroicon::OutlinedShieldCheck)
                                ->columns(2)
                                ->extraAttributes(['class' => 'service-form-section'])
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
                                        ->options(ParishPronouns::requiredOptions())
                                        ->required()
                                        ->native()
                                        ->rules(UserProfileAttributes::pronounsRules())
                                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null)
                                        ->helperText('Shown on the member portal.'),
                                    Select::make('gender')
                                        ->label('Gender')
                                        ->options(ParishGender::requiredOptions())
                                        ->required()
                                        ->native()
                                        ->rules(UserProfileAttributes::genderRules())
                                        ->dehydrateStateUsing(fn (?string $state): ?string => filled($state) ? trim($state) : null),
                                    TextInput::make('email')
                                        ->label('Email address')
                                        ->email()
                                        ->required()
                                        ->unique(ignoreRecord: true)
                                        ->columnSpanFull(),
                                    TextInput::make('password')
                                        ->password()
                                        ->dehydrated(fn (?string $state): bool => filled($state))
                                        ->required(fn (string $operation): bool => $operation === 'create')
                                        ->helperText('Leave blank when editing to keep the current password.')
                                        ->columnSpanFull(),
                                    Select::make('role')
                                        ->options(fn (): array => auth()->user()?->assignableRoleOptions() ?? Role::options())
                                        ->default(UserRole::Member->value)
                                        ->required()
                                        ->disabled(function (?User $record, string $operation): bool {
                                            $actor = auth()->user();

                                            if (! $actor?->canManageTeamRoles()) {
                                                return true;
                                            }

                                            if ($operation === 'create') {
                                                return false;
                                            }

                                            return $record instanceof User && ! $actor->canChangeRoleOf($record);
                                        })
                                        ->helperText(function (?User $record): ?string {
                                            $actor = auth()->user();

                                            if (! $actor?->canManageTeamRoles()) {
                                                return 'Only super admins and parish admins can change roles.';
                                            }

                                            if ($record?->id && (int) $record->id === (int) $actor->id) {
                                                return 'You cannot change your own role.';
                                            }

                                            return $actor->isSuperAdmin()
                                                ? 'Members use the public website only. Only you can promote someone to Super Admin.'
                                                : 'Members use the public website only. Only the Super Admin can promote someone to Super Admin.';
                                        }),
                                    Select::make('account_status')
                                        ->label('Account status')
                                        ->options([
                                            AccountStatus::Pending->value => AccountStatus::Pending->label(),
                                            AccountStatus::Approved->value => AccountStatus::Approved->label(),
                                            AccountStatus::Rejected->value => AccountStatus::Rejected->label(),
                                        ])
                                        ->default(AccountStatus::Approved->value)
                                        ->visible(fn (): bool => auth()->user()?->can('update', User::class) ?? false),
                                    Toggle::make('is_active')
                                        ->label('Account active')
                                        ->default(true)
                                        ->helperText('Deactivated members cannot sign in to the website or member portal.')
                                        ->visible(fn (): bool => auth()->user()?->can('update', User::class) ?? false),
                                ]),
                        ]),
                    Tab::make('Profile')
                        ->icon(Heroicon::OutlinedUserCircle)
                        ->schema([
                            Section::make('Personal details')
                                ->description('Phone, date of birth, address, and preferred worship location.')
                                ->icon(Heroicon::OutlinedMapPin)
                                ->columns(2)
                                ->extraAttributes(['class' => 'service-form-section'])
                                ->schema([
                                    TextInput::make('phone')
                                        ->label('Phone number')
                                        ->tel()
                                        ->placeholder('e.g. 07700 900123')
                                        ->helperText('UK format — include the leading 0 for mobile and landline numbers.')
                                        ->maxLength(30),
                                    DatePicker::make('date_of_birth')
                                        ->label('Date of birth')
                                        ->maxDate(now()->subDay())
                                        ->native(false),
                                    ...UkAddressFormSchema::modelFields(),
                                    Select::make('preferred_worship_location')
                                        ->label('Preferred worship location')
                                        ->options(ParishWorshipLocations::options())
                                        ->native()
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Household')
                        ->icon(Heroicon::OutlinedUserGroup)
                        ->schema([
                            Section::make('Family link')
                                ->description('Link parish members and team accounts who share a household — e.g. a vicar and spouse with separate logins.')
                                ->icon(Heroicon::OutlinedHomeModern)
                                ->columns(2)
                                ->visible(fn (?User $record): bool => ($record?->canBelongToHousehold() ?? true) && (auth()->user()?->can('update', User::class) ?? false))
                                ->extraAttributes(['class' => 'service-form-section'])
                                ->schema([
                                    Select::make('family_id')
                                        ->label('Family household')
                                        ->relationship(
                                            name: 'family',
                                            titleAttribute: 'name',
                                            modifyQueryUsing: fn ($query) => $query->with('admin')->orderBy('name')->orderBy('id'),
                                        )
                                        ->getOptionLabelFromRecordUsing(fn (Family $record): string => $record->adminDisplayLabel())
                                        ->searchable()
                                        ->preload()
                                        ->nullable()
                                        ->helperText('The same family name can exist for many households. Options show the primary account, email, location, and household number.'),
                                    Select::make('family_relationship')
                                        ->label('Relationship in household')
                                        ->options(FamilyRelationship::options())
                                        ->nullable()
                                        ->required(fn (callable $get): bool => filled($get('family_id'))),
                                    Toggle::make('is_family_admin')
                                        ->label('Family administrator')
                                        ->helperText('Can add and manage household members on the public website after approval.')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                ], 'member-tab'),
            ]);
    }
}
