<?php

namespace App\Enums;

enum ServiceScheduleType: string
{
    case OneTime = 'one_time';
    case Multiple = 'multiple';
    case Recurring = 'recurring';

    public function label(): string
    {
        return match ($this) {
            self::OneTime => 'One-time',
            self::Multiple => 'Multiple dates',
            self::Recurring => 'Regular / repeating',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::OneTime => 'A single worship date — ideal for special services or one-off gatherings.',
            self::Multiple => 'Several specific dates — pick each date and time individually.',
            self::Recurring => 'Repeating pattern — weekly, fortnightly, or monthly on a set day.',
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::cases())
            ->mapWithKeys(fn (self $type): array => [$type->value => $type->label()])
            ->all();
    }
}
