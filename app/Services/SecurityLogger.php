<?php

namespace App\Services;

use App\Models\SecurityAuditLog;

class SecurityLogger
{
    public static function info(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        SecurityAuditLog::record($action, 'info', $userId, $metadata);
    }

    public static function warning(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        SecurityAuditLog::record($action, 'warning', $userId, $metadata);
    }

    public static function critical(string $action, ?int $userId = null, ?array $metadata = null): void
    {
        SecurityAuditLog::record($action, 'critical', $userId, $metadata);
    }
}
