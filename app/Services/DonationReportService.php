<?php

namespace App\Services;

use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\Family;
use App\Models\Setting;
use App\Models\User;
use App\Support\DonationReportScope;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

class DonationReportService
{
    /**
     * @param  array{from?: string|null, to?: string|null, month?: string|null}  $input
     * @return array{from: Carbon, to: Carbon, label: string}
     */
    public function resolvePeriod(array $input): array
    {
        if (filled($input['month'] ?? null)) {
            $month = Carbon::createFromFormat('Y-m', (string) $input['month'])->startOfMonth();

            return [
                'from' => $month->copy()->startOfDay(),
                'to' => $month->copy()->endOfMonth()->endOfDay(),
                'label' => $month->format('F Y'),
            ];
        }

        $from = filled($input['from'] ?? null)
            ? Carbon::parse((string) $input['from'])->startOfDay()
            : null;
        $to = filled($input['to'] ?? null)
            ? Carbon::parse((string) $input['to'])->endOfDay()
            : null;

        if (! $from && ! $to) {
            $from = now()->startOfMonth()->startOfDay();
            $to = now()->endOfMonth()->endOfDay();
        } elseif ($from && ! $to) {
            $to = $from->copy()->endOfMonth()->endOfDay();
        } elseif (! $from && $to) {
            $from = $to->copy()->startOfMonth()->startOfDay();
        }

        if ($from->greaterThan($to)) {
            throw ValidationException::withMessages([
                'from' => 'The start date must be on or before the end date.',
            ]);
        }

        $label = $from->isSameDay($to)
            ? $from->format('j F Y')
            : $from->format('j M Y').' – '.$to->format('j M Y');

        return compact('from', 'to', 'label');
    }

