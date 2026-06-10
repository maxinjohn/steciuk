<?php

namespace App\Filament\Resources\SecurityAuditLogs\Pages;

use App\Filament\Resources\SecurityAuditLogs\SecurityAuditLogResource;
use Filament\Resources\Pages\ViewRecord;

class ViewSecurityAuditLog extends ViewRecord
{
    protected static string $resource = SecurityAuditLogResource::class;
}
