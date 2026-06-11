<?php

namespace App\Services;

use App\Models\SecurityAuditLog;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SecurityAuditLogService
{
    /**
     * Permanently delete activity log entries on or before the given date.
     *
     * Entries from the last {@see retentionDays()} days are always kept.
     */
    public function purgeOnOrBefore(User $actor, CarbonInterface $beforeDate): int
    {
        if (! $actor->isSuperAdmin()) {
            throw new InvalidArgumentException('Only super admins can clean the activity log.');
        }

        $cutoff = $beforeDate->copy()->endOfDay();

        if ($cutoff->isFuture()) {
            throw new InvalidArgumentException('The clean-up date cannot be in the future.');
        }

        if ($cutoff->gt($this->maxPurgeBeforeDate())) {
            throw new InvalidArgumentException(
                'For security, only entries older than '.$this->retentionDays().' days can be deleted.'
            );
        }

        return DB::transaction(function () use ($actor, $cutoff): int {
            $purgedCount = SecurityAuditLog::query()
                ->where('created_at', '<=', $cutoff)
                ->count();

            if ($purgedCount === 0) {
                return 0;
            }

            SecurityAuditLog::query()
                ->where('created_at', '<=', $cutoff)
                ->delete();

            SecurityLogger::audit('security_audit_log_purged', actor: $actor, context: [
                'purged_count' => $purgedCount,
                'before_date' => $cutoff->toDateString(),
                'portal' => SecurityLogger::adminPortalLabel(),
            ]);

            return $purgedCount;
        });
    }

    public function countOnOrBefore(CarbonInterface $beforeDate): int
    {
        $cutoff = $beforeDate->copy()->endOfDay();

        if ($cutoff->gt($this->maxPurgeBeforeDate())) {
            return 0;
        }

        return SecurityAuditLog::query()
            ->where('created_at', '<=', $cutoff)
            ->count();
    }

    public function retentionDays(): int
    {
        return max(1, (int) config('security.audit_log_min_retention_days', 30));
    }

    public function maxPurgeBeforeDate(): CarbonInterface
    {
        return Carbon::now()->subDays($this->retentionDays())->endOfDay();
    }
}
