<?php

namespace App\Services;

use App\Enums\ConversationStatus;
use App\Enums\FormType;
use App\Enums\MessageSenderType;
use App\Models\Conversation;
use App\Models\FormSubmission;
use App\Models\Message;
use App\Models\Setting;
use App\Models\User;
use App\Support\AdminPanelConfig;
use Illuminate\Support\Facades\Auth;

class ParishConversationService
{
    public function startFromFormSubmission(
        FormSubmission $submission,
        FormType $formType,
        array $data,
        ?User $user = null,
    ): Conversation {
        $name = trim((string) ($data['name'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));
        $messageBody = trim((string) ($data['message'] ?? ''));

        if ($messageBody === '') {
            $messageBody = collect($data)
                ->except(['name', 'email', 'phone', 'website'])
                ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
                ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', (string) $key)).': '.$value)
                ->implode("\n");
        }

        $subject = match ($formType) {
            FormType::Contact => 'Contact message',
            FormType::PrayerRequest => 'Prayer request',
            FormType::NewMember => 'Membership enquiry',
            FormType::EventEnquiry => 'Event enquiry',
            FormType::Volunteer => 'Volunteer enquiry',
        };

        if ($name !== '') {
            $subject .= ' from '.$name;
        }

        $conversation = Conversation::query()->create([
            'user_id' => $user?->id,
            'form_submission_id' => $submission->id,
            'guest_name' => $user ? null : ($name !== '' ? $name : null),
            'guest_email' => $user ? null : ($email !== '' ? $email : null),
            'subject' => $subject,
            'source' => $formType->value,
            'status' => ConversationStatus::Open,
            'unread_by_admin' => true,
            'unread_by_member' => false,
        ]);

        $submission->update(['conversation_id' => $conversation->id]);

        $senderType = $user ? MessageSenderType::Member : MessageSenderType::Guest;

        $this->appendMessage($conversation, $senderType, $messageBody, $user, notifyAdmin: true, notifyMember: true);

        return $conversation;
    }

    public function startMemberMessage(User $member, string $subject, string $body): Conversation
    {
        $conversation = Conversation::query()->create([
            'user_id' => $member->id,
            'subject' => trim($subject),
            'source' => 'member_portal',
            'status' => ConversationStatus::Open,
            'unread_by_admin' => true,
            'unread_by_member' => false,
        ]);

        $this->appendMessage($conversation, MessageSenderType::Member, $body, $member, notifyAdmin: true, notifyMember: false);

        return $conversation;
    }

    public function replyAsAdmin(Conversation $conversation, User $admin, string $body): Message
    {
        return $this->appendMessage(
            $conversation,
            MessageSenderType::Admin,
            $body,
            $admin,
            notifyAdmin: false,
            notifyMember: true,
        );
    }

    public function replyAsMember(Conversation $conversation, User $member, string $body): Message
    {
        abort_unless($conversation->user_id === $member->id, 403);

        return $this->appendMessage(
            $conversation,
            MessageSenderType::Member,
            $body,
            $member,
            notifyAdmin: true,
            notifyMember: false,
        );
    }

    public function markReadByAdmin(Conversation $conversation): void
    {
        $conversation->update(['unread_by_admin' => false]);
    }

    public function markReadByMember(Conversation $conversation): void
    {
        $conversation->update(['unread_by_member' => false]);
    }

    private function appendMessage(
        Conversation $conversation,
        MessageSenderType $senderType,
        string $body,
        ?User $sender,
        bool $notifyAdmin,
        bool $notifyMember,
    ): Message {
        $body = trim($body);

        $message = Message::query()->create([
            'conversation_id' => $conversation->id,
            'sender_type' => $senderType,
            'sender_user_id' => $sender?->id,
            'body' => $body,
        ]);

        $conversation->update([
            'unread_by_admin' => $senderType !== MessageSenderType::Admin,
            'unread_by_member' => $senderType === MessageSenderType::Admin,
            'status' => ConversationStatus::Open,
        ]);

        if ($notifyAdmin) {
            $this->notifyAdminOfMessage($conversation, $message);
        }

        if ($notifyMember) {
            if ($senderType === MessageSenderType::Admin) {
                $this->notifyMemberOfReply($conversation, $message);
            } else {
                $this->notifyMemberOfConfirmation($conversation, $message);
            }
        }

        return $message;
    }

    private function notifyAdminOfMessage(Conversation $conversation, Message $message): void
    {
        $email = Setting::get('contact_email') ?: config('site.admin_email') ?: config('mail.from.address');

        if (! $email) {
            return;
        }

        $participant = $conversation->participantName();
        $subject = 'Inbox: '.$conversation->subject;
        $body = implode("\n", array_filter([
            'New message on steciuk.org',
            '',
            'From: '.$participant.($conversation->participantEmail() ? ' <'.$conversation->participantEmail().'>' : ''),
            'Subject: '.$conversation->subject,
            'Source: '.$conversation->sourceLabel(),
            '',
            $message->body,
            '',
            'Open in admin: '.$conversation->adminUrl(),
            '',
            'Reply from the admin inbox so the member can continue the conversation in their portal.',
        ]));

        try {
            MailConfigService::applyFromSettings();
            MailConfigService::deliverPlainTextMessage(
                $email,
                $subject,
                $body,
                replyToAddress: $conversation->participantEmail(),
                replyToName: $participant,
            );
        } catch (\Throwable) {
            // Mail failure must not block messaging
        }
    }

    private function notifyMemberOfConfirmation(Conversation $conversation, Message $message): void
    {
        $recipient = $conversation->participantEmail();

        if (! $recipient) {
            return;
        }

        $officeEmail = Setting::get('contact_email') ?: config('site.admin_email');
        $siteName = Setting::get('church_name') ?: config('app.name', 'STECI UK Parish');

        $lines = [
            'Hello '.$conversation->participantName().',',
            '',
            'Thank you — we received your message and added it to the parish inbox.',
            '',
            'Subject: '.$conversation->subject,
            '',
            $message->body,
        ];

        if ($conversation->user_id) {
            $lines[] = '';
            $lines[] = 'You can follow replies in your member portal:';
            $lines[] = $conversation->memberPortalUrl();
        } else {
            $lines[] = '';
            $lines[] = 'The parish office will respond by email. To reply, use the contact form on our website or email the parish office directly.';
        }

        $lines[] = '';
        $lines[] = $siteName;

        $this->deliverMemberMail(
            $recipient,
            'We received your message — '.$siteName,
            implode("\n", $lines),
            $officeEmail,
            $siteName,
        );
    }

    private function notifyMemberOfReply(Conversation $conversation, Message $message): void
    {
        $recipient = $conversation->participantEmail();

        if (! $recipient) {
            return;
        }

        $officeEmail = Setting::get('contact_email') ?: config('site.admin_email');
        $siteName = Setting::get('church_name') ?: config('app.name', 'STECI UK Parish');

        $lines = [
            'Hello '.$conversation->participantName().',',
            '',
            'The parish office has replied to your message:',
            '',
            $message->body,
        ];

        if ($conversation->user_id) {
            $lines[] = '';
            $lines[] = 'You can read and reply in your member portal:';
            $lines[] = $conversation->memberPortalUrl();
        } else {
            $lines[] = '';
            $lines[] = 'To continue this conversation, please use the contact form on our website or email the parish office directly.';
        }

        $lines[] = '';
        $lines[] = $siteName;

        $this->deliverMemberMail(
            $recipient,
            'Reply from '.$siteName.': '.$conversation->subject,
            implode("\n", $lines),
            $officeEmail,
            $siteName,
        );
    }

    private function deliverMemberMail(
        string $recipient,
        string $subject,
        string $body,
        ?string $officeEmail,
        string $siteName,
    ): void {
        try {
            MailConfigService::applyFromSettings();
            MailConfigService::deliverPlainTextMessage(
                $recipient,
                $subject,
                $body,
                replyToAddress: $officeEmail,
                replyToName: $siteName,
            );
        } catch (\Throwable) {
            // Mail failure must not block messaging
        }
    }

    public function currentMember(): ?User
    {
        $user = Auth::user();

        return $user instanceof User ? $user : null;
    }
}
