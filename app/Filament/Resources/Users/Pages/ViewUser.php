<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use App\Filament\Support\AdminUserPasswordActions;
use App\Models\User;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewUser extends ViewRecord
{
    protected static string $resource = UserResource::class;

    public function getTitle(): string
    {
        /** @var User $record */
        $record = $this->getRecord();

        return trim($record->displayFirstName().' '.$record->displayLastName()) ?: ($record->name ?: 'Member summary');
    }

    protected function getHeaderActions(): array
    {
        /** @var User $record */
        $record = $this->getRecord();

        return [
            AdminUserPasswordActions::setPasswordPageAction($record),
            AdminUserPasswordActions::sendResetLinkPageAction($record),
            EditAction::make(),
        ];
    }
}
