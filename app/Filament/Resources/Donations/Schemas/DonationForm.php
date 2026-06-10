<?php

namespace App\Filament\Resources\Donations\Schemas;

use App\Enums\DonationMethod;
use App\Enums\DonationStatus;
use App\Models\User;
use App\Support\FamilyLabel;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class DonationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Donation')
                    ->description('Record a gift on behalf of a parish member or household. The donor must already have a member account.')
                    ->columns(2)
                    ->schema([
                        Select::make('family_id')
                            ->label('Family household')
                            ->options(fn (): array => FamilyLabel::selectOptions())
                            ->searchable()
                            ->live()
                            ->dehydrated(false)
                            ->afterStateUpdated(fn (Set $set) => $set('user_id', null))
                            ->helperText('Optional — narrows the donor list to one household.'),
                        Select::make('user_id')
                            ->label('Donor')
                            ->required()
                            ->searchable(['name', 'first_name', 'last_name', 'email'])
                            ->getSearchResultsUsing(fn (string $search, Get $get): array => self::donorOptions($search, $get('family_id')))
                            ->getOptionLabelUsing(fn ($value): ?string => User::query()->find($value)?->displayFullName())
                            ->options(fn (Get $get): array => self::donorOptions('', $get('family_id'), 50)),
                        TextInput::make('amount')
                            ->numeric()
                            ->prefix('£')
                            ->minValue(0.01)
                            ->required(),
                        Select::make('method')
                            ->options(DonationMethod::options())
                            ->required(),
                        DatePicker::make('donated_on')
                            ->label('Date of gift')
                            ->required()
                            ->default(now())
                            ->maxDate(now())
                            ->native(false),
                        Select::make('status')
                            ->options([
                                DonationStatus::Pending->value => DonationStatus::Pending->label(),
                                DonationStatus::Approved->value => DonationStatus::Approved->label(),
                                DonationStatus::Rejected->value => DonationStatus::Rejected->label(),
                            ])
                            ->default(DonationStatus::Approved->value)
                            ->required()
                            ->helperText('Member-submitted gifts arrive as pending. Manual entries are usually recorded as approved.'),
                        TextInput::make('reference')
                            ->label('Bank reference / receipt')
                            ->maxLength(120)
                            ->columnSpanFull(),
                        Textarea::make('member_note')
                            ->label('Donor note')
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('admin_note')
                            ->label('Admin note')
                            ->rows(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    /**
     * @return array<int|string, string>
     */
    private static function donorOptions(string $search, mixed $familyId, int $limit = 50): array
    {
        return User::query()
            ->where('is_active', true)
            ->when(filled($familyId), fn (Builder $query): Builder => $query->where('family_id', (int) $familyId))
            ->when($search !== '', function (Builder $query) use ($search): void {
                $query->where(function (Builder $inner) use ($search): void {
                    $inner->where('name', 'like', "%{$search}%")
                        ->orWhere('first_name', 'like', "%{$search}%")
                        ->orWhere('last_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                });
            })
            ->orderBy('last_name')
            ->orderBy('first_name')
            ->limit($limit)
            ->get()
            ->mapWithKeys(fn (User $user): array => [
                $user->id => trim($user->displayFullName().' · '.($user->email ?? 'no email')),
            ])
            ->all();
    }
}
