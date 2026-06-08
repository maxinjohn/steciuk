<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\FormSubmission;
use App\Models\News;
use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make('Pages', Page::query()->count())
                ->description('Total pages')
                ->icon('heroicon-o-document-text'),
            Stat::make('Events', Event::query()->count())
                ->description('Total events')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('News', News::query()->count())
                ->description('News articles')
                ->icon('heroicon-o-newspaper'),
            Stat::make('Unread submissions', FormSubmission::query()->where('is_read', false)->count())
                ->description('Form submissions')
                ->icon('heroicon-o-inbox')
                ->color('warning'),
        ];
    }
}
