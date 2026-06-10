<?php

namespace App\Models;

use App\Enums\DonationMethod;
use App\Enums\DonationStatus;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Donation extends Model
{
    protected $fillable = [
        'user_id',
        'family_id',
        'amount',
        'currency',
        'method',
        'status',
        'donated_on',
        'reference',
        'member_note',
        'accuracy_confirmed_at',
        'processing_basis',
        'admin_note',
        'recorded_by',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'donated_on' => 'date',
            'reviewed_at' => 'datetime',
            'accuracy_confirmed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function family(): BelongsTo
    {
        return $this->belongsTo(Family::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusEnum(): DonationStatus
    {
        return DonationStatus::tryFrom((string) $this->status) ?? DonationStatus::Pending;
    }

    public function methodEnum(): ?DonationMethod
    {
        return DonationMethod::tryFrom((string) $this->method);
    }

    public function isPending(): bool
    {
        return $this->statusEnum() === DonationStatus::Pending;
    }

    public function isApproved(): bool
    {
        return $this->statusEnum() === DonationStatus::Approved;
    }

    public function formattedAmount(): string
    {
        $symbol = match (strtoupper((string) $this->currency)) {
            'GBP' => '£',
            'EUR' => '€',
            'USD' => '$',
            default => strtoupper((string) $this->currency).' ',
        };

        return $symbol.number_format((float) $this->amount, 2);
    }
}
