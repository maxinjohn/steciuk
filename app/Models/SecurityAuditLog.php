<?php

namespace App\Models;

use App\Support\SecurityAuditCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SecurityAuditLog extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'actor_name',
        'actor_email',
        'actor_role',
        'action',
        'subject_type',
        'subject_id',
        'subject_label',
        'summary',
        'ip_address',
        'user_agent',
        'severity',
        'metadata',
        'created_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'created_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function actionLabel(): string
    {
        return SecurityAuditCatalog::label((string) $this->action);
    }

    public function actorDisplayName(): string
    {
        if (filled($this->actor_name)) {
            $email = filled($this->actor_email) ? ' · '.$this->actor_email : '';

            return $this->actor_name.$email;
        }

        return $this->user?->displayFullName() ?? 'System / guest';
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function record(array $payload): void
    {
        static::query()->create([
            'user_id' => $payload['user_id'] ?? null,
            'actor_name' => $payload['actor_name'] ?? null,
            'actor_email' => $payload['actor_email'] ?? null,
            'actor_role' => $payload['actor_role'] ?? null,
            'action' => $payload['action'],
            'subject_type' => $payload['subject_type'] ?? null,
            'subject_id' => $payload['subject_id'] ?? null,
            'subject_label' => $payload['subject_label'] ?? null,
            'summary' => $payload['summary'] ?? null,
            'ip_address' => request()?->ip(),
            'user_agent' => request()?->userAgent(),
            'severity' => $payload['severity'] ?? 'info',
            'metadata' => $payload['metadata'] ?? null,
            'created_at' => now(),
        ]);
    }
}
