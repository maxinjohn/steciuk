<?php

namespace App\Livewire\Account;

use App\Enums\DonationMethod;
use App\Models\Donation;
use App\Models\User;
use App\Support\GivingPageConfig;
use App\Services\DonationService;
use App\Support\GdprConfig;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class DonationManager extends Component
{
    public string $amount = '';

    public string $method = '';

    public string $donated_on = '';

    public string $reference = '';

    public string $member_note = '';

    public bool $confirm_accuracy = false;

    public bool $submitted = false;

    public string $export_from = '';

    public string $export_to = '';

    public string $export_month = '';

    public bool $export_include_all_statuses = false;

    public function mount(): void
    {
        $this->donated_on = now()->format('Y-m-d');
        $this->method = DonationMethod::BankTransfer->value;
        $this->export_from = now()->startOfMonth()->format('Y-m-d');
        $this->export_to = now()->endOfMonth()->format('Y-m-d');
        $this->export_month = now()->format('Y-m');
    }

    public function submit(DonationService $donationService): void
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User, 403);

        $this->submitted = false;

        $validated = $this->validate([
            'amount' => 'required|numeric|min:0.01|max:999999.99',
            'method' => 'required|string|in:'.implode(',', array_keys(DonationMethod::options())),
            'donated_on' => 'required|date|before_or_equal:today|after:2000-01-01',
            'reference' => 'nullable|string|max:120',
            'member_note' => 'nullable|string|max:1000',
            'confirm_accuracy' => 'accepted',
        ]);

        $donationService->submitFromAccount($user, $validated);

        $this->reset(['amount', 'reference', 'member_note', 'confirm_accuracy']);
        $this->method = DonationMethod::BankTransfer->value;
        $this->donated_on = now()->format('Y-m-d');
        $this->submitted = true;
    }

    public function exportPersonalStatement()
    {
        return redirect()->route('account.giving.export', $this->exportQuery('personal'));
    }

    public function exportHouseholdStatement()
    {
        /** @var User|null $user */
        $user = Auth::user();

        abort_unless($user instanceof User && $user->canViewHouseholdGivingOnPortal(), 403);

        return redirect()->route('account.giving.export', $this->exportQuery('household'));
    }

    /**
     * @return array<string, mixed>
     */
    private function exportQuery(string $scope): array
    {
        if (blank($this->export_to)) {
            $this->export_to = now()->format('Y-m-d');
        }

        if (blank($this->export_from)) {
            $this->export_from = now()->startOfMonth()->format('Y-m-d');
        }

        $validated = $this->validate([
            'export_from' => 'nullable|date|before_or_equal:today',
            'export_to' => 'nullable|date|before_or_equal:today|after_or_equal:export_from',
            'export_month' => 'nullable|date_format:Y-m',
            'export_include_all_statuses' => 'boolean',
        ]);

        if (blank($validated['export_month'] ?? null)
            && filled($validated['export_from'] ?? null)
            && filled($validated['export_to'] ?? null)
            && $validated['export_from'] > $validated['export_to']) {
            throw ValidationException::withMessages([
                'export_from' => 'The start date must be on or before the end date.',
            ]);
        }

        return array_filter([
            'scope' => $scope,
            'from' => blank($validated['export_month'] ?? null) ? ($validated['export_from'] ?: null) : null,
            'to' => blank($validated['export_month'] ?? null) ? ($validated['export_to'] ?: null) : null,
            'month' => $validated['export_month'] ?: null,
            'include_all_statuses' => $validated['export_include_all_statuses'] ? 1 : null,
        ]);
    }

    public function render()
    {
        /** @var User|null $user */
        $user = Auth::user();
        $donationService = app(DonationService::class);

        $donations = collect();
        $householdDonations = collect();
        $summary = ['personal' => 0.0, 'household' => 0.0, 'pending_count' => 0];

        if ($user) {
            $summary = $donationService->accountSummary($user);

            $donations = Donation::query()
                ->where('user_id', $user->id)
                ->latest('donated_on')
                ->latest('id')
                ->limit(50)
                ->get();

            if ($user->canViewHouseholdGivingOnPortal()) {
                $householdDonations = Donation::query()
                    ->with('user')
                    ->where('family_id', $user->family_id)
                    ->latest('donated_on')
                    ->latest('id')
                    ->limit(50)
                    ->get();
            }
        }

        return view('livewire.account.donation-manager', [
            'donations' => $donations,
            'householdDonations' => $householdDonations,
            'summary' => $summary,
            'methodOptions' => DonationMethod::options(),
            'canViewHousehold' => $user?->canViewHouseholdGivingOnPortal() ?? false,
            'privacyPolicyUrl' => GdprConfig::privacyPolicyUrl(),
            'givingBankDetails' => GivingPageConfig::bankDetails(),
        ]);
    }
}
