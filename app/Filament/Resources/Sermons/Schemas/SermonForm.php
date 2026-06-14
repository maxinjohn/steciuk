<?php

namespace App\Filament\Resources\Sermons\Schemas;

use App\Filament\Support\PublishStatusSelect;
use App\Filament\Support\SecureFileUpload;
use Filament\Forms\Components\DatePicker;
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
                SecureFileUpload::file('audio_file', 'sermons/audio', 51200, ['audio/mpeg', 'audio/mp3', 'audio/wav', 'audio/x-wav'])
                    ->label('Audio'),
                SecureFileUpload::file('pdf_file', 'sermons/pdf', 10240, ['application/pdf'])
                    ->label('PDF notes'),
                TextInput::make('youtube_url')
                    ->url(),
                TextInput::make('category'),
                PublishStatusSelect::make(),
            ]);
    }
}
