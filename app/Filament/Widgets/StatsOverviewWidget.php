<?php

namespace App\Filament\Widgets;

use App\Filament\Pages\ContentApprovals;
use App\Filament\Resources\Conversations\ConversationResource;
use App\Filament\Resources\Events\EventResource;
use App\Filament\Resources\News\NewsResource;
use App\Filament\Resources\Pages\PageResource;
use App\Models\Conversation;
use App\Models\Event;
use App\Models\News;
use App\Models\Page;
use App\Services\ContentApprovalService;
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
            'pending_approvals' => app(ContentApprovalService::class)->pendingCount(),
        ]);

        $stats = [
            Stat::make('Pages', $counts['pages'])
                ->description('Website pages live on the site')
                ->icon('heroicon-o-document-text')
                ->url(PageResource::canViewAny() ? PageResource::getUrl('index') : null),
            Stat::make('Events', $counts['events'])
                ->description('Upcoming parish gatherings')
                ->icon('heroicon-o-calendar-days')
                ->url(EventResource::canViewAny() ? EventResource::getUrl('index') : null),
            Stat::make('News', $counts['news'])
                ->description('News articles published')
                ->icon('heroicon-o-newspaper')
                ->url(NewsResource::canViewAny() ? NewsResource::getUrl('index') : null),
            Stat::make('Unread inbox', $counts['unread'])
                ->description('Prayer requests, contact, forms')
                ->icon('heroicon-o-inbox')
                ->color('warning')
                ->url(ConversationResource::canViewAny() ? ConversationResource::getUrl('index') : null),
        ];

        if (ContentApprovals::canAccess() && $counts['pending_approvals'] > 0) {
            $stats[] = Stat::make('Awaiting review', $counts['pending_approvals'])
                ->description('Content waiting for approval')
                ->icon('heroicon-o-clipboard-document-check')
                ->color('warning')
                ->url(ContentApprovals::getUrl());
        }

        return $stats;
    }
}
