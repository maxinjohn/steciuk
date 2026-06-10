<?php

namespace App\Enums;

enum FamilyRelationship: string
{
    case Head = 'head';
    case Spouse = 'spouse';
    case Child = 'child';
    case Parent = 'parent';
    case Other = 'other';

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return [
            self::Head->value => 'Head of household',
            self::Spouse->value => 'Spouse / partner',
            self::Child->value => 'Child',
            self::Parent->value => 'Parent',
            self::Other->value => 'Other family member',
        ];
    }

    public static function tryFromValue(?string $value): ?self
    {
        if ($value === null || $value === '') {
            return null;
        }

        return self::tryFrom($value);
    }

    public function label(): string
    {
        return self::options()[$this->value] ?? $this->value;
    }

    public function emailIsOptionalForHouseholdMember(): bool
    {
        return match ($this) {
            self::Child => true,
            self::Head => false,
            default => true,
        };
    }

    public static function householdAssignmentOptions(): array
    {
        return collect(self::options())
            ->except([self::Head->value])
            ->all();
    }

    public function emailHintForHouseholdMember(): string
    {
        return match ($this) {
            self::Child => 'Optional — for parish records only. Children sign in through the primary family account.',
            self::Spouse, self::Parent, self::Other => 'Optional — for parish records only. Household members sign in through the primary family account.',
            self::Head => 'Required for the primary family account holder.',
        };
    }
}
