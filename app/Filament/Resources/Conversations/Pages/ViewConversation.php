<?php

namespace App\Filament\Resources\Conversations\Pages;

use App\Filament\Resources\Conversations\ConversationResource;
use App\Models\Conversation;
use App\Models\User;
use App\Services\ParishConversationService;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Illuminate\Contracts\Support\Htmlable;

class ViewConversation extends ViewRecord
{
    protected static string $resource = ConversationResource::class;

    protected string $view = 'filament.resources.conversations.view-conversation';

    public function mount(int | string $record): void
    {
        parent::mount($record);

        if ($this->record instanceof Conversation && $this->record->unread_by_admin) {
            app(ParishConversationService::class)->markReadByAdmin($this->record);
            $this->record->refresh();
        }
    }

    public function getTitle(): string | Htmlable
    {
        return (string) $this->record->subject;
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('reply')
                ->label('Reply')
                ->icon('heroicon-o-paper-airplane')
                ->form([
                    Textarea::make('body')
                        ->label('Reply to '.$this->record->participantName())
                        ->required()
                        ->rows(6)
                        ->maxLength(5000),
                ])
                ->action(function (array $data): void {
                    $admin = auth()->user();

                    abort_unless($admin instanceof User, 403);

                    app(ParishConversationService::class)->replyAsAdmin(
                        $this->record,
                        $admin,
                        (string) $data['body'],
                    );

                    $this->record->refresh();

                    Notification::make()
                        ->success()
                        ->title('Reply sent')
                        ->body('The member was notified by email and can reply in their portal.')
                        ->send();
                }),
            DeleteAction::make(),
        ];
    }
}
