<x-filament-panels::page>
    @php
        /** @var \App\Models\Conversation $record */
        $record = $this->record;
        $record->loadMissing(['messages.sender', 'user']);
    @endphp

    <div class="admin-conversation-meta">
        <p><strong>From:</strong> {{ $record->participantName() }}
            @if ($record->participantEmail())
                &lt;{{ $record->participantEmail() }}&gt;
            @endif
        </p>
        <p><strong>Source:</strong> {{ $record->sourceLabel() }}</p>
        <p><strong>Status:</strong> {{ $record->status->label() }}</p>
        @if ($record->user)
            <p><strong>Member portal:</strong> <a href="{{ $record->memberPortalUrl() }}" target="_blank" rel="noopener">Open member thread</a></p>
        @endif
    </div>

    <div class="admin-conversation-thread">
        @foreach ($record->messages as $message)
            <article @class([
                'admin-conversation-message',
                'admin-conversation-message--admin' => $message->sender_type->value === 'admin',
            ])>
                <header>
                    <strong>{{ $message->senderLabel() }}</strong>
                    <span>{{ $message->created_at->format('j M Y, g:i a') }}</span>
                </header>
                <div class="admin-conversation-message__body">{!! nl2br(e($message->body)) !!}</div>
            </article>
        @endforeach
    </div>
</x-filament-panels::page>
