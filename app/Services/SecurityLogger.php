<?php

namespace App\Services;

use App\Models\Donation;
use App\Models\Family;
use App\Models\SecurityAuditLog;
use App\Models\User;
use App\Support\AdminPanelConfig;
use App\Support\SecurityAuditCatalog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class SecurityLogger
{
    public static function info(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        self::audit($action, 'info', actorId: $userId, context: $metadata ?? []);
    }

    public static function warning(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        self::audit($action, 'warning', actorId: $userId, context: $metadata ?? []);
    }

    public static function critical(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        self::audit($action, 'critical', actorId: $userId, context: $metadata ?? [], defer: false);
    }

    /**
     * @param  array<string, mixed>  $context
     */
    public static function audit(
        string $action,
        string $severity = 'info',
        ?User $actor = null,
        ?int $actorId = null,
        ?Model $subject = null,
        ?string $summary = null,
        array $context = [],
        bool $defer = false,
    ): void {
        $resolvedActor = self::resolveActor($actor, $actorId);
        $context = SecurityAuditCatalog::enrichContext($context);

        if ($resolvedActor) {
            $context['actor_name'] ??= $resolvedActor->displayFullName();
            $context['actor_email'] ??= $resolvedActor->email;
            $context['actor_role'] ??= $resolvedActor->roleSlug();
        }

        if ($subject) {
            $context['subject_label'] ??= self::subjectLabel($subject);
            $context['resource'] ??= class_basename($subject);
            $context['resource_id'] ??= $subject->getKey();
        }

        $summary ??= SecurityAuditCatalog::summarize($action, $context);

        $payload = [
            'action' => $action,
            'severity' => $severity,
            'user_id' => $resolvedActor?->id,
            'actor_name' => $context['actor_name'] ?? null,
            'actor_email' => $context['actor_email'] ?? null,
            'actor_role' => $context['actor_role'] ?? null,
            'subject_type' => $subject ? class_basename($subject) : null,
            'subject_id' => $subject?->getKey(),
            'subject_label' => $context['subject_label'] ?? null,
            'summary' => $summary,
            'metadata' => $context !== [] ? $context : null,
        ];

        if ($defer) {
            app()->terminating(static fn () => self::persist($payload));

            return;
        }

        self::persist($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    private static function persist(array $payload): void
    {
        $write = static function () use ($payload): void {
            try {
                SecurityAuditLog::record($payload);
            } catch (\Throwable $exception) {
                report($exception);
            }
        };

        if (DB::transactionLevel() > 0) {
            DB::afterCommit($write);

            return;
        }

        $write();
    }

    private static function resolveActor(?User $actor, ?int $actorId): ?User
    {
        if ($actor) {
            return $actor;
        }

        if ($actorId) {
            return User::query()->find($actorId);
        }

        $authenticated = auth()->user();

        return $authenticated instanceof User ? $authenticated : null;
    }

    private static function subjectLabel(Model $subject): string
    {
        return match (true) {
            $subject instanceof User => trim($subject->displayFullName().' · '.($subject->email ?? 'no email')),
            $subject instanceof Family => (string) $subject->name,
            $subject instanceof Donation => $subject->formattedAmount().' · #'.$subject->getKey(),
            method_exists($subject, 'title') && filled($subject->title) => (string) $subject->title,
            method_exists($subject, 'name') && filled($subject->name) => (string) $subject->name,
            method_exists($subject, 'getKey') => class_basename($subject).' #'.$subject->getKey(),
            default => class_basename($subject),
        };
    }

    public static function logSettingsSaved(string $settingsPage): void
    {
        self::audit('settings_updated', context: [
            'settings_page' => $settingsPage,
            'portal' => self::adminPortalLabel(),
        ]);
    }

    public static function adminPortalLabel(): string
    {
        return 'parish admin panel';
    }

    public static function detectPortal(): string
    {
        $path = trim(AdminPanelConfig::path(), '/');

        if ($path !== '' && request()->is($path) || request()->is($path.'/*')) {
            return self::adminPortalLabel();
        }

        if (request()->routeIs('account', 'account.*')) {
            return 'member portal';
        }

        return 'public website';
    }
}
