<?php

namespace App\Filament\Resources\SecurityAuditLogs\Pages;

use App\Filament\Resources\SecurityAuditLogs\SecurityAuditLogResource;
use App\Services\SecurityAuditLogService;
use Carbon\Carbon;
use Filament\Actions\Action;
use Filament\Forms\Components\DatePicker;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ListRecords;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ListSecurityAuditLogs extends ListRecords
{
    protected static string $resource = SecurityAuditLogResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('cleanActivityLog')
                ->label('Clean older entries')
                ->icon(Heroicon::OutlinedTrash)
                ->color('danger')
                ->visible(fn (): bool => auth()->user()?->hasFullPanelAccess() ?? false)
                ->form([
                    DatePicker::make('before_date')
                        ->label('Delete entries on or before')
                        ->helperText(function (): string {
                            $service = app(SecurityAuditLogService::class);
                            $latest = $service->maxPurgeBeforeDate()->format('j M Y');

                            return 'For security, entries from the last '.$service->retentionDays().' days cannot be deleted. The latest date you can choose is '.$latest.'.';
                        })
                        ->required()
                        ->native(false)
                        ->maxDate(fn (): Carbon => app(SecurityAuditLogService::class)->maxPurgeBeforeDate())
                        ->default(fn (): Carbon => app(SecurityAuditLogService::class)->maxPurgeBeforeDate()->copy()->subMonths(6)->startOfDay()),
                ])
                ->requiresConfirmation()
                ->modalHeading('Clean activity log')
                ->modalDescription(function (array $data): string {
                    $beforeDate = $data['before_date'] ?? null;

                    if (blank($beforeDate)) {
                        return 'Choose a date, then confirm to permanently delete matching activity log entries.';
                    }

                    $count = app(SecurityAuditLogService::class)->countOnOrBefore(Carbon::parse($beforeDate));

                    if ($count === 0) {
                        $service = app(SecurityAuditLogService::class);
                        $parsed = Carbon::parse($beforeDate);

                        if ($parsed->copy()->endOfDay()->gt($service->maxPurgeBeforeDate())) {
                            return 'For security, only entries older than '.$service->retentionDays().' days can be deleted. Choose a date on or before '.$service->maxPurgeBeforeDate()->format('j M Y').'.';
                        }

                        return 'No activity log entries were found on or before '.$parsed->format('j M Y').'.';
                    }

                    return 'This will permanently delete '.$count.' '.Str::plural('entry', $count).' on or before '.Carbon::parse($beforeDate)->format('j M Y').'.';
                })
                ->action(function (array $data): void {
                    $actor = auth()->user();

                    if (! $actor) {
                        return;
                    }

                    $purgedCount = app(SecurityAuditLogService::class)->purgeOnOrBefore(
                        $actor,
                        Carbon::parse($data['before_date']),
                    );

                    if ($purgedCount === 0) {
                        $service = app(SecurityAuditLogService::class);
                        $parsed = Carbon::parse($data['before_date']);

                        Notification::make()
                            ->warning()
                            ->title('Nothing to clean')
                            ->body(
                                $parsed->copy()->endOfDay()->gt($service->maxPurgeBeforeDate())
                                    ? 'For security, only entries older than '.$service->retentionDays().' days can be deleted.'
                                    : 'No activity log entries matched that date.'
                            )
                            ->send();

                        return;
                    }

                    Notification::make()
                        ->success()
                        ->title('Activity log cleaned')
                        ->body('Removed '.$purgedCount.' '.Str::plural('entry', $purgedCount).'.')
                        ->send();
                }),
        ];
    }
}
