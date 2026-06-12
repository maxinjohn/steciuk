<?php

namespace App\Services;

use App\Enums\DonationMethod;
use App\Enums\DonationStatus;
use App\Models\Donation;
use App\Models\User;
use Illuminate\Validation\ValidationException;

class DonationService
{
    /**
     * @param  array<string, mixed>  $data
     */
    public function submitFromAccount(User $user, array $data): Donation
    {
        abort_unless($user->isActive() && $user->familyIsActive(), 403);

        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount < 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Please enter a valid donation amount.',
            ]);
        }

        $method = DonationMethod::tryFrom((string) ($data['method'] ?? ''));

        if (! $method) {
            throw ValidationException::withMessages([
                'method' => 'Please choose how you donated.',
            ]);
        }

        $donation = Donation::query()->create([
            'user_id' => $user->id,
            'family_id' => $user->family_id,
            'amount' => $amount,
            'currency' => 'GBP',
            'method' => $method->value,
            'status' => DonationStatus::Pending->value,
            'donated_on' => $data['donated_on'] ?? now()->toDateString(),
            'reference' => filled($data['reference'] ?? null) ? trim((string) $data['reference']) : null,
            'member_note' => filled($data['member_note'] ?? null) ? trim((string) $data['member_note']) : null,
            'accuracy_confirmed_at' => now(),
            'processing_basis' => 'legal_obligation',
            'recorded_by' => null,
        ]);

        SecurityLogger::audit(
            'donation_submitted',
            actor: $user,
            subject: $donation,
            context: [
                'donation_id' => $donation->id,
                'amount' => $donation->formattedAmount(),
                'method' => $method->value,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $donation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function recordManual(User $admin, array $data, bool $approveImmediately = true): Donation
    {
        abort_unless($admin->can('create', Donation::class), 403);

        $donor = User::query()->findOrFail($data['user_id']);
        $this->assertEligibleDonor($donor);
        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount < 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Please enter a valid donation amount.',
            ]);
        }

        $method = DonationMethod::tryFrom((string) ($data['method'] ?? ''));

        if (! $method) {
            throw ValidationException::withMessages([
                'method' => 'Please choose a donation method.',
            ]);
        }

        $status = $approveImmediately
            ? DonationStatus::Approved
            : (DonationStatus::tryFrom((string) ($data['status'] ?? '')) ?? DonationStatus::Pending);

        $donation = Donation::query()->create([
            'user_id' => $donor->id,
            'family_id' => $donor->family_id,
            'amount' => $amount,
            'currency' => 'GBP',
            'method' => $method->value,
            'status' => $status->value,
            'donated_on' => $data['donated_on'] ?? now()->toDateString(),
            'reference' => filled($data['reference'] ?? null) ? trim((string) $data['reference']) : null,
            'member_note' => filled($data['member_note'] ?? null) ? trim((string) $data['member_note']) : null,
            'admin_note' => filled($data['admin_note'] ?? null) ? trim((string) $data['admin_note']) : null,
            'processing_basis' => 'legal_obligation',
            'recorded_by' => $admin->id,
            'reviewed_by' => $status === DonationStatus::Approved ? $admin->id : null,
            'reviewed_at' => $status === DonationStatus::Approved ? now() : null,
        ]);

        SecurityLogger::audit(
            'donation_recorded',
            actor: $admin,
            subject: $donation,
            context: [
                'donation_id' => $donation->id,
                'target_user_id' => $donor->id,
                'amount' => $donation->formattedAmount(),
                'status' => $status->value,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $donation;
    }

    public function approve(Donation $donation, User $admin, ?string $adminNote = null): Donation
    {
        abort_unless($admin->can('update', $donation), 403);

        $donation->update([
            'status' => DonationStatus::Approved->value,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_note' => $adminNote ?? $donation->admin_note,
        ]);

        $donation = $donation->fresh();

        SecurityLogger::audit(
            'donation_approved',
            actor: $admin,
            subject: $donation,
            context: [
                'donation_id' => $donation->id,
                'target_user_id' => $donation->user_id,
                'amount' => $donation->formattedAmount(),
                'admin_note' => $adminNote,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $donation;
    }

    public function reject(Donation $donation, User $admin, ?string $adminNote = null): Donation
    {
        abort_unless($admin->can('update', $donation), 403);

        $donation->update([
            'status' => DonationStatus::Rejected->value,
            'reviewed_by' => $admin->id,
            'reviewed_at' => now(),
            'admin_note' => $adminNote ?? $donation->admin_note,
        ]);

        $donation = $donation->fresh();

        SecurityLogger::audit(
            'donation_rejected',
            actor: $admin,
            subject: $donation,
            context: [
                'donation_id' => $donation->id,
                'target_user_id' => $donation->user_id,
                'amount' => $donation->formattedAmount(),
                'admin_note' => $adminNote,
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $donation;
    }

    public function setPending(Donation $donation, User $admin): Donation
    {
        abort_unless($admin->can('update', $donation), 403);

        $donation->update([
            'status' => DonationStatus::Pending->value,
            'reviewed_by' => null,
            'reviewed_at' => null,
        ]);

        $donation = $donation->fresh();

        SecurityLogger::audit(
            'donation_pending',
            actor: $admin,
            subject: $donation,
            context: [
                'donation_id' => $donation->id,
                'target_user_id' => $donation->user_id,
                'amount' => $donation->formattedAmount(),
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        return $donation;
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public function updateFromAdmin(User $admin, Donation $donation, array $data): Donation
    {
        abort_unless($admin->can('update', $donation), 403);

        $donor = User::query()->findOrFail($data['user_id'] ?? $donation->user_id);
        $this->assertEligibleDonor($donor);

        $amount = round((float) ($data['amount'] ?? 0), 2);

        if ($amount < 0.01) {
            throw ValidationException::withMessages([
                'amount' => 'Please enter a valid donation amount.',
            ]);
        }

        $method = DonationMethod::tryFrom((string) ($data['method'] ?? ''));

        if (! $method) {
            throw ValidationException::withMessages([
                'method' => 'Please choose a donation method.',
            ]);
        }

        $newStatus = DonationStatus::tryFrom((string) ($data['status'] ?? '')) ?? $donation->statusEnum();
        $currentStatus = $donation->statusEnum();

        $donation->update([
            'user_id' => $donor->id,
            'family_id' => $donor->family_id,
            'amount' => $amount,
            'method' => $method->value,
            'donated_on' => $data['donated_on'] ?? $donation->donated_on,
            'reference' => filled($data['reference'] ?? null) ? trim((string) $data['reference']) : null,
            'member_note' => filled($data['member_note'] ?? null) ? trim((string) $data['member_note']) : null,
            'admin_note' => filled($data['admin_note'] ?? null) ? trim((string) $data['admin_note']) : null,
        ]);

        $donation = $donation->fresh();

        if ($newStatus === $currentStatus) {
            return $donation;
        }

        return match ($newStatus) {
            DonationStatus::Approved => $this->approve($donation, $admin, $data['admin_note'] ?? null),
            DonationStatus::Rejected => $this->reject($donation, $admin, $data['admin_note'] ?? null),
            DonationStatus::Pending => $this->setPending($donation, $admin),
        };
    }

    public function deleteFromAdmin(User $admin, Donation $donation): void
    {
        abort_unless($admin->can('delete', $donation), 403);

        SecurityLogger::audit(
            'donation_deleted',
            actor: $admin,
            subject: $donation,
            context: [
                'donation_id' => $donation->id,
                'target_user_id' => $donation->user_id,
                'amount' => $donation->formattedAmount(),
                'portal' => SecurityLogger::detectPortal(),
            ],
        );

        $donation->delete();
    }

    /**
     * @return list<int>
     */
    public static function familyMemberIds(?int $familyId): array
    {
        if (! $familyId) {
            return [];
        }

        return User::query()
            ->where('family_id', $familyId)
            ->pluck('id')
            ->all();
    }

    public function approvedTotalForUser(User $user): float
    {
        return (float) Donation::query()
            ->where('user_id', $user->id)
            ->where('status', DonationStatus::Approved->value)
            ->sum('amount');
    }

    public function approvedTotalForFamily(?int $familyId): float
    {
        if (! $familyId) {
            return 0.0;
        }

        return (float) Donation::query()
            ->where('family_id', $familyId)
            ->where('status', DonationStatus::Approved->value)
            ->sum('amount');
    }

    /**
     * @return array{personal: float, household: float, pending_count: int}
     */
    public function accountSummary(User $user): array
    {
        $personal = $this->approvedTotalForUser($user);
        $household = $this->approvedTotalForFamily($user->family_id);
        $pendingQuery = Donation::query()->where('status', DonationStatus::Pending->value);

        if ($user->canViewHouseholdGivingOnPortal()) {
            $pendingCount = (clone $pendingQuery)->where('family_id', $user->family_id)->count();
        } else {
            $pendingCount = (clone $pendingQuery)->where('user_id', $user->id)->count();
        }

        return [
            'personal' => $personal,
            'household' => $household,
            'pending_count' => $pendingCount,
        ];
    }

    private function assertEligibleDonor(User $donor): void
    {
        if (! $donor->isActive()) {
            throw ValidationException::withMessages([
                'user_id' => 'This member account is inactive.',
            ]);
        }

        if (! $donor->familyIsActive()) {
            throw ValidationException::withMessages([
                'user_id' => 'This member\'s household is inactive.',
            ]);
        }
    }
}
