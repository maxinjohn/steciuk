<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Role extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_system',
        'grants_full_access',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'grants_full_access' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable('roles');
        } catch (\Throwable) {
            return false;
        }
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'role', 'slug');
    }

    public static function labelForSlug(?string $slug): string
    {
        if ($slug === null || $slug === '') {
            return 'Unknown';
        }

        if (static::tableExists()) {
            try {
                $name = static::query()->where('slug', $slug)->value('name');

                if (filled($name)) {
                    return (string) $name;
                }
            } catch (\Throwable) {
                // Fall back to legacy labels below.
            }
        }

        return static::legacyLabelForSlug($slug);
    }

    public static function legacyLabelForSlug(string $slug): string
    {
        return match ($slug) {
            UserRole::SuperAdmin->value => 'Super Admin',
            UserRole::Admin->value => 'Admin',
            UserRole::Editor->value => 'Editor',
            UserRole::Member->value => 'Member',
            default => Str::headline(str_replace('_', ' ', $slug)),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        if (! static::tableExists()) {
            return static::legacyOptions();
        }

        try {
            $options = static::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name', 'slug')
                ->all();

            return $options !== [] ? $options : static::legacyOptions();
        } catch (\Throwable) {
            return static::legacyOptions();
        }
    }

    /**
     * @return array<string, string>
     */
    public static function legacyOptions(): array
    {
        return [
            UserRole::SuperAdmin->value => 'Super Admin',
            UserRole::Admin->value => 'Admin',
            UserRole::Editor->value => 'Editor',
            UserRole::Member->value => 'Member',
        ];
    }

    public static function findBySlug(string $slug): ?self
    {
        if (! static::tableExists()) {
            return null;
        }

        try {
            return static::query()->where('slug', $slug)->first();
        } catch (\Throwable) {
            return null;
        }
    }
}
