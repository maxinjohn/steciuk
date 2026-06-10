<?php

namespace App\Filament\Support;

use App\Models\Family;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class AdminTableSearch
{
    /**
     * @param  Builder<User>  $query
     */
    public static function applyUsers(Builder $query, string $search): void
    {
        foreach (self::terms($search) as $term) {
            $like = '%'.$term.'%';
            $table = $query->getModel()->getTable();

            $query->where(function (Builder $query) use ($like, $table, $term): void {
                $query
                    ->where("{$table}.first_name", 'like', $like)
                    ->orWhere("{$table}.last_name", 'like', $like)
                    ->orWhere("{$table}.name", 'like', $like)
                    ->orWhere("{$table}.email", 'like', $like)
                    ->orWhere("{$table}.role", 'like', $like)
                    ->orWhere("{$table}.preferred_worship_location", 'like', $like)
                    ->orWhere("{$table}.postcode", 'like', $like)
                    ->orWhereHas('family', function (Builder $query) use ($like, $term): void {
                        self::applyFamilyMatch($query, $like, $term);

                        $query->orWhereHas('admin', function (Builder $query) use ($like): void {
                            self::applyPersonNameSearch($query, $like);
                        });
                    });

                $adminIds = self::matchingFamilyAdminIds($like, $term);

                if ($adminIds !== []) {
                    $query->orWhereIn("{$table}.id", $adminIds);
                }

                if ($familyId = self::numericId($term)) {
                    $query->orWhere("{$table}.family_id", $familyId);
                }
            });
        }
    }

    /**
     * @param  Builder<Family>  $query
     */
    public static function applyFamilies(Builder $query, string $search): void
    {
        foreach (self::terms($search) as $term) {
            $like = '%'.$term.'%';
            $table = $query->getModel()->getTable();

            $query->where(function (Builder $query) use ($like, $table, $term): void {
                $query
                    ->where("{$table}.name", 'like', $like)
                    ->orWhere("{$table}.preferred_worship_location", 'like', $like)
                    ->orWhereHas('admin', function (Builder $query) use ($like): void {
                        self::applyPersonNameSearch($query, $like);
                    })
                    ->orWhereHas('members', function (Builder $query) use ($like): void {
                        self::applyPersonNameSearch($query, $like);
                    });

                if ($familyId = self::numericId($term)) {
                    $query->orWhere("{$table}.id", $familyId);
                }
            });
        }
    }

    /**
     * @return list<int>
     */
    private static function matchingFamilyAdminIds(string $like, string $term): array
    {
        return Family::query()
            ->whereNotNull('admin_user_id')
            ->where(function (Builder $query) use ($like, $term): void {
                self::applyFamilyMatch($query, $like, $term);
            })
            ->pluck('admin_user_id')
            ->filter()
            ->unique()
            ->values()
            ->all();
    }

    private static function applyFamilyMatch(Builder $query, string $like, string $term): void
    {
        $table = $query->getModel()->getTable();

        $query
            ->where("{$table}.name", 'like', $like)
            ->orWhere("{$table}.preferred_worship_location", 'like', $like);

        if ($familyId = self::numericId($term)) {
            $query->orWhere("{$table}.id", $familyId);
        }
    }

    /**
     * @return list<string>
     */
    public static function terms(string $search): array
    {
        return array_values(array_filter(
            str_getcsv(preg_replace('/(\s|\x{3164}|\x{1160})+/u', ' ', Str::trim($search)), separator: ' ', escape: '\\'),
            fn (string $word): bool => filled($word),
        ));
    }

    /**
     * @param  Builder<Model>  $query
     */
    private static function applyPersonNameSearch(Builder $query, string $like): void
    {
        $table = $query->getModel()->getTable();

        $query
            ->where("{$table}.first_name", 'like', $like)
            ->orWhere("{$table}.last_name", 'like', $like)
            ->orWhere("{$table}.name", 'like', $like)
            ->orWhere("{$table}.email", 'like', $like);
    }

    private static function numericId(string $term): ?int
    {
        $normalized = ltrim($term, '#');

        if (! is_numeric($normalized)) {
            return null;
        }

        return (int) $normalized;
    }
}
