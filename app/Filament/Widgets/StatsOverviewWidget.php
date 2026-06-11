<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use App\Models\Conversation;
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
                ->description('Website pages live on the site')
                ->icon('heroicon-o-document-text'),
            Stat::make('Events', Event::query()->count())
                ->description('Upcoming parish gatherings')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('News', News::query()->count())
                ->description('News articles published')
                ->icon('heroicon-o-newspaper'),
            Stat::make('Unread inbox', Conversation::query()->where('unread_by_admin', true)->count())
                ->description('Prayer requests, contact, forms')
                ->icon('heroicon-o-inbox')
                ->color('warning'),
        ];
    }
}
