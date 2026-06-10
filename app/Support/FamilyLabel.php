<?php

namespace App\Support;

use App\Models\Family;
use App\Models\User;
use Illuminate\Support\Collection;

class FamilyLabel
{
    public static function forAdmin(Family $family, bool $includeHouseholdId = true): string
    {
        $family->loadMissing('admin');

        $parts = [trim($family->name ?: 'Parish household')];

        if ($family->admin) {
            $parts[] = $family->admin->displayFullName();

            if (filled($family->admin->email)) {
                $parts[] = $family->admin->email;
            }
        }

        if (filled($family->preferred_worship_location)) {
            $parts[] = $family->preferred_worship_location;
        }

        if ($includeHouseholdId) {
            $parts[] = 'Household #'.$family->id;
        }

        return implode(' · ', array_filter($parts));
    }

    public static function forMemberPortal(Family $family): string
    {
        $family->loadMissing('admin');

        $name = trim($family->name ?: 'Parish household');

        if ($family->admin && filled($family->admin->displayFullName())) {
            return $name.' ('.$family->admin->displayFullName().')';
        }

        return $name;
    }

    /**
     * Compact labelled lines for a member row on the Users list.
     *
     * @return list<string>
     */
    public static function userFamilyTableLines(User $user): array
    {
        $user->loadMissing('family.admin');

        $family = $user->family;

        if (! $family) {
            return [];
        }

        $lines = [
            'Family name: '.trim($family->name ?: 'Parish household'),
        ];

        if ($family->admin && filled($family->admin->displayFullName())) {
            $lines[] = 'Family admin: '.$family->admin->displayFullName();
        }

        $relationship = $user->familyRelationship()?->label();

        if ($relationship) {
            $lines[] = 'Relation to admin: '.$relationship;
        }

        return $lines;
    }

    /**
     * Compact labelled lines for family household rows.
     *
     * @return list<string>
     */
    public static function familyTableLines(Family $family): array
    {
        $family->loadMissing('admin');

        $lines = [
            'Family name: '.trim($family->name ?: 'Parish household'),
        ];

        if ($family->admin && filled($family->admin->displayFullName())) {
            $lines[] = 'Family admin: '.$family->admin->displayFullName();
        }

        return $lines;
    }

    public static function tableSummary(Family $family): ?string
    {
        return $family->isActive() ? null : 'Deactivated';
    }

    /**
     * @return array<int, string>
     */
    public static function selectOptions(?Collection $families = null): array
    {
        $families ??= Family::query()
            ->with('admin')
            ->orderBy('name')
            ->orderBy('id')
            ->get();

        return $families->mapWithKeys(
            fn (Family $family): array => [$family->id => self::forAdmin($family)]
        )->all();
    }
}
