<?php

namespace App\Listeners;

use App\Models\Donation;
use App\Models\SecurityAuditLog;
use App\Models\User;
use App\Services\SecurityLogger;
use App\Support\AdminPanelConfig;
use Filament\Resources\Events\RecordCreated;
use Filament\Resources\Events\RecordUpdated;
use Illuminate\Database\Eloquent\Model;

class FilamentAdminAuditListener
{
    /**
     * @var list<class-string<Model>>
     */
    private const SKIP_MODELS = [
        SecurityAuditLog::class,
        Donation::class,
    ];

    /**
     * Filament dispatches resource events with named payload arrays.
     *
     * @param  mixed  ...$args
     */
    public function recordCreated(...$args): void
    {
        $record = $this->resolveRecord($args);

        if ($record) {
            $this->log($record, 'admin_record_created');
        }
    }

    /**
     * @param  mixed  ...$args
     */
    public function recordUpdated(...$args): void
    {
        $record = $this->resolveRecord($args);

        if ($record) {
            $this->log($record, 'admin_record_updated');
        }
    }

    /**
     * @param  list<mixed>  $args
     */
    private function resolveRecord(array $args): ?Model
    {
        $first = $args[0] ?? null;

        if ($first instanceof RecordCreated || $first instanceof RecordUpdated) {
            return $first->getRecord();
        }

        return $first instanceof Model ? $first : null;
    }

    private function log(Model $record, string $action): void
    {
        if (! $this->shouldLog($record)) {
            return;
        }

        SecurityLogger::audit($action, subject: $record, context: [
            'resource' => class_basename($record),
            'resource_id' => $record->getKey(),
            'portal' => SecurityLogger::adminPortalLabel(),
        ]);
    }

    private function shouldLog(Model $record): bool
    {
        if (! auth()->user() instanceof User) {
            return false;
        }

        foreach (self::SKIP_MODELS as $modelClass) {
            if ($record instanceof $modelClass) {
                return false;
            }
        }

        $path = trim(AdminPanelConfig::path(), '/');

        return $path !== '' && (request()->is($path) || request()->is($path.'/*'));
    }
}
