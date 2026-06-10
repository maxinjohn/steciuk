<?php

namespace App\Enums;

enum AccountStatus: string
{
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Pending approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function canSignIn(): bool
    {
        return $this === self::Approved;
    }

    public function canUseMemberPortal(): bool
    {
        return $this === self::Approved;
    }
}
