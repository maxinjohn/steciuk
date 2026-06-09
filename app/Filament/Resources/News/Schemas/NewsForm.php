<?php

namespace App\Filament\Resources\News\Schemas;

use App\Enums\PublishStatus;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Support\ChurchRichEditor;
use App\Filament\Support\SecureFileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class NewsForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('excerpt')
                    ->columnSpanFull(),
                ChurchRichEditor::make('content'),
                SecureFileUpload::image('featured_image', 'news/featured'),
                TextInput::make('category'),
                DateTimePicker::make('published_at'),
                Select::make('status')
                    ->options(PublishStatus::class)
                    ->default('draft')
                    ->required(),
                TextInput::make('seo_title'),
                Textarea::make('seo_description')
                    ->columnSpanFull(),
            ]);
    }
}
