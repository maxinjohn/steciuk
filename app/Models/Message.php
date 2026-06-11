<?php

namespace App\Models;

use App\Enums\MessageSenderType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Message extends Model
{
    protected $fillable = [
        'conversation_id',
        'sender_type',
        'sender_user_id',
        'body',
    ];

    protected function casts(): array
    {
        return [
            'sender_type' => MessageSenderType::class,
        ];
    }

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_user_id');
    }

    public function senderLabel(): string
    {
        if ($this->sender_type === MessageSenderType::Admin && $this->sender) {
            return $this->sender->displayFullName();
        }

        return $this->sender_type->label();
    }
}
