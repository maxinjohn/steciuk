<?php

namespace App\Support;

enum DonationReportScope: string
{
    case Personal = 'personal';
    case Household = 'household';
    case Member = 'member';
    case Family = 'family';
    case All = 'all';

    public function label(): string
    {
        return match ($this) {
            self::Personal => 'My giving',
            self::Household => 'Household giving',
            self::Member => 'Individual member',
            self::Family => 'Family household',
            self::All => 'All parish giving',
        };
    }
}