    /**
     * @return array{
     *     title: string,
     *     subtitle: string,
     *     period_label: string,
     *     parish_name: string,
     *     charity_number: string|null,
     *     generated_at: string,
     *     scope_label: string,
     *     status_filter: string,
     *     donations: Collection<int, Donation>,
     *     total_approved: float,
     *     total_pending: float,
     *     total_rejected: float,
     * }
     */
    public function buildReport(
        DonationReportScope $scope,
        Carbon $from,
        Carbon $to,
        string $periodLabel,
        ?User $member = null,
        ?Family $family = null,
        ?DonationStatus $status = DonationStatus::Approved,
        bool $includeAllStatuses = false,
    ): array {
        $donations = $this->queryDonations($scope, $from, $to, $member, $family, $includeAllStatuses ? null : $status)
            ->with(['user', 'family'])
            ->orderBy('donated_on')
            ->orderBy('id')
            ->get();

        $subtitle = match ($scope) {
            DonationReportScope::Personal => $member?->displayFullName() ?? 'Member',
            DonationReportScope::Household => $family?->name ?? 'Household',
            DonationReportScope::Member => trim(($member?->displayFullName() ?? 'Member').($member?->email ? ' · '.$member->email : '')),
            DonationReportScope::Family => $family?->name ?? 'Family household',
            DonationReportScope::All => 'All recorded gifts',
        };

        return [
            'title' => 'Parish giving statement',
            'subtitle' => $subtitle,
            'period_label' => $periodLabel,
            'parish_name' => (string) (Setting::get('site_name') ?: config('site.name', 'STECI UK Parish')),
            'charity_number' => Setting::get('charity_number') ?: null,
            'generated_at' => now()->format('j F Y H:i'),
            'scope_label' => $scope->label(),
            'show_donor_column' => ! in_array($scope, [DonationReportScope::Personal, DonationReportScope::Member], true),
            'show_family_column' => in_array($scope, [DonationReportScope::Household, DonationReportScope::Family, DonationReportScope::All], true),
            'status_filter' => $includeAllStatuses ? 'All statuses' : ($status?->label() ?? 'Approved'),
            'donations' => $donations,
            'total_approved' => (float) $donations->where('status', DonationStatus::Approved->value)->sum('amount'),
            'total_pending' => (float) $donations->where('status', DonationStatus::Pending->value)->sum('amount'),
            'total_rejected' => (float) $donations->where('status', DonationStatus::Rejected->value)->sum('amount'),
            'verification' => app(VicarVerificationService::class)->pdfVerificationBlock(),
        ];
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function memberPdfResponse(User $actor, array $filters): Response
    {
        $scope = DonationReportScope::tryFrom((string) ($filters['scope'] ?? '')) ?? DonationReportScope::Personal;

        abort_unless(in_array($scope, [DonationReportScope::Personal, DonationReportScope::Household], true), 403);

        if ($scope === DonationReportScope::Household) {
            abort_unless($actor->canViewHouseholdGivingOnPortal(), 403);
        }

        $actor->loadMissing('family');
        $period = $this->resolvePeriod($filters);
        $includeAll = (bool) ($filters['include_all_statuses'] ?? false);
        $family = $actor->family;

        $report = $this->buildReport(
            scope: $scope,
            from: $period['from'],
            to: $period['to'],
            periodLabel: $period['label'],
            member: $scope === DonationReportScope::Personal ? $actor : null,
            family: $scope === DonationReportScope::Household ? $family : null,
            includeAllStatuses: $includeAll,
        );

        SecurityLogger::audit('donation_report_exported', actor: $actor, context: [
            'scope' => $scope->value,
            'period' => $period['label'],
            'portal' => SecurityLogger::detectPortal(),
        ]);

        return $this->pdfDownload($report, $this->filename($scope, $period['label'], $actor));
    }

    /**
     * @param  array<string, mixed>  $filters
     */
    public function adminPdfResponse(User $admin, array $filters): Response
    {
        abort_unless($admin->can('viewAny', Donation::class), 403);

        $scope = DonationReportScope::tryFrom((string) ($filters['scope'] ?? '')) ?? DonationReportScope::All;
        $period = $this->resolvePeriod($filters);
        $includeAll = (bool) ($filters['include_all_statuses'] ?? false);

        $member = filled($filters['user_id'] ?? null)
            ? User::query()->findOrFail((int) $filters['user_id'])
            : null;
        $family = filled($filters['family_id'] ?? null)
            ? Family::query()->findOrFail((int) $filters['family_id'])
            : null;

        if ($scope === DonationReportScope::Member) {
            abort_unless($member, 422, 'Choose a member for this export.');
        }

        if ($scope === DonationReportScope::Family) {
            abort_unless($family, 422, 'Choose a family for this export.');
        }

        $report = $this->buildReport(
            scope: $scope,
            from: $period['from'],
            to: $period['to'],
            periodLabel: $period['label'],
            member: $member,
            family: $family,
            includeAllStatuses: $includeAll,
        );

        SecurityLogger::audit('donation_report_exported', actor: $admin, context: [
            'scope' => $scope->value,
            'period' => $period['label'],
            'target_user_id' => $member?->id,
            'family_id' => $family?->id,
            'portal' => SecurityLogger::detectPortal(),
        ]);

        return $this->pdfDownload($report, $this->filename($scope, $period['label'], $member, $family));
    }

    /**
     * @return Builder<Donation>
     */
    private function queryDonations(
        DonationReportScope $scope,
        Carbon $from,
        Carbon $to,
        ?User $member,
        ?Family $family,
        ?DonationStatus $status,
    ): Builder {
        $query = Donation::query()
            ->whereDate('donated_on', '>=', $from->toDateString())
            ->whereDate('donated_on', '<=', $to->toDateString());

        if ($status) {
            $query->where('status', $status->value);
        }

        return match ($scope) {
            DonationReportScope::Personal => $query->where('user_id', $member?->id),
            DonationReportScope::Member => $query->where('user_id', $member?->id),
            DonationReportScope::Household => $query->where(
                'family_id',
                $family?->id ?? -1,
            ),
            DonationReportScope::Family => $query->where(
                'family_id',
                $family?->id ?? -1,
            ),
            DonationReportScope::All => $query,
        };
    }

    /**
     * @param  array<string, mixed>  $report
     */
    private function pdfDownload(array $report, string $filename): Response
    {
        $pdf = Pdf::loadView('reports.donation-statement', $report)
            ->setPaper('a4', 'portrait');

        return $pdf->download($filename);
    }

    private function filename(
        DonationReportScope $scope,
        string $periodLabel,
        ?User $member = null,
        ?Family $family = null,
    ): string {
        $subject = match ($scope) {
            DonationReportScope::Personal, DonationReportScope::Member => $member?->displayFullName() ?? 'member',
            DonationReportScope::Household, DonationReportScope::Family => $family?->name ?? 'household',
            DonationReportScope::All => 'all-giving',
        };

        return Str::slug($scope->value.'-'.$subject.'-'.$periodLabel).'.pdf';
    }
}
