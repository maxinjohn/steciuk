<?php

namespace App\Filament\Pages;

use App\Enums\AdminNavigationGroup;
use App\Models\User;
use App\Services\ContentApprovalService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Collection;

class ContentApprovals extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentCheck;

    protected static string|\UnitEnum|null $navigationGroup = AdminNavigationGroup::Website;

    protected static ?string $navigationLabel = 'Content approvals';

    protected static ?int $navigationSort = 0;

    protected static ?string $title = 'Content awaiting review';

    protected static ?string $slug = 'content-approvals';

    protected string $view = 'filament.pages.content-approvals';

    public static function canAccess(): bool
    {
        return auth()->user()?->canPublishContent() ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        $count = app(ContentApprovalService::class)->pendingCount();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    /**
     * @return Collection<int, array<string, mixed>>
     */
    public function getPendingItemsProperty(): Collection
    {
        return app(ContentApprovalService::class)->pendingItems();
    }

    public function approve(string $modelClass, int $modelId): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return;
        }

        app(ContentApprovalService::class)->approve($modelClass, $modelId, $actor);

        Notification::make()
            ->title('Approved and published')
            ->success()
            ->send();
    }

    public function returnToDraft(string $modelClass, int $modelId): void
    {
        $actor = auth()->user();

        if (! $actor instanceof User) {
            return;
        }

        app(ContentApprovalService::class)->returnToDraft($modelClass, $modelId, $actor);

        Notification::make()
            ->title('Returned to draft')
            ->success()
            ->send();
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('refresh')
                ->label('Refresh list')
                ->icon('heroicon-o-arrow-path')
                ->action(fn () => null),
        ];
    }
}
