<?php

namespace App\Filament\Resources\Services\Schemas;

use App\Enums\ServiceScheduleType;
use App\Support\ServiceSchedule;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\ToggleButtons;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Support\Icons\Heroicon;

class ServiceScheduleFormSchema
{
    /**
     * @return list<\Filament\Schemas\Components\Component|\Filament\Forms\Components\Component>
     */
    public static function components(): array
    {
        return [
            Section::make('Step 1 · What kind of schedule?')
                ->description('Pick one option below. The next step appears immediately with the right date and time fields.')
                ->icon(Heroicon::OutlinedCursorArrowRays)
                ->schema([
                    ToggleButtons::make('schedule_type')
                        ->label('Schedule type')
                        ->options([
                            ServiceScheduleType::OneTime->value => 'Single date',
                            ServiceScheduleType::Multiple->value => 'Several dates',
                            ServiceScheduleType::Recurring->value => 'Repeating',
                        ])
                        ->icons([
                            ServiceScheduleType::OneTime->value => Heroicon::OutlinedCalendar,
                            ServiceScheduleType::Multiple->value => Heroicon::OutlinedCalendarDateRange,
                            ServiceScheduleType::Recurring->value => Heroicon::OutlinedArrowPath,
                        ])
                        ->tooltips([
                            ServiceScheduleType::OneTime->value => 'One specific day — e.g. a special service or guest event.',
                            ServiceScheduleType::Multiple->value => 'A list of separate dates — e.g. Lent services or a short series.',
                            ServiceScheduleType::Recurring->value => 'Same pattern every week or month — e.g. every Sunday or monthly worship.',
                        ])
                        ->default(ServiceScheduleType::Recurring->value)
                        ->required()
                        ->live()
                        ->inline(false)
                        ->columnSpanFull()
                        ->afterStateUpdated(function (?string $state, Set $set): void {
                            $type = ServiceScheduleType::tryFrom((string) $state)
                                ?? ServiceScheduleType::Recurring;

                            $set('schedule_data', ServiceSchedule::defaultData($type));
                        }),
                ]),
            Section::make('Step 2 · Single date & time')
                ->description('When is this one-off worship service?')
                ->icon(Heroicon::OutlinedCalendarDays)
                ->visible(fn (Get $get): bool => $get('schedule_type') === ServiceScheduleType::OneTime->value)
                ->schema(self::singleDateFields()),
            Section::make('Step 2 · All service dates')
                ->description('Add every date this location meets. Each row needs a date and start time.')
                ->icon(Heroicon::OutlinedCalendarDateRange)
                ->visible(fn (Get $get): bool => $get('schedule_type') === ServiceScheduleType::Multiple->value)
                ->schema([
                    Repeater::make('schedule_data.occurrences')
                        ->label('Dates')
                        ->schema(self::occurrenceFields())
                        ->defaultItems(2)
                        ->minItems(2)
                        ->addActionLabel('Add another date')
                        ->collapsible()
                        ->itemLabel(fn (array $state): ?string => filled($state['date'] ?? null)
                            ? date('l j F Y', strtotime((string) $state['date']))
                            : 'New date — pick below')
                        ->columnSpanFull(),
                ]),
            Section::make('Step 2 · Repeating pattern')
                ->description('Tell visitors how often worship happens and at what time.')
                ->icon(Heroicon::OutlinedArrowPath)
                ->visible(fn (Get $get): bool => $get('schedule_type') === ServiceScheduleType::Recurring->value)
                ->schema(self::recurringFields()),
            Section::make('Visitors will read')
                ->description('This is the summary shown on the public service times page.')
                ->icon(Heroicon::OutlinedEye)
                ->schema([
                    Placeholder::make('schedule_preview')
                        ->hiddenLabel()
                        ->content(fn (Get $get): string => ServiceSchedule::previewFromForm(
                            (string) ($get('schedule_type') ?? ''),
                            is_array($get('schedule_data')) ? $get('schedule_data') : [],
                        ))
                        ->columnSpanFull(),
                ]),
        ];
    }

