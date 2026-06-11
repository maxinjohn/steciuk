<?php

namespace App\Filament\Resources\Sermons\Pages;

use App\Filament\Resources\Sermons\SermonResource;
use App\Filament\Support\PublishWorkflowActions;
use App\Models\Sermon;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditSermon extends EditRecord
{
    protected static string $resource = SermonResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ...PublishWorkflowActions::headerActions(
                fn (): Sermon => $this->getRecord(),
                fn (Sermon $sermon): string => route('sermons.index').'#sermon-'.$sermon->id,
            ),
            RestoreAction::make()
                ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false),
            ForceDeleteAction::make()
                ->visible(fn (): bool => auth()->user()?->isSuperAdmin() ?? false),
        ];
    }
}
