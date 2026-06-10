<?php

namespace App\Enums;

enum DonationMethod: string
{
    case BankTransfer = 'bank_transfer';
    case Cash = 'cash';
    case Cheque = 'cheque';
    case CardOnline = 'card_online';
    case StandingOrder = 'standing_order';
    case Envelope = 'envelope';
    case Other = 'other';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::BankTransfer->value => 'Bank transfer',
            self::Cash->value => 'Cash',
            self::Cheque->value => 'Cheque',
            self::CardOnline->value => 'Card / online payment',
            self::StandingOrder->value => 'Standing order',
            self::Envelope->value => 'Church envelope / collection',
            self::Other->value => 'Other',
        ];
    }

    public function label(): string
    {
        return self::options()[$this->value] ?? $this->value;
    }
}
