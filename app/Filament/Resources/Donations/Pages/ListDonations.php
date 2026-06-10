<?php

namespace App\Filament\Resources\Donations\Pages;

use App\Filament\Resources\Donations\DonationResource;
use App\Models\Donation;
use App\Models\User;
use App\Services\DonationReportService;
use App\Support\DonationReportScope;
use App\Support\FamilyLabel;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Pages\ListRecords;

class ListDonations extends ListRecords
{
    protected static string $resource = DonationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make()
                ->label('Record donation')
                ->visible(fn (): bool => auth()->user()?->can('create', Donation::class) ?? false),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon('heroicon-o-document-arrow-down')
                ->visible(fn (): bool => auth()->user()?->can('viewAny', Donation::class) ?? false)
                ->form([
                    Select::make('scope')
                        ->label('Export scope')
                        ->options([
                            DonationReportScope::All->value => DonationReportScope::All->label(),
                            DonationReportScope::Family->value => DonationReportScope::Family->label(),
                            DonationReportScope::Member->value => DonationReportScope::Member->label(),
                        ])
                        ->default(DonationReportScope::All->value)
                        ->required()
                        ->live(),
                    Select::make('family_id')
                        ->label('Family household')
                        ->options(fn (): array => FamilyLabel::selectOptions())
                        ->searchable()
                        ->visible(fn (callable $get): bool => $get('scope') === DonationReportScope::Family->value)
                        ->required(fn (callable $get): bool => $get('scope') === DonationReportScope::Family->value),
                    Select::make('user_id')
                        ->label('Member')
                        ->options(fn (): array => User::query()->orderBy('last_name')->orderBy('first_name')->limit(200)->get()->mapWithKeys(
                            fn (User $user): array => [$user->id => trim($user->displayFullName().' · '.($user->email ?? 'no email'))]
                        )->all())
                        ->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => User::query()
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
                            ->mapWithKeys(fn (User $user): array => [$user->id => trim($user->displayFullName().' · '.($user->email ?? 'no email'))])
                            ->all())
                        ->visible(fn (callable $get): bool => $get('scope') === DonationReportScope::Member->value)
                        ->required(fn (callable $get): bool => $get('scope') === DonationReportScope::Member->value),
                    DatePicker::make('from')
                        ->label('From date')
                        ->native(false)
                        ->maxDate(now()),
                    DatePicker::make('to')
                        ->label('To date')
                        ->native(false)
                        ->maxDate(now())
                        ->helperText('Leave dates blank to export the current calendar month.'),
                    TextInput::make('month')
                        ->label('Single month')
                        ->placeholder(now()->format('Y-m'))
                        ->helperText('Optional YYYY-MM — overrides the date range above.'),
                    Toggle::make('include_all_statuses')
                        ->label('Include pending and rejected gifts')
                        ->helperText('Leave off for an approved-giving statement only.'),
                ])
                ->action(fn (array $data) => app(DonationReportService::class)->adminPdfResponse(auth()->user(), $data)),
        ];
    }
}
