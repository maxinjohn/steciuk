<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class UpcomingEventsWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading('Upcoming Events')
            ->query(
                Event::query()
                    ->where('starts_at', '>=', now())
                    ->orderBy('starts_at')
                    ->limit(5),
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('starts_at')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('location'),
                TextColumn::make('status')
                    ->badge(),
            ])
            ->paginated(false);
    }
}
