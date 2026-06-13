<?php

namespace App\Filament\Resources\Families\Schemas;

use App\Filament\Support\HouseholdMemberOptions;
use App\Models\Family;
use App\Models\User;
use App\Support\ParishWorshipLocations;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class FamilyForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Household')
                    ->description('Create a parish household and link members who registered separately — for example husband and wife with individual accounts.')
                    ->columns(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Family name')
                            ->maxLength(120)
                            ->placeholder('e.g. Thadathil')
                            ->helperText('A surname or household label only — many unrelated families can share the same name. Each household is identified separately by its primary account and household number in admin.'),
                        Select::make('preferred_worship_location')
                            ->label('Preferred worship location')
                            ->options(ParishWorshipLocations::options())
                            ->searchable(),
                        Select::make('head_user_id')
                            ->label('Head of household (optional)')
                            ->helperText('Choose an existing member account to link immediately — usually whoever registered first individually.')
                            ->searchable()
                            ->preload()
                            ->options(fn (): array => HouseholdMemberOptions::unlinkedOptions())
                            ->getSearchResultsUsing(fn (string $search): array => HouseholdMemberOptions::unlinkedOptions($search))
                            ->getOptionLabelUsing(fn ($value): ?string => User::query()->find($value)?->displayFullName())
                            ->visible(fn (string $operation): bool => $operation === 'create'),
                        Placeholder::make('primary_family_account')
                            ->label('Primary family account')
                            ->content(function (?Family $record): string {
                                if (! $record) {
                                    return '—';
                                }

                                $admin = User::query()
                                    ->where('family_id', $record->id)
                                    ->where('is_family_admin', true)
                                    ->orderBy('id')
                                    ->first();

                                if (! $admin) {
                                    return 'No primary account set yet. Use “Set as family admin” on a household member below.';
                                }

                                return trim($admin->displayFullName().' · '.($admin->email ?? 'no email'));
                            })
                            ->helperText('Only the primary family account can sign in on behalf of this household. Change it from the household members table below.')
                            ->columnSpanFull()
                            ->visible(fn (?Family $record, string $operation): bool => $operation === 'edit' && $record !== null),
                        Toggle::make('is_active')
                            ->label('Family active')
                            ->default(true)
                            ->helperText('When off, no one in this household can sign in to the member portal.')
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
