<?php

namespace App\Filament\Resources\Events\Schemas;

use App\Filament\Support\ChurchRichEditor;
use App\Filament\Support\PublishStatusSelect;
use App\Filament\Support\SecureFileUpload;
use App\Rules\SafeHttpUrl;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class EventForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('title')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                ChurchRichEditor::make('description'),
                SecureFileUpload::image('featured_image', 'events/featured'),
                DateTimePicker::make('starts_at')
                    ->required(),
                DateTimePicker::make('ends_at'),
                TextInput::make('location')
                    ->required(),
                TextInput::make('address'),
                Toggle::make('registration_required'),
                TextInput::make('registration_link')
                    ->url()
                    ->rules([new SafeHttpUrl()]),
                TextInput::make('category'),
                PublishStatusSelect::make(),
                Textarea::make('repeat_rule')
                    ->columnSpanFull(),
            ]);
    }
}
