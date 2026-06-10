<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Rules\UkPostcode;
use App\Services\UkAddressLookup;
use App\Support\ServiceMapLink;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\ViewField;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class ServiceLocationFormSchema
{
    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    public static function components(): array
    {
        return [
            TextInput::make('location')
                ->label('Worship location name')
                ->placeholder('e.g. Manchester, Bristol, Parish hall')
                ->required()
                ->maxLength(120)
                ->columnSpanFull()
                ->helperText('Short label shown on the public service times page.'),
            Grid::make(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('postcode')
                        ->label('Postcode')
                        ->placeholder('e.g. M1 1AE')
                        ->maxLength(12)
                        ->live(debounce: 500)
                        ->extraInputAttributes(['class' => 'uppercase'])
                        ->rules(['nullable', 'string', 'max:12', new UkPostcode])
                        ->suffixAction(
                            Action::make('lookupPostcode')
                                ->label('Find address')
                                ->icon(Heroicon::OutlinedMagnifyingGlassCircle)
                                ->action(function (Get $get, Set $set): void {
                                    self::lookupPostcode($get, $set);
                                })
                        ),
                    Select::make('address_lookup_pick')
                        ->label('Choose property')
                        ->options(fn (Get $get): array => self::addressOptions($get))
                        ->visible(fn (Get $get): bool => count(self::addressOptions($get)) > 1)
                        ->live()
                        ->dehydrated(false)
                        ->searchable()
                        ->afterStateUpdated(function (?string $state, Get $get, Set $set): void {
                            if (blank($state)) {
                                return;
                            }

                            $address = self::findAddressOption($get, $state);

                            if ($address === null) {
                                return;
                            }

                            $set('address_line_1', $address['line_1']);
                            $set('address_line_2', $address['line_2']);
                            $set('city', $address['city'] ?: $get('city'));
                            $set('county', $address['county'] ?: $get('county'));
                        }),
                ]),
            TextInput::make('address_line_1')
                ->label('Address line 1')
                ->placeholder('House name or number and street')
                ->maxLength(255)
                ->required()
                ->columnSpanFull(),
            TextInput::make('address_line_2')
                ->label('Address line 2')
                ->placeholder('Flat, locality, or additional detail (optional)')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('city')
                ->label('Town or city')
                ->maxLength(120)
                ->required(),
            TextInput::make('county')
                ->label('County')
                ->maxLength(120),
            TextInput::make('map_link')
                ->label('Google Maps link')
                ->url()
                ->placeholder('Auto-generated when you save with a postcode or address')
                ->suffixAction(
                    Action::make('generateMapLink')
                        ->label('Generate')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->action(function (Get $get, Set $set): void {
                            $service = new \App\Models\Service([
                                'location' => $get('location'),
                                'address_line_1' => $get('address_line_1'),
                                'address_line_2' => $get('address_line_2'),
                                'city' => $get('city'),
                                'county' => $get('county'),
                                'postcode' => $get('postcode'),
                                'latitude' => $get('latitude'),
                                'longitude' => $get('longitude'),
                            ]);

                            ServiceMapLink::syncCoordinates($service);
                            $set('latitude', $service->latitude);
                            $set('longitude', $service->longitude);
                            $set('map_link', ServiceMapLink::generate($service));

                            Notification::make()
                                ->success()
                                ->title('Map link ready')
                                ->send();
                        })
                )
                ->columnSpanFull(),
            Grid::make(['default' => 1, 'md' => 2])
                ->schema([
                    TextInput::make('latitude')
                        ->numeric()
                        ->label('Latitude')
                        ->disabled()
                        ->dehydrated(),
                    TextInput::make('longitude')
                        ->numeric()
                        ->label('Longitude')
                        ->disabled()
                        ->dehydrated(),
                ]),
            ViewField::make('map_preview')
                ->view('filament.services.map-preview')
                ->viewData(fn (Get $get): array => [
                    'mapLink' => $get('map_link'),
                    'embedUrl' => ServiceMapLink::embedUrl(
                        filled($get('latitude')) ? (float) $get('latitude') : null,
                        filled($get('longitude')) ? (float) $get('longitude') : null,
                    ),
                    'address' => collect([
                        $get('address_line_1'),
                        $get('address_line_2'),
                        $get('city'),
                        $get('county'),
                        $get('postcode'),
                    ])->filter()->implode(', '),
                ])
                ->columnSpanFull(),
            TextInput::make('online_stream_link')
                ->label('Online stream link')
                ->url()
                ->placeholder('YouTube or live stream URL')
                ->columnSpanFull(),
        ];
    }

    /**
     * @return array<string, string>
     */
    private static function addressOptions(Get $get): array
    {
        $result = self::lookupResult($get);

        if ($result === null) {
            return [];
        }

        return collect($result['addresses'])
            ->mapWithKeys(fn (array $address): array => [
                $address['id'] => $address['label'],
            ])
            ->all();
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function findAddressOption(Get $get, string $id): ?array
    {
        $result = self::lookupResult($get);

        if ($result === null) {
            return null;
        }

        $address = collect($result['addresses'])->firstWhere('id', $id);

        return is_array($address) ? $address : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    private static function lookupResult(Get $get): ?array
    {
        $postcode = trim((string) $get('postcode'));

        if ($postcode === '') {
            return null;
        }

        return app(UkAddressLookup::class)->lookup($postcode);
    }

    private static function lookupPostcode(Get $get, Set $set): void
    {
        $postcode = trim((string) $get('postcode'));

        if ($postcode === '') {
            Notification::make()
                ->warning()
                ->title('Enter a postcode first')
                ->send();

            return;
        }

        $result = app(UkAddressLookup::class)->lookup($postcode);

        if ($result === null) {
            Notification::make()
                ->danger()
                ->title('Postcode not found')
                ->body('Check the postcode or enter the address manually.')
                ->send();

            return;
        }

        $set('postcode', $result['postcode']);
        $set('city', $result['city']);
        $set('county', $result['county']);

        $addresses = $result['addresses'];

        if (count($addresses) === 1) {
            $address = $addresses[0];
            $set('address_line_1', $address['line_1']);
            $set('address_line_2', $address['line_2']);
            $set('city', $address['city'] ?: $result['city']);
            $set('county', $address['county'] ?: $result['county']);
            $set('address_lookup_pick', $address['id']);

            Notification::make()
                ->success()
                ->title('Address filled')
                ->send();

            return;
        }

        if ($addresses !== []) {
            $set('address_lookup_pick', null);

            Notification::make()
                ->success()
                ->title('Choose your property')
                ->body('Select the correct address from the list below.')
                ->send();

            return;
        }

        Notification::make()
            ->info()
            ->title('Area details filled')
            ->body('Town and county were filled from the postcode. Add the street address manually.')
            ->send();
    }
}
