<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Filament\Support\ResourceFormTabs;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;

class ServiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->columns(1)
            ->components([
                ResourceFormTabs::make('Worship service', [
                    Tab::make('Overview')
                        ->icon(Heroicon::OutlinedSparkles)
                        ->schema([
                            Section::make('Service story')
                                ->description('What visitors see first on the service times page.')
                                ->icon(Heroicon::OutlinedBookOpen)
                                ->extraAttributes(['class' => 'service-form-section service-form-section--hero'])
                                ->schema([
                                    TextInput::make('title')
                                        ->label('Service title')
                                        ->placeholder('e.g. Manchester Worship Service')
                                        ->required()
                                        ->maxLength(120)
                                        ->columnSpanFull(),
                                    Textarea::make('description')
                                        ->label('Description')
                                        ->rows(5)
                                        ->placeholder('Tell visitors what to expect — worship, Holy Communion, fellowship, languages, and who is welcome.')
                                        ->columnSpanFull(),
                                    TextInput::make('language')
                                        ->label('Languages')
                                        ->placeholder('English & Malayalam')
                                        ->required()
                                        ->maxLength(120),
                                ]),
                        ]),
                    Tab::make('Schedule')
                        ->icon(Heroicon::OutlinedCalendarDays)
                        ->schema([
                            Section::make('When we meet')
                                ->description('Follow the steps — pick a type first, then fill in the date and time fields that appear.')
                                ->icon(Heroicon::OutlinedClock)
                                ->extraAttributes(['class' => 'service-form-section service-form-section--schedule'])
                                ->schema(ServiceScheduleFormSchema::components()),
                        ]),
                    Tab::make('Location')
                        ->icon(Heroicon::OutlinedMapPin)
                        ->schema([
                            Section::make('Venue & map')
                                ->description('Find the address with a postcode, preview the map, and add a stream link if needed.')
                                ->icon(Heroicon::OutlinedBuildingOffice2)
                                ->columns(2)
                                ->extraAttributes(['class' => 'service-form-section service-form-section--location'])
                                ->schema(ServiceLocationFormSchema::components()),
                        ]),
                    Tab::make('Contact')
                        ->icon(Heroicon::OutlinedPhone)
                        ->schema([
                            Section::make('Visitor contact')
                                ->description('Who should people reach for this worship location?')
                                ->icon(Heroicon::OutlinedChatBubbleLeftRight)
                                ->columns(2)
                                ->extraAttributes(['class' => 'service-form-section'])
                                ->schema([
                                    TextInput::make('contact_person')
                                        ->label('Contact person')
                                        ->placeholder('Parish Office'),
                                    TextInput::make('contact_email')
                                        ->label('Contact email')
                                        ->email()
                                        ->placeholder('admin@steciuk.org'),
                                    TextInput::make('contact_phone')
                                        ->label('Phone number')
                                        ->tel()
                                        ->placeholder('e.g. 07578 189530')
                                        ->columnSpanFull(),
                                ]),
                        ]),
                    Tab::make('Publish')
                        ->icon(Heroicon::OutlinedRocketLaunch)
                        ->schema([
                            Section::make('Go live')
                                ->description('Control visibility and the order this location appears on the website.')
                                ->icon(Heroicon::OutlinedAdjustmentsHorizontal)
                                ->columns(2)
                                ->extraAttributes(['class' => 'service-form-section'])
                                ->schema([
                                    TextInput::make('sort_order')
                                        ->label('Display order')
                                        ->numeric()
                                        ->default(0)
                                        ->required()
                                        ->helperText('Lower numbers appear first on the service times page.'),
                                    Select::make('status')
                                        ->options([
                                            'active' => 'Active — visible on the website',
                                            'inactive' => 'Hidden — keep in admin only',
                                        ])
                                        ->default('active')
                                        ->required(),
                                ]),
                        ]),
                ], 'service-tab'),
            ]);
    }
}
