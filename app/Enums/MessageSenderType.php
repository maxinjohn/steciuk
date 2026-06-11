<?php

namespace App\Enums;

enum MessageSenderType: string
{
    case Guest = 'guest';
    case Member = 'member';
    case Admin = 'admin';

    public function label(): string
    {
        return match ($this) {
            self::Guest => 'Guest',
            self::Member => 'Member',
            self::Admin => 'Parish office',
        };
    }
}
