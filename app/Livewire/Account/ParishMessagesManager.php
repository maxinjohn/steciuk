<?php

namespace App\Livewire\Account;

use App\Models\Conversation;
use App\Models\User;
use App\Services\ParishConversationService;
use Illuminate\Support\Facades\Auth;
use Livewire\Attributes\Url;
use Livewire\Component;

class ParishMessagesManager extends Component
{
    #[Url(as: 'conversation', except: null)]
    public ?int $selectedConversationId = null;

    public string $newSubject = '';

    public string $newMessage = '';

    public string $replyBody = '';

    public bool $sent = false;

    public function mount(): void
    {
        if ($this->selectedConversationId !== null) {
            $this->selectConversation($this->selectedConversationId);
        }
    }

    public function selectConversation(int $conversationId): void
    {
        $conversation = $this->memberConversationQuery()->findOrFail($conversationId);

        $this->selectedConversationId = $conversation->id;
        $this->replyBody = '';
        $this->sent = false;

        app(ParishConversationService::class)->markReadByMember($conversation);

        $this->dispatch('conversation-selected');
    }

    public function startConversation(ParishConversationService $conversationService): void
    {
        $member = $this->member();

        $this->validate([
            'newSubject' => 'required|string|max:160',
            'newMessage' => 'required|string|max:5000',
        ]);

        $conversation = $conversationService->startMemberMessage(
            $member,
            $this->newSubject,
            $this->newMessage,
        );

        $this->reset(['newSubject', 'newMessage']);
        $this->selectConversation($conversation->id);
        $this->sent = true;
    }

    public function sendReply(ParishConversationService $conversationService): void
    {
        $member = $this->member();

        $this->validate([
            'replyBody' => 'required|string|max:5000',
        ]);

        $conversation = $this->memberConversationQuery()->findOrFail((int) $this->selectedConversationId);

        $conversationService->replyAsMember($conversation, $member, $this->replyBody);

        $this->reset('replyBody');
        $this->sent = true;
    }

    public function render()
    {
        $member = $this->member();
        $conversations = $this->memberConversationQuery()
            ->with(['latestMessage'])
            ->orderByDesc('updated_at')
            ->get();

        $activeConversation = $this->selectedConversationId
            ? $this->memberConversationQuery()
                ->with(['messages.sender'])
                ->find($this->selectedConversationId)
            : null;

        if ($activeConversation) {
            app(ParishConversationService::class)->markReadByMember($activeConversation);
        }

        return view('livewire.account.parish-messages-manager', [
            'conversations' => $conversations,
            'activeConversation' => $activeConversation,
            'unreadCount' => $conversations->where('unread_by_member', true)->count(),
        ]);
    }

    private function member(): User
    {
        $member = Auth::user();

        abort_unless($member instanceof User, 403);

        return $member;
    }

    private function memberConversationQuery()
    {
        return Conversation::query()->where('user_id', $this->member()->id);
    }
}
