<?php

namespace App\Services;

use App\Models\SecurityAuditLog;

class SecurityLogger
{
    public static function info(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        self::write($action, 'info', $userId, $metadata, defer: self::shouldDefer($action));
    }

    public static function warning(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        self::write($action, 'warning', $userId, $metadata, defer: self::shouldDefer($action));
    }

    public static function critical(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        self::write($action, 'critical', $userId, $metadata, defer: false);
    }

    private static function shouldDefer(string $action): bool
    {
        return in_array($action, ['login_failed', 'login_rate_limited', 'user_login', 'user_logout'], true);
    }

    private static function write(
        string $action,
        string $severity,
        ?int $userId,
        ?array $metadata,
        bool $defer,
    ): void {
        $write = static function () use ($action, $severity, $userId, $metadata): void {
            SecurityAuditLog::record($action, $severity, $userId, $metadata);
        };

        if ($defer) {
            app()->terminating($write);

            return;
        }

        $write();
    }
}
