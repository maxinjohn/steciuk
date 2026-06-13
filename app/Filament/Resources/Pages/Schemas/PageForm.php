<?php

namespace App\Filament\Resources\Pages\Schemas;

use App\Enums\PublishStatus;
use App\Filament\Support\ChurchRichEditor;
use App\Filament\Support\PublishStatusSelect;
use App\Filament\Support\SecureFileUpload;
use App\Models\Page;
use App\Support\PageTopicArt;
use Filament\Forms\Components\Placeholder;
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
                                Placeholder::make('sections_notice')
                                    ->hiddenLabel()
                                    ->content(function (?Page $record, string $operation): string {
                                        if ($operation === 'create') {
                                            return 'After saving, use the Page Sections panel below to add hero banners, location tabs, CTAs, and other visible layout blocks.';
                                        }

                                        if ($record?->contentBlocks()->exists()) {
                                            return 'This page is built from '.$record->contentBlocks()->count().' section(s). Edit each section in the Page Sections panel below — headlines, buttons, stats, and quotes live there.';
                                        }

                                        if ($record?->is_home || $record?->template === 'home') {
                                            return 'The homepage uses Page Sections (below), not this body field. Add or edit hero, locations, ministries, and CTAs there.';
                                        }

                                        return 'Use the Page Sections panel below for layout blocks, or this field for rich body text on standard pages.';
                                    })
                                    ->columnSpanFull(),
                                TextInput::make('title')
                                    ->required()
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (Set $set, ?string $state, ?string $operation): void {
                                        if ($operation === 'create') {
                                            $set('slug', Str::slug($state ?? ''));
                                        }
                                    }),
                                TextInput::make('slug')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->helperText('URL path for this page, e.g. welcome → /welcome'),
                                ChurchRichEditor::make('content')
                                    ->helperText(function (?Page $record): string {
                                        if ($record?->is_home || $record?->template === 'home') {
                                            return 'Optional extra prose below page sections. The homepage layout comes from Page Sections below.';
                                        }

                                        if ($record?->contentBlocks()->exists()) {
                                            return 'Optional body text shown below page sections when present.';
                                        }

                                        return 'Full page body — headings, lists, links, tables, and images are preserved on save.';
                                    }),
                            ]),
                        Tab::make('Hero')
                            ->icon('heroicon-o-photo')
                            ->schema([
                                Toggle::make('show_hero')
                                    ->label('Show hero section')
                                    ->default(true)
                                    ->helperText('Disable when the page uses a Hero section block in Page Sections below.'),
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
                                SecureFileUpload::image('featured_image', 'pages/featured'),
                                Placeholder::make('topic_art_preview')
                                    ->label('Auto topic art')
                                    ->content(function (?Page $record, callable $get): string {
                                        $slug = (string) ($get('slug') ?? $record?->slug ?? '');
                                        $title = (string) ($get('hero_title') ?: $get('title') ?: $record?->title ?? '');
                                        $content = (string) ($get('content') ?? $record?->content ?? '');

                                        if ($slug === '' && $title === '') {
                                            return 'Save the page with a title and slug — a matching illustration is chosen automatically from the name and content when no hero photo is uploaded.';
                                        }

                                        $topic = PageTopicArt::resolveTopic(
                                            $slug,
                                            $title,
                                            $record ? PageTopicArt::contextForPage($record) : PageTopicArt::contextForSlug($slug),
                                            $content,
                                        );

                                        $uploaded = PageTopicArt::hasRealFeaturedImage($get('featured_image') ?? $record?->featured_image);

                                        if ($uploaded) {
                                            return 'Hero photo uploaded — that image is shown. Remove it to use auto topic art ('.PageTopicArt::topicLabel($topic).').';
                                        }

                                        return 'No hero photo — visitors will see dynamic '.PageTopicArt::topicLabel($topic).' art generated from this page’s slug, title, and body text.';
                                    })
                                    ->columnSpanFull(),
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
                                    ->required()
                                    ->helperText('Home template expects Page Sections rather than body content.'),
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
                                    ->helperText('CSS only — scripts and @import are stripped on save.'),
                                Textarea::make('custom_js')
                                    ->label('Custom JS (disabled on public site)')
                                    ->rows(4)
                                    ->helperText('Stored for reference only. Public custom JS is blocked for security.')
                                    ->visible(fn (): bool => (bool) config('security.allow_page_custom_js')
                                        && auth()->user()?->hasFullPanelAccess()),
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
                                SecureFileUpload::image('og_image', 'pages/og', 4096),
                            ]),
                        Tab::make('Settings')
                            ->icon('heroicon-o-cog-6-tooth')
                            ->schema([
                                PublishStatusSelect::make(),
                                TextInput::make('sort_order')
                                    ->numeric()
                                    ->default(0)
                                    ->required(),
                                Toggle::make('is_home')
                                    ->helperText('Only one page should be marked as the homepage.'),
                                Section::make('Menus')
                                    ->description('Use the Menu Placement tab below to add this page to header, footer, or mobile menus — including submenus.')
                                    ->schema([]),
                            ]),
                    ])
                    ->columnSpanFull()
                    ->persistTabInQueryString(),
            ]);
    }
}
