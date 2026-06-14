<?php

namespace App\Filament\Resources\Ministries\Schemas;

use App\Filament\Support\ChurchRichEditor;
use App\Filament\Support\MenuPlacementFields;
use App\Filament\Support\SecureFileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class MinistryForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required(),
                TextInput::make('slug')
                    ->required(),
                Textarea::make('short_description')
                    ->columnSpanFull(),
                ChurchRichEditor::make('description'),
                SecureFileUpload::image('featured_image', 'ministries/featured'),
                TextInput::make('contact_person'),
                TextInput::make('contact_email')
                    ->email(),
                TextInput::make('meeting_time'),
                TextInput::make('sort_order')
                    ->numeric()
                    ->default(0)
                    ->required(),
                Select::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                    ])
                    ->default('active')
                    ->required(),
                ...MenuPlacementFields::schema('ministries'),
            ]);
    }
}
