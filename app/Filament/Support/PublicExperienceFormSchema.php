<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs\Tab;

final class PublicExperienceFormSchema
{
    /**
     * @return list<\Filament\Schemas\Components\Component>
     */
    public static function settingsTabs(): array
    {
        return [
            SettingsFormTabs::make('Public experience', [
                Tab::make('Spark strip')
                    ->icon('heroicon-o-fire')
                    ->schema([
                        Section::make('Footer faith spark strip')
                            ->description('Horizontal chip row above the sanctuary peace block — Gen Z anchors with Scripture references.')
                            ->compact()
                            ->schema([
                                TextInput::make('public_ui_spark_strip.kicker')
                                    ->label('Kicker')
                                    ->maxLength(80)
                                    ->default('Anchored in Christ'),
                                self::sparkRepeater(),
                            ]),
                    ]),
                Tab::make('Divine whispers')
                    ->icon('heroicon-o-chat-bubble-bottom-center-text')
                    ->schema([
                        Section::make('Rotating footer whispers')
                            ->description('Gentle Scripture lines that rotate in the bar above the spark strip.')
                            ->compact()
                            ->schema([self::whisperRepeater('public_ui_divine_whispers')]),
                    ]),
                Tab::make('Action strip')
                    ->icon('heroicon-o-bolt')
                    ->schema([
                        Section::make('Parish quick actions')
                            ->description('Cards shown under page intros on listings and detail bridges.')
                            ->compact()
                            ->schema([
                                TextInput::make('public_ui_action_strip.kicker')
                                    ->label('Kicker')
                                    ->maxLength(120)
                                    ->default('Draw near · Worship · Pray'),
                                self::actionRepeater(),
                            ]),
                    ]),
                Tab::make('Page intro')
                    ->icon('heroicon-o-book-open')
                    ->schema([
                        Section::make('Default page band copy')
                            ->description('Fallback kicker and Scripture ribbon when a page does not override them.')
                            ->compact()
                            ->schema([
                                TextInput::make('public_ui_page_intro.kicker')
                                    ->label('Band kicker')
                                    ->maxLength(120),
                                Textarea::make('public_ui_page_intro.scripture')
                                    ->label('Scripture ribbon text')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                TextInput::make('public_ui_page_intro.scripture_ref')
                                    ->label('Scripture reference')
                                    ->maxLength(80),
                            ]),
                    ]),
                Tab::make('Context Scripture')
                    ->icon('heroicon-o-map')
                    ->schema([
                        Section::make('Route-aware Scripture nudges')
                            ->description('Top-of-page nudge and context copy per section. Use slug for CMS pages only.')
                            ->compact()
                            ->schema([self::contextScriptureRepeater()]),
                    ]),
                Tab::make('Experience')
                    ->icon('heroicon-o-sparkles')
                    ->schema([
                        Section::make('Performance & atmosphere')
                            ->description('Toggle futuristic layers without redeploying code.')
                            ->compact()
                            ->schema([
                                Toggle::make('public_ui_experience.enabled')
                                    ->label('Future-ready layer (master)')
                                    ->default(true),
                                Toggle::make('public_ui_experience.heavenly_atmosphere')
                                    ->label('Heavenly atmosphere (site-wide orbs & crosses)')
                                    ->default(true),
                                Toggle::make('public_ui_experience.speculation_rules')
                                    ->label('Smart link prefetch (Speculation Rules)')
                                    ->default(true),
                                Toggle::make('public_ui_experience.reading_progress')
                                    ->label('Reading progress bar on articles')
                                    ->default(true),
                            ]),
                        Section::make('Prayer floating button')
                            ->compact()
                            ->schema([
                                TextInput::make('public_ui_prayer_fab.label')
                                    ->label('Button label')
                                    ->maxLength(24)
                                    ->default('Pray'),
                                TextInput::make('public_ui_prayer_fab.url')
                                    ->label('Link path')
                                    ->placeholder('/prayer-request')
                                    ->maxLength(255),
                                TextInput::make('public_ui_prayer_fab.aria_label')
                                    ->label('Accessibility label')
                                    ->maxLength(120)
                                    ->columnSpanFull(),
                            ]),
                    ]),
            ], 'experience-tab'),
        ];
    }

