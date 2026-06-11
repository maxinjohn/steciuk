<?php

namespace App\Filament\Resources\FormSubmissions\Schemas;

use App\Models\FormSubmission;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class FormSubmissionInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('form_type')
                    ->badge(),
                IconEntry::make('is_read')
                    ->boolean()
                    ->label('Read'),
                KeyValueEntry::make('data')
                    ->state(fn (FormSubmission $record): array => $record->normalizedData())
                    ->columnSpanFull(),
                TextEntry::make('ip_address'),
                TextEntry::make('user_agent')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime(),
            ]);
    }
}
