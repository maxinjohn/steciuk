<?php

namespace App\Models;

use App\Enums\ConversationStatus;
use App\Enums\FormType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Conversation extends Model
{
    protected $fillable = [
        'user_id',
        'form_submission_id',
        'guest_name',
        'guest_email',
        'subject',
        'source',
        'status',
        'unread_by_admin',
        'unread_by_member',
    ];

    protected function casts(): array
    {
        return [
            'status' => ConversationStatus::class,
            'unread_by_admin' => 'boolean',
            'unread_by_member' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function formSubmission(): BelongsTo
    {
        return $this->belongsTo(FormSubmission::class);
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class)->orderBy('created_at');
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    public function participantName(): string
    {
        if ($this->user) {
            return $this->user->displayFullName();
        }

        return (string) ($this->guest_name ?: 'Guest');
    }

    public function participantEmail(): ?string
    {
        return $this->user?->email ?: $this->guest_email;
    }

    public function sourceLabel(): string
    {
        $formType = FormType::tryFrom($this->source);

        return $formType
            ? str($formType->value)->headline()->toString()
            : str($this->source)->headline()->toString();
    }

    public function memberPortalUrl(): string
    {
        return url('/account?tab=messages&conversation='.$this->id);
    }

    public function adminUrl(): string
    {
        return \App\Support\AdminPanelConfig::url('conversations/'.$this->id);
    }
}
