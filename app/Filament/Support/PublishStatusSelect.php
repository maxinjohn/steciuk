<?php

namespace App\Filament\Support;

use App\Enums\PublishStatus;
use Filament\Forms\Components\Select;

class PublishStatusSelect
{
    public static function make(string $name = 'status'): Select
    {
        return Select::make($name)
            ->label('Status')
            ->options(fn (): array => PublishStatus::optionsFor())
            ->default(PublishStatus::Draft->value)
            ->required()
            ->helperText(fn (): ?string => auth()->user()?->canPublishContent()
                ? null
                : 'Editors can save drafts or submit for parish review. Publishing is done by an admin.');
    }
}
