<?php

namespace App\Filament\Widgets;

use App\Models\Conversation;
use App\Models\Event;
use App\Models\News;
use App\Models\Page;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Illuminate\Support\Facades\Cache;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected static bool $isLazy = true;

    protected function getStats(): array
    {
        $counts = Cache::remember('admin.dashboard.stats.v1', 60, static fn (): array => [
            'pages' => Page::query()->count(),
            'events' => Event::query()->count(),
            'news' => News::query()->count(),
            'unread' => Conversation::query()->where('unread_by_admin', true)->count(),
        ]);

        return [
            Stat::make('Pages', $counts['pages'])
                ->description('Website pages live on the site')
                ->icon('heroicon-o-document-text'),
            Stat::make('Events', $counts['events'])
                ->description('Upcoming parish gatherings')
                ->icon('heroicon-o-calendar-days'),
            Stat::make('News', $counts['news'])
                ->description('News articles published')
                ->icon('heroicon-o-newspaper'),
            Stat::make('Unread inbox', $counts['unread'])
                ->description('Prayer requests, contact, forms')
                ->icon('heroicon-o-inbox')
                ->color('warning'),
        ];
    }
}