    private static function sparkRepeater(): Repeater
    {
        return Repeater::make('public_ui_spark_strip.items')
            ->label('Spark chips')
            ->addActionLabel('Add chip')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->table([
                TableColumn::make('Label')->width('8rem'),
                TableColumn::make('Reference')->width('7rem'),
                TableColumn::make('Link'),
            ])
            ->schema([
                TextInput::make('label')->required()->maxLength(40),
                TextInput::make('ref')->label('Reference')->maxLength(40),
                TextInput::make('href')->label('Link path')->placeholder('/our-church')->maxLength(255),
            ])
            ->columnSpanFull();
    }

    private static function whisperRepeater(string $field): Repeater
    {
        return Repeater::make($field)
            ->label('Whispers')
            ->addActionLabel('Add whisper')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->table([
                TableColumn::make('Reference')->width('9rem'),
                TableColumn::make('Verse'),
            ])
            ->schema([
                TextInput::make('ref')->label('Reference')->required()->maxLength(80),
                Textarea::make('text')->label('Verse')->rows(2)->required()->columnSpanFull(),
            ])
            ->columnSpanFull();
    }

    private static function actionRepeater(): Repeater
    {
        return Repeater::make('public_ui_action_strip.items')
            ->label('Action cards')
            ->addActionLabel('Add card')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->table([
                TableColumn::make('Label')->width('9rem'),
                TableColumn::make('Description'),
            ])
            ->schema([
                TextInput::make('label')->required()->maxLength(60),
                TextInput::make('desc')->label('Description')->maxLength(120),
                TextInput::make('href')->label('Link path')->placeholder('/sermons')->maxLength(255),
                TextInput::make('icon')->label('Icon')->maxLength(8)->default('✝'),
                Select::make('tone')
                    ->options([
                        'gold' => 'Gold',
                        'navy' => 'Navy',
                        'rose' => 'Rose',
                        'violet' => 'Violet',
                        'sky' => 'Sky',
                    ])
                    ->default('gold'),
            ])
            ->columnSpanFull();
    }

    private static function contextScriptureRepeater(): Repeater
    {
        return Repeater::make('public_ui_context_scripture')
            ->label('Context entries')
            ->addActionLabel('Add route')
            ->cloneable()
            ->reorderableWithDragAndDrop()
            ->table([
                TableColumn::make('Route')->width('10rem'),
                TableColumn::make('Slug')->width('8rem'),
                TableColumn::make('Kicker')->width('8rem'),
            ])
            ->schema([
                Select::make('route')
                    ->options(self::routeOptions())
                    ->required()
                    ->searchable(),
                TextInput::make('slug')
                    ->label('CMS slug (pages only)')
                    ->placeholder('contact')
                    ->maxLength(80),
                TextInput::make('kicker')->required()->maxLength(60),
                Textarea::make('text')->label('Verse')->rows(2)->required()->columnSpanFull(),
                TextInput::make('ref')->label('Reference')->required()->maxLength(80),
            ])
            ->columnSpanFull();
    }

    /**
     * @return array<string, string>
     */
    private static function routeOptions(): array
    {
        return [
            'home' => 'Home',
            'events.*' => 'Events',
            'news.*' => 'News',
            'sermons.*' => 'Sermons',
            'gallery.*' => 'Gallery',
            'ministries.*' => 'Ministries',
            'give' => 'Give',
            'services.*' => 'Service times',
            'resources.*' => 'Resources',
            'pages.show' => 'CMS page (set slug)',
            'default' => 'Fallback (everything else)',
        ];
    }
}
