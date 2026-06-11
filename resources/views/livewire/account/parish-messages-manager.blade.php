<div class="member-portal-panel-stack">
    <div class="member-portal-card">
        <div class="member-messages-header">
            <div>
                <h2 class="member-portal-panel-title">Messages to parish</h2>
                <p class="member-portal-panel-intro">
                    Send a message to the parish office and follow replies here. Contact form submissions linked to your account also appear in this inbox.
                </p>
            </div>
            @if ($unreadCount > 0)
                <span class="member-portal-chip">{{ $unreadCount }} unread</span>
            @endif
        </div>
    </div>

    <div
        class="member-messages-layout @if($activeConversation) is-thread-open @endif"
        x-data="{ mobileThreadOpen: @js($activeConversation !== null) }"
        x-bind:class="mobileThreadOpen ? 'is-thread-open' : ''"
        x-on:conversation-selected.window="mobileThreadOpen = true"
    >
        <aside class="member-messages-list" aria-label="Your conversations">
            @forelse ($conversations as $conversation)
                <button
                    type="button"
                    wire:key="conversation-{{ $conversation->id }}"
                    wire:click="selectConversation({{ $conversation->id }})"
                    @class([
                        'member-messages-item',
                        'is-active' => $activeConversation?->id === $conversation->id,
                        'is-unread' => $conversation->unread_by_member,
                    ])
                >
                    <span class="member-messages-item__subject">{{ $conversation->subject }}</span>
                    <span class="member-messages-item__meta">
                        {{ $conversation->sourceLabel() }}
                        · {{ $conversation->updated_at->diffForHumans() }}
                    </span>
                </button>
            @empty
                <p class="member-messages-empty">No messages yet. Start a conversation below.</p>
            @endforelse
        </aside>

        <div class="member-messages-thread">
            @if ($activeConversation)
                <div class="member-portal-card member-messages-thread-card">
                    <div class="member-messages-thread-head">
                        <button
                            type="button"
                            class="member-messages-back lg:hidden"
                            x-on:click="mobileThreadOpen = false"
                        >
                            ← All messages
                        </button>
                        <h3 class="member-messages-thread-title">{{ $activeConversation->subject }}</h3>
                        <p class="member-messages-thread-meta">
                            {{ $activeConversation->sourceLabel() }}
                            · started {{ $activeConversation->created_at->format('j M Y') }}
                        </p>
                    </div>

                    <div class="member-messages-thread-body">
                        @foreach ($activeConversation->messages as $message)
                            <article
                                wire:key="message-{{ $message->id }}"
                                @class([
                                    'member-message',
                                    'member-message--admin' => $message->sender_type->value === 'admin',
                                    'member-message--member' => $message->sender_type->value !== 'admin',
                                ])
                            >
                                <header class="member-message__head">
                                    <strong>{{ $message->senderLabel() }}</strong>
                                    <time datetime="{{ $message->created_at->toIso8601String() }}">
                                        {{ $message->created_at->format('j M Y, g:i a') }}
                                    </time>
                                </header>
                                <div class="member-message__body">{!! nl2br(e($message->body)) !!}</div>
                            </article>
                        @endforeach
                    </div>

                    @if ($sent)
                        <div class="member-alert member-alert--success mt-5" role="status">
                            Message sent. The parish office will be notified by email.
                        </div>
                    @endif

                    <form wire:submit="sendReply" class="member-messages-reply mt-6">
                        <label for="reply-body" class="form-label">Your reply</label>
                        <textarea id="reply-body" wire:model.blur="replyBody" rows="4" class="form-input resize-y" @error('replyBody') aria-invalid="true" @enderror></textarea>
                        @error('replyBody')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                        <div class="mt-4">
                            <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                                <span wire:loading.remove wire:target="sendReply">Send reply</span>
                                <span wire:loading wire:target="sendReply">Sending…</span>
                            </button>
                        </div>
                    </form>
                </div>
            @else
                <div class="member-portal-card member-messages-compose">
                    <h3 class="member-messages-thread-title">New message</h3>
                    <p class="member-portal-panel-intro">Write to the parish office. You will receive an email when we reply, and the conversation stays here in your account.</p>

                    @if ($sent)
                        <div class="member-alert member-alert--success mt-5" role="status">
                            Your message was sent to the parish inbox.
                        </div>
                    @endif

                    <form wire:submit="startConversation" class="mt-6 space-y-5">
                        <div>
                            <label for="new-subject" class="form-label">Subject</label>
                            <input id="new-subject" type="text" wire:model.blur="newSubject" class="form-input" placeholder="e.g. Question about baptism" @error('newSubject') aria-invalid="true" @enderror>
                            @error('newSubject')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="new-message" class="form-label">Message</label>
                            <textarea id="new-message" wire:model.blur="newMessage" rows="6" class="form-input resize-y" @error('newMessage') aria-invalid="true" @enderror></textarea>
                            @error('newMessage')<p class="form-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <button type="submit" class="btn btn-primary sm:w-auto" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="startConversation">Send to parish</span>
                            <span wire:loading wire:target="startConversation">Sending…</span>
                        </button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
