<?php

namespace App\Filament\Support;

use App\Support\FaithComfortVerseBuckets;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

final class FaithComfortFormSchema
{
    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    public static function settingsTabs(): array
    {
        return [
            SettingsFormTabs::make('Faith & comfort', [
                self::verseTab(
                    label: 'All pages',
                    icon: 'heroicon-o-globe-alt',
                    field: FaithComfortVerseBuckets::FIELD_ALL,
                    description: 'Footer ribbon and fallback pool for every page type.',
                ),
                self::verseTab(
                    label: 'Error pages',
                    icon: 'heroicon-o-exclamation-triangle',
                    field: FaithComfortVerseBuckets::FIELD_ERROR,
                    description: '404, 500, and other error screens.',
                ),
                self::verseTab(
                    label: 'Maintenance',
                    icon: 'heroicon-o-wrench-screwdriver',
                    field: FaithComfortVerseBuckets::FIELD_MAINTENANCE,
                    description: 'Maintenance gate pages while work is in progress.',
                ),
                self::verseTab(
                    label: 'Launch',
                    icon: 'heroicon-o-rocket-launch',
                    field: FaithComfortVerseBuckets::FIELD_LAUNCH,
                    description: 'Pre-launch and countdown gate pages.',
                ),
                self::pathVerseTab(),
                Tab::make('Footer ribbon')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Section::make('Footer ribbon lines')
                            ->description('Rotating kicker and closing note above the Scripture in the site footer. One pair is shown per day.')
                            ->compact()
                            ->schema([self::ribbonRepeater()]),
                    ]),
                Tab::make('Comfort block')
                    ->icon('heroicon-o-squares-2x2')
                    ->schema([
                        Section::make('Comfort headings')
                            ->description('Rotating kicker, heading, and subheading for the on-page comfort section. One set is shown per day.')
                            ->compact()
                            ->schema([self::comfortHeaderRepeater()]),
                        Section::make('Comfort cards')
                            ->description('Cards shown beneath the comfort heading.')
                            ->compact()
                            ->schema([self::comfortCardRepeater()]),
                    ]),
            ], 'faith-tab'),
        ];
    }

    private static function verseTab(string $label, string $icon, string $field, string $description): Tab
    {
        return Tab::make($label)
            ->icon($icon)
            ->schema([
                Section::make($label)
                    ->description($description.' Ships with 50+ prefilled verses. One is chosen at random on each visit.')
                    ->compact()
                    ->schema([self::verseRepeater($field)]),
            ]);
    }

    private static function pathVerseTab(): Tab
    {
        return Tab::make('Custom URL')
            ->icon('heroicon-o-link')
            ->schema([
                Section::make('Custom URL verses')
                    ->description('Optional verses for a specific path only. Same fields as other tabs, plus the page path.')
                    ->compact()
                    ->schema([self::pathVerseRepeater(FaithComfortVerseBuckets::FIELD_PATHS)]),
            ]);
    }

    private static function verseRepeater(string $field): Repeater
    {
        return Repeater::make($field)
            ->label('Verses')
            ->addActionLabel('Add verse')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->defaultItems(0)
            ->table([
                TableColumn::make('Reference')->width('9rem'),
                TableColumn::make('Verse'),
            ])
            ->schema([
                TextInput::make('ref')
                    ->label('Reference')
                    ->placeholder('Psalm 23:1')
                    ->required()
                    ->maxLength(80),
                Textarea::make('text')
                    ->label('Verse')
                    ->rows(2)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function pathVerseRepeater(string $field): Repeater
    {
        return Repeater::make($field)
            ->label('Verses')
            ->addActionLabel('Add verse')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->defaultItems(0)
            ->table([
                TableColumn::make('Path')->width('8rem'),
                TableColumn::make('Reference')->width('9rem'),
                TableColumn::make('Verse'),
            ])
            ->schema([
                TextInput::make('path')
                    ->label('Page path')
                    ->placeholder('/contact')
                    ->required()
                    ->maxLength(80),
                TextInput::make('ref')
                    ->label('Reference')
                    ->placeholder('Psalm 23:1')
                    ->required()
                    ->maxLength(80),
                Textarea::make('text')
                    ->label('Verse')
                    ->rows(2)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function ribbonRepeater(): Repeater
    {
        return Repeater::make('faith_sanctuary_ribbons')
            ->label('Ribbon lines')
            ->addActionLabel('Add line')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->defaultItems(0)
            ->table([
                TableColumn::make('Kicker')->width('10rem'),
                TableColumn::make('Closing note'),
            ])
            ->schema([
                TextInput::make('kicker')
                    ->label('Kicker')
                    ->placeholder('In Christ\'s peace')
                    ->required()
                    ->maxLength(80),
                Textarea::make('note')
                    ->label('Closing note')
                    ->rows(2)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function comfortHeaderRepeater(): Repeater
    {
        return Repeater::make('faith_comfort_headers')
            ->label('Heading sets')
            ->addActionLabel('Add heading set')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->defaultItems(0)
            ->table([
                TableColumn::make('Kicker')->width('8rem'),
                TableColumn::make('Heading')->width('10rem'),
                TableColumn::make('Subheading'),
            ])
            ->schema([
                TextInput::make('kicker')
                    ->label('Kicker')
                    ->required()
                    ->maxLength(80),
                TextInput::make('heading')
                    ->label('Heading')
                    ->required()
                    ->maxLength(120),
                Textarea::make('subheading')
                    ->label('Subheading')
                    ->rows(2)
                    ->required()
                    ->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function comfortCardRepeater(): Repeater
    {
        return Repeater::make('faith_comfort_cards')
            ->label('Cards')
            ->addActionLabel('Add card')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->defaultItems(0)
            ->table([
                TableColumn::make('Icon')->width('3rem'),
                TableColumn::make('Title')->width('8rem'),
                TableColumn::make('Reference')->width('8rem'),
                TableColumn::make('Text'),
                TableColumn::make('Link')->width('7rem'),
            ])
            ->schema([
                TextInput::make('icon')
                    ->label('Icon')
                    ->default('🕊')
                    ->maxLength(8),
                TextInput::make('title')
                    ->label('Title')
                    ->required()
                    ->maxLength(80),
                TextInput::make('ref')
                    ->label('Reference')
                    ->required()
                    ->maxLength(80),
                Textarea::make('text')
                    ->label('Text')
                    ->rows(2)
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('link')
                    ->label('Link')
                    ->placeholder('/prayer-request'),
                TextInput::make('linkLabel')
                    ->label('Link label')
                    ->placeholder('Learn more'),
            ])
            ->columnSpanFull();
    }
}
