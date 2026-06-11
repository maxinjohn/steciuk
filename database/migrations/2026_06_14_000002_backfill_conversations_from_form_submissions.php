<?php

use App\Enums\MessageSenderType;
use App\Models\Conversation;
use App\Models\FormSubmission;
use App\Models\Message;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('conversations') || ! Schema::hasTable('form_submissions')) {
            return;
        }

        FormSubmission::query()
            ->whereNull('conversation_id')
            ->orderBy('id')
            ->each(function (FormSubmission $submission): void {
                $data = $submission->normalizedData();
                $name = trim((string) ($data['name'] ?? ''));
                $email = trim((string) ($data['email'] ?? ''));
                $body = trim((string) ($data['message'] ?? ''));

                if ($body === '') {
                    $body = collect($data)
                        ->filter(fn ($value) => is_scalar($value) && trim((string) $value) !== '')
                        ->map(fn ($value, $key) => ucfirst(str_replace('_', ' ', (string) $key)).': '.$value)
                        ->implode("\n");
                }

                $subject = str($submission->form_type->value)->headline()->toString();
                if ($name !== '') {
                    $subject .= ' from '.$name;
                }

                $conversation = Conversation::query()->create([
                    'form_submission_id' => $submission->id,
                    'guest_name' => $name !== '' ? $name : null,
                    'guest_email' => $email !== '' ? $email : null,
                    'subject' => $subject,
                    'source' => $submission->form_type->value,
                    'status' => 'open',
                    'unread_by_admin' => ! $submission->is_read,
                    'unread_by_member' => false,
                    'created_at' => $submission->created_at,
                    'updated_at' => $submission->updated_at,
                ]);

                Message::query()->create([
                    'conversation_id' => $conversation->id,
                    'sender_type' => MessageSenderType::Guest,
                    'body' => $body !== '' ? $body : '(No message body recorded)',
                    'created_at' => $submission->created_at,
                    'updated_at' => $submission->updated_at,
                ]);

                $submission->update(['conversation_id' => $conversation->id]);
            });
    }

    public function down(): void
    {
        // Backfilled conversations are not removed automatically.
    }
};