    /**
     * @return list<\Filament\Forms\Components\Component>
     */
    private static function singleDateFields(): array
    {
        return [
            Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])
                ->schema([
                    DatePicker::make('schedule_data.occurrences.0.date')
                        ->label('Service date')
                        ->helperText('The calendar date of this worship.')
                        ->native(false)
                        ->required()
                        ->columnSpan(1),
                    TimePicker::make('schedule_data.occurrences.0.start_time')
                        ->label('Starts at')
                        ->helperText('When worship begins.')
                        ->seconds(false)
                        ->native(false)
                        ->default('10:30')
                        ->required()
                        ->columnSpan(1),
                    TimePicker::make('schedule_data.occurrences.0.end_time')
                        ->label('Ends at (optional)')
                        ->helperText('Leave blank if there is no fixed finish time.')
                        ->seconds(false)
                        ->native(false)
                        ->columnSpan(1),
                ]),
            Textarea::make('schedule_data.occurrences.0.note')
                ->label('Extra note (optional)')
                ->placeholder('e.g. Combined service with Leicester — all welcome.')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }

    /**
     * @return list<\Filament\Forms\Components\Component>
     */
    private static function recurringFields(): array
    {
        return [
            Select::make('schedule_data.interval_unit')
                ->label('How often?')
                ->options([
                    'weekly' => 'Every week (same weekday)',
                    'fortnightly' => 'Every 2 weeks (same weekday)',
                    'monthly' => 'Every month',
                ])
                ->default('monthly')
                ->required()
                ->live()
                ->columnSpanFull()
                ->helperText('Choose weekly if worship is every Sunday (or another fixed weekday). Choose monthly for parish locations that meet once a month.'),
            Grid::make(['default' => 1, 'md' => 2])
                ->visible(fn (Get $get): bool => in_array($get('schedule_data.interval_unit'), ['weekly', 'fortnightly'], true))
                ->schema([
                    Select::make('schedule_data.weekday')
                        ->label('Which weekday?')
                        ->options(ServiceSchedule::weekdayOptions())
                        ->default('sunday')
                        ->required()
                        ->helperText('The day worship happens each week.'),
                    Grid::make(['default' => 1, 'md' => 2])
                        ->schema([
                            TimePicker::make('schedule_data.start_time')
                                ->label('Starts at')
                                ->seconds(false)
                                ->native(false)
                                ->default('10:30')
                                ->required(),
                            TimePicker::make('schedule_data.end_time')
                                ->label('Ends at (optional)')
                                ->seconds(false)
                                ->native(false),
                        ]),
                ]),
            Grid::make(['default' => 1, 'md' => 2])
                ->visible(fn (Get $get): bool => ($get('schedule_data.interval_unit') ?? 'monthly') === 'monthly')
                ->schema([
                    TextInput::make('schedule_data.interval')
                        ->label('Every how many months?')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(12)
                        ->default(1)
                        ->suffix('month(s)')
                        ->helperText('Use 1 for “once a month”.'),
                    Select::make('schedule_data.monthly_mode')
                        ->label('Which day each month?')
                        ->options(ServiceSchedule::monthlyModeOptions())
                        ->default('flexible')
                        ->required()
                        ->live()
                        ->helperText('Most UK parish locations use “Dates change each month”.'),
                    TextInput::make('schedule_data.day_of_month')
                        ->label('Date of the month')
                        ->numeric()
                        ->minValue(1)
                        ->maxValue(31)
                        ->suffix('th of the month')
                        ->visible(fn (Get $get): bool => ($get('schedule_data.monthly_mode') ?? '') === 'day_of_month')
                        ->required(fn (Get $get): bool => ($get('schedule_data.monthly_mode') ?? '') === 'day_of_month'),
                    Select::make('schedule_data.nth')
                        ->label('Which week?')
                        ->options(ServiceSchedule::nthOptions())
                        ->default(1)
                        ->visible(fn (Get $get): bool => ($get('schedule_data.monthly_mode') ?? '') === 'nth_weekday')
                        ->required(fn (Get $get): bool => ($get('schedule_data.monthly_mode') ?? '') === 'nth_weekday'),
                    Select::make('schedule_data.weekday')
                        ->label('Which weekday?')
                        ->options(ServiceSchedule::weekdayOptions())
                        ->default('sunday')
                        ->visible(fn (Get $get): bool => in_array($get('schedule_data.monthly_mode'), ['nth_weekday', 'last_weekday'], true))
                        ->required(fn (Get $get): bool => in_array($get('schedule_data.monthly_mode'), ['nth_weekday', 'last_weekday'], true)),
                    TimePicker::make('schedule_data.start_time')
                        ->label('Starts at (if fixed)')
                        ->seconds(false)
                        ->native(false)
                        ->visible(fn (Get $get): bool => ($get('schedule_data.monthly_mode') ?? '') !== 'flexible'),
                    TimePicker::make('schedule_data.end_time')
                        ->label('Ends at (optional)')
                        ->seconds(false)
                        ->native(false)
                        ->visible(fn (Get $get): bool => ($get('schedule_data.monthly_mode') ?? '') !== 'flexible'),
                    Textarea::make('schedule_data.note')
                        ->label('Message for visitors')
                        ->placeholder('e.g. Contact admin@steciuk.org for the current monthly date, time, and venue.')
                        ->rows(3)
                        ->columnSpanFull()
                        ->helperText('Required for “Dates change each month”. This text appears on the website when the exact date is not fixed.'),
                ]),
        ];
    }

    /**
     * @return list<\Filament\Forms\Components\Component>
     */
    private static function occurrenceFields(): array
    {
        return [
            Grid::make(['default' => 1, 'md' => 2, 'xl' => 3])
                ->schema([
                    DatePicker::make('date')
                        ->label('Date')
                        ->native(false)
                        ->required(),
                    TimePicker::make('start_time')
                        ->label('Starts at')
                        ->seconds(false)
                        ->native(false)
                        ->default('10:30')
                        ->required(),
                    TimePicker::make('end_time')
                        ->label('Ends at (optional)')
                        ->seconds(false)
                        ->native(false),
                ]),
            Textarea::make('note')
                ->label('Note (optional)')
                ->rows(2)
                ->columnSpanFull(),
        ];
    }
}
