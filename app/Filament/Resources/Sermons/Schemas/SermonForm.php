<?php

namespace App\Filament\Resources\Sermons\Schemas;

use App\Enums\PublishStatus;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class SermonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('speaker')
                    ->required(),
                DatePicker::make('preached_at')
                    ->required(),
                TextInput::make('bible_passage'),
                Textarea::make('description')
                    ->columnSpanFull(),
                FileUpload::make('audio_file')
                    ->label('Audio')
                    ->acceptedFileTypes(['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav'])
                    ->directory('sermons/audio')
                    ->disk('public')
                    ->maxSize(51200),
                FileUpload::make('pdf_file')
                    ->label('PDF notes')
                    ->acceptedFileTypes(['application/pdf'])
                    ->directory('sermons/pdf')
                    ->disk('public')
                    ->maxSize(10240),
                TextInput::make('youtube_url')
                    ->url(),
                TextInput::make('category'),
                Select::make('status')
                    ->options(PublishStatus::class)
                    ->default('draft')
                    ->required(),
            ]);
    }
}
