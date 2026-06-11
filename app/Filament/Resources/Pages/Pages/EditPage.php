<?php

namespace App\Filament\Resources\Pages\Pages;

use App\Filament\Resources\Pages\PageResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\Page;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Contracts\Support\Htmlable;

class EditPage extends EditRecord
{
    protected static string $resource = PageResource::class;

    public function getSubheading(): string|Htmlable|null
    {
        /** @var Page $page */
        $page = $this->getRecord();
        $sections = $page->contentBlocks()->count();

        if ($sections > 0) {
            return "{$sections} page section(s) — scroll down to Page Sections and click Edit on each block.";
        }

        if ($page->is_home || $page->template === 'home') {
            return 'Homepage content lives in Page Sections below — the Content tab body field is optional.';
        }

        return 'Use Page Sections below for layout blocks, or the Content tab for rich body text.';
    }

    protected function getHeaderActions(): array
    {
        return [
            ...PublishWorkflowActions::headerActions(
                fn (): Page => $this->getRecord(),
                fn (Page $page): string => $page->publicUrl(),
            ),
            ForceDeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
            RestoreAction::make()
                ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
        ];
    }
}
