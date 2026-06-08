<?php

namespace App\Filament\Resources\FormSubmissions\Pages;

use App\Filament\Resources\FormSubmissions\FormSubmissionResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;

class ViewFormSubmission extends ViewRecord
{
    protected static string $resource = FormSubmissionResource::class;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if (! $this->record->is_read) {
            $this->record->update(['is_read' => true]);
        }
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('markAsRead')
                ->label('Mark as read')
                ->icon('heroicon-o-check')
                ->visible(fn (): bool => ! $this->record->is_read)
                ->action(function (): void {
                    $this->record->update(['is_read' => true]);

                    Notification::make()
                        ->success()
                        ->title('Submission marked as read')
                        ->send();
                }),
            Action::make('markAsUnread')
                ->label('Mark as unread')
                ->icon('heroicon-o-envelope')
                ->visible(fn (): bool => $this->record->is_read)
                ->action(function (): void {
                    $this->record->update(['is_read' => false]);

                    Notification::make()
                        ->success()
                        ->title('Submission marked as unread')
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
