<?php

namespace App\Filament\Support;

use App\Enums\ContentBlockType;
use App\Models\ContentBlock;
use Closure;
use Filament\Forms\Components\Component;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Utilities\Get;
use Illuminate\Database\Eloquent\Model;

class ContentBlockFormBuilder
{
    /**
     * @return list<Component>
     */
    public static function fields(): array
    {
        return [
            Select::make('type')
                ->options(collect(ContentBlockType::cases())->mapWithKeys(
                    fn (ContentBlockType $type) => [$type->value => $type->label()],
                )->all())
                ->required()
                ->live()
                ->afterStateHydrated(function (Select $component): void {
                    $component->partiallyRender();
                }),
            TextInput::make('title')
                ->helperText('Internal label shown in admin lists.'),
            ...self::contentFields(),
            TextInput::make('sort_order')
                ->numeric()
                ->default(0)
                ->required(),
            Toggle::make('is_visible')
                ->default(true)
                ->required(),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function contentFields(): array
    {
        return [
            ...self::heroFields(),
            ...self::headingFields(),
            ...self::bodyFields(),
            ...self::mediaFields(),
            ...self::linkFields(),
            ...self::ctaFields(),
            ...self::listFields(),
            ...self::quoteFields(),
            ...self::faqFields(),
            ...self::locationFields(),
            ...self::embedFields(),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function heroFields(): array
    {
        return [
            TextInput::make('content.eyebrow')
                ->label('Eyebrow')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.headline')
                ->label('Headline')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.subtitle')
                ->label('Subtitle')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.badge')
                ->default('UK Parish')
                ->visible(self::whenType(ContentBlockType::Hero)),
            SecureFileUpload::image('content.image', 'blocks/hero')
                ->visible(self::whenType(ContentBlockType::Hero)),
            Repeater::make('content.stats')
                ->label('Highlight stats')
                ->schema([
                    TextInput::make('value')->required(),
                    TextInput::make('label')->required(),
                ])
                ->columns(2)
                ->collapsible()
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.primary_cta_label')
                ->label('Primary button label')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.primary_cta_url')
                ->label('Primary button URL')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.secondary_cta_label')
                ->label('Secondary button label')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.secondary_cta_url')
                ->label('Secondary button URL')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.tertiary_cta_label')
                ->label('Third button label')
                ->visible(self::whenType(ContentBlockType::Hero)),
            TextInput::make('content.tertiary_cta_url')
                ->label('Third button URL')
                ->visible(self::whenType(ContentBlockType::Hero)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function headingFields(): array
    {
        $withHeading = [
            ContentBlockType::TextImage,
            ContentBlockType::ImageText,
            ContentBlockType::Cta,
            ContentBlockType::MinistryCards,
            ContentBlockType::EventList,
            ContentBlockType::NewsList,
            ContentBlockType::SermonList,
            ContentBlockType::Gallery,
            ContentBlockType::Faq,
            ContentBlockType::Location,
            ContentBlockType::Youtube,
            ContentBlockType::Map,
            ContentBlockType::Contact,
            ContentBlockType::Downloads,
        ];

        $withSubheading = [
            ContentBlockType::TextImage,
            ContentBlockType::ImageText,
            ContentBlockType::MinistryCards,
            ContentBlockType::EventList,
            ContentBlockType::NewsList,
            ContentBlockType::SermonList,
            ContentBlockType::Gallery,
            ContentBlockType::Location,
            ContentBlockType::Youtube,
        ];

        return [
            TextInput::make('content.heading')
                ->label('Section heading')
                ->visible(self::whenType(...$withHeading)),
            TextInput::make('content.subheading')
                ->label('Section subheading')
                ->visible(self::whenType(...$withSubheading)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function bodyFields(): array
    {
        return [
            ChurchRichEditor::make('content.body')
                ->label('Body')
                ->visible(self::whenType(
                    ContentBlockType::TextImage,
                    ContentBlockType::ImageText,
                    ContentBlockType::Cta,
                )),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function mediaFields(): array
    {
        return [
            SecureFileUpload::image('content.image', 'blocks/media')
                ->visible(self::whenType(
                    ContentBlockType::TextImage,
                    ContentBlockType::ImageText,
                )),
            TextInput::make('content.image_alt')
                ->label('Image alt text')
                ->visible(self::whenType(
                    ContentBlockType::TextImage,
                    ContentBlockType::ImageText,
                )),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function linkFields(): array
    {
        $withLink = [
            ContentBlockType::TextImage,
            ContentBlockType::ImageText,
            ContentBlockType::MinistryCards,
            ContentBlockType::EventList,
            ContentBlockType::NewsList,
            ContentBlockType::SermonList,
            ContentBlockType::Gallery,
            ContentBlockType::Quote,
            ContentBlockType::Location,
            ContentBlockType::Downloads,
        ];

        return [
            TextInput::make('content.link_label')
                ->label('Link label')
                ->visible(self::whenType(...$withLink)),
            TextInput::make('content.link_url')
                ->label('Link URL')
                ->visible(self::whenType(...$withLink)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function ctaFields(): array
    {
        return [
            Select::make('content.style')
                ->options([
                    'primary' => 'Primary (navy gradient)',
                    'secondary' => 'Secondary (card)',
                ])
                ->default('primary')
                ->visible(self::whenType(ContentBlockType::Cta)),
            TextInput::make('content.button_label')
                ->label('Button label')
                ->visible(self::whenType(ContentBlockType::Cta)),
            TextInput::make('content.button_url')
                ->label('Button URL')
                ->visible(self::whenType(ContentBlockType::Cta)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function listFields(): array
    {
        $withLimit = [
            ContentBlockType::MinistryCards,
            ContentBlockType::EventList,
            ContentBlockType::NewsList,
            ContentBlockType::SermonList,
            ContentBlockType::Gallery,
        ];

        return [
            TextInput::make('content.limit')
                ->numeric()
                ->default(4)
                ->label('Number of items to show')
                ->visible(self::whenType(...$withLimit)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function quoteFields(): array
    {
        return [
            Textarea::make('content.quote')
                ->rows(3)
                ->visible(self::whenType(ContentBlockType::Quote)),
            TextInput::make('content.attribution')
                ->visible(self::whenType(ContentBlockType::Quote)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function faqFields(): array
    {
        return [
            Repeater::make('content.items')
                ->label('FAQ items')
                ->schema([
                    TextInput::make('question')->required(),
                    ChurchRichEditor::make('answer')->label('Answer'),
                ])
                ->columnSpanFull()
                ->collapsible()
                ->visible(self::whenType(ContentBlockType::Faq)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function locationFields(): array
    {
        return [
            TagsInput::make('content.locations')
                ->label('City names')
                ->helperText('Must match location names on Service Times (e.g. Manchester, Leicester).')
                ->visible(self::whenType(ContentBlockType::Location)),
        ];
    }

    /**
     * @return list<Component>
     */
    private static function embedFields(): array
    {
        return [
            TextInput::make('content.url')
                ->label('YouTube URL')
                ->url()
                ->visible(self::whenType(ContentBlockType::Youtube)),
            Textarea::make('content.embed')
                ->label('Map embed HTML')
                ->rows(4)
                ->helperText('Paste a Google Maps iframe embed code.')
                ->visible(self::whenType(ContentBlockType::Map)),
        ];
    }

    private static function whenType(ContentBlockType ...$types): Closure
    {
        return fn (Get $get, ?Model $record = null): bool => self::typeIs($get, $record, ...$types);
    }

    private static function typeIs(Get $get, ?Model $record, ContentBlockType ...$types): bool
    {
        $allowed = array_map(fn (ContentBlockType $type) => $type->value, $types);

        if ($record instanceof ContentBlock) {
            $recordType = $record->type instanceof ContentBlockType
                ? $record->type->value
                : (string) $record->type;

            if ($recordType !== '' && in_array($recordType, $allowed, true)) {
                return true;
            }
        }

        $current = self::resolveType($get);

        if ($current === null) {
            return false;
        }

        return in_array($current, $allowed, true);
    }

    private static function resolveType(Get $get): ?string
    {
        foreach (['type', '../type', '../../type'] as $path) {
            $current = $get($path);

            if ($current instanceof ContentBlockType) {
                return $current->value;
            }

            if (is_string($current) && $current !== '') {
                return $current;
            }
        }

        return null;
    }
}
