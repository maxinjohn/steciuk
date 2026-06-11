<?php

namespace App\Filament\Resources\News\Pages;

use App\Filament\Resources\News\NewsResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\News;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditNews extends EditRecord
{
    protected static string $resource = NewsResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...PublishWorkflowActions::headerActions(
                fn (): News => $this->getRecord(),
                fn (News $news): string => route('news.show', $news->slug),
            ),
            RestoreAction::make()
                ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
            ForceDeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
        ];
    }
}
