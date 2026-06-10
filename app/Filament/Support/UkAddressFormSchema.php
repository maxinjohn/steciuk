<?php

namespace App\Filament\Support;

use App\Rules\UkPostcode;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class UkAddressFormSchema
{
    /**
     * @return list<Section>
     */
    public static function settingsSection(
        string $prefix = 'contact',
        string $heading = 'UK postal address',
        ?string $description = 'Parish correspondence address shown on the contact page, footer, and search listings.',
    ): array {
        return [
            Section::make($heading)
                ->description($description)
                ->columns(2)
                ->schema(self::fields($prefix)),
        ];
    }

    /**
     * @return list<TextInput>
     */
    public static function fields(string $prefix = 'contact'): array
    {
        return [
            TextInput::make("{$prefix}_address_line_1")
                ->label('Address line 1')
                ->placeholder('House name or number and street')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make("{$prefix}_address_line_2")
                ->label('Address line 2')
                ->placeholder('Flat, locality, or additional detail (optional)')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make("{$prefix}_city")
                ->label('Town or city')
                ->maxLength(120),
            TextInput::make("{$prefix}_county")
                ->label('County')
                ->maxLength(120),
            TextInput::make("{$prefix}_postcode")
                ->label('Postcode')
                ->placeholder('e.g. M1 1AE')
                ->maxLength(12)
                ->extraInputAttributes(['class' => 'uppercase'])
                ->rules([new UkPostcode])
                ->validationAttribute('postcode'),
            TextInput::make("{$prefix}_country")
                ->label('Country')
                ->default('United Kingdom')
                ->maxLength(120)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<TextInput>
     */
    public static function modelFields(
        bool $requirePostcode = false,
        bool $requireLine1 = false,
    ): array {
        $postcodeRules = $requirePostcode
            ? ['required', 'string', 'max:12', new UkPostcode]
            : ['nullable', 'string', 'max:12', new UkPostcode];

        return [
            TextInput::make('address_line_1')
                ->label('Address line 1')
                ->placeholder('House name or number and street')
                ->maxLength(255)
                ->required($requireLine1)
                ->columnSpanFull(),
            TextInput::make('address_line_2')
                ->label('Address line 2')
                ->placeholder('Flat, locality, or additional detail (optional)')
                ->maxLength(255)
                ->columnSpanFull(),
            TextInput::make('city')
                ->label('Town or city')
                ->maxLength(120)
                ->required($requireLine1),
            TextInput::make('county')
                ->label('County')
                ->maxLength(120),
            TextInput::make('postcode')
                ->label('Postcode')
                ->placeholder('e.g. M1 1AE')
                ->maxLength(12)
                ->extraInputAttributes(['class' => 'uppercase'])
                ->rules($postcodeRules)
                ->validationAttribute('postcode'),
        ];
    }
}
