<?php

namespace App\Filament\Resources\Services\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Service Details')
                    ->schema([
                        TextInput::make('title')
                            ->required(),
                        Textarea::make('description')
                            ->columnSpanFull(),
                        TextInput::make('language')
                            ->required(),
                        TextInput::make('frequency')
                            ->required(),
                    ]),
                Section::make('Schedule')
                    ->schema([
                        TextInput::make('service_day')
                            ->required(),
                        TextInput::make('service_time')
                            ->required(),
                    ]),
                Section::make('Location')
                    ->schema([
                        TextInput::make('location')
                            ->required(),
                        TextInput::make('address')
                            ->required(),
                        TextInput::make('map_link')
                            ->url(),
                        TextInput::make('online_stream_link')
                            ->url(),
                    ]),
                Section::make('Contact')
                    ->schema([
                        TextInput::make('contact_person'),
                        TextInput::make('contact_email')
                            ->email(),
                        TextInput::make('contact_phone')
                            ->tel(),
                    ]),
                Section::make('Settings')
                    ->schema([
                        TextInput::make('sort_order')
                            ->numeric()
                            ->default(0)
                            ->required(),
                        Select::make('status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                            ])
                            ->default('active')
                            ->required(),
                    ]),
            ]);
    }
}
