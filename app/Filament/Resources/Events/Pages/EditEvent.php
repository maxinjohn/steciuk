<?php

namespace App\Filament\Resources\Events\Pages;

use App\Filament\Resources\Events\EventResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\Event;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditEvent extends EditRecord
{
    protected static string $resource = EventResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...PublishWorkflowActions::headerActions(
                fn (): Event => $this->getRecord(),
                fn (Event $event): string => route('events.show', $event->slug),
            ),
            RestoreAction::make()
                ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
            ForceDeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
        ];
    }
}
