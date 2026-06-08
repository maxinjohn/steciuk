<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PublishStatus;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class PageForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Tabs::make('Page')
                    ->tabs([
                        Tab::make('Content')
                            ->icon('heroicon-o-document-text')
                            ->schema([
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, string $operation): void {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('URL path for this page, e.g. welcome → /welcome'),
                                RichEditor::make('content')
                                    ->columnSpanFull()
                                    ->helperText('Full page body — fully editable rich text with headings, lists, links, and images.'),
                            ]),
                        Tab::make('Hero')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Toggle::make('show_hero')
                                    ->label('Show hero section')
                                    ->default(true),
                                Select::make('hero_style')
                                    ->options([
                                        'gradient' => 'Animated Gradient (Modern)',
                                        'image' => 'Background Image',
                                        'minimal' => 'Minimal Clean',
                                        'immersive' => 'Full Immersive',
                                    ])
                                    ->default('gradient'),
                                TextInput::make('hero_title'),
                                TextInput::make('hero_subtitle'),
                                FileUpload::make('featured_image')
                                    ->image()
                                    ->maxSize(5120)
                                    ->directory('pages/featured')
                                    ->disk('public'),
                            ]),
                        Tab::make('Design')
                            ->icon('heroicon-o-paint-brush')
                            ->schema([
                                Select::make('template')
                                    ->options([
                                        'default' => 'Default',
                                        'home' => 'Home (Bento Grid)',
                                        'about' => 'About (Wide)',
                                        'contact' => 'Contact + Form',
                                        'form' => 'Form Page',
                                        'full-width' => 'Full Width',
                                    ])
                                    ->default('default')
                                    ->required(),
                                Select::make('layout_variant')
                                    ->options([
                                        'standard' => 'Standard',
                                        'bento' => 'Bento Grid',
                                        'minimal' => 'Minimal',
                                        'immersive' => 'Immersive',
                                    ])
                                    ->default('standard'),
                                Select::make('accent_color')
                                    ->options([
                                        'gold' => 'Gold',
                                        'royal' => 'Royal Blue',
                                        'navy' => 'Deep Navy',
                                        'emerald' => 'Emerald',
                                    ])
                                    ->default('gold'),
                                Textarea::make('custom_css')
                                    ->label('Custom CSS')
                                    ->rows(4)
                                    ->helperText('Page-specific styles. Avoid script tags.'),
                                Textarea::make('custom_js')
                                    ->label('Custom JS')
                                    ->rows(4)
                                    ->helperText('Page-specific scripts. Use with caution.'),
                            ]),
                        Tab::make('SEO')
                            ->icon('heroicon-o-magnifying-glass')
                            ->schema([
                                TextInput::make('seo_title'),
                                Textarea::make('seo_description')
                                    ->columnSpanFull(),
                                TextInput::make('meta_robots')
                                    ->placeholder('index, follow')
                                    ->helperText('Leave blank for default indexing'),
                                FileUpload::make('og_image')
                                    ->image()
                                    ->directory('pages/og')
                                    ->disk('public'),
                            ]),
                        Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                Select::make('status')
                                    ->options(PublishStatus::class)
                                    ->default('draft')
                                    ->required(),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Toggle::make('is_home')
                                    ->helperText('Only one page should be marked as the homepage.'),
                                Section::make('Content Blocks')
                                    ->description('Add flexible sections on the Content Blocks tab after saving.')
                                    ->schema([]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}
