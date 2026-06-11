<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Designation extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'is_system',
        'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    public static function tableExists(): bool
    {
        try {
            return Schema::hasTable('designations');
        } catch (\Throwable) {
            return false;
        }
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function isNameLocked(): bool
    {
        return (bool) $this->is_system;
    }

    public static function labelForId(?int $id): ?string
    {
        if (! $id || ! static::tableExists()) {
            return null;
        }

        try {
            return static::query()->whereKey($id)->value('name');
        } catch (\Throwable) {
            return null;
        }
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        if (! static::tableExists()) {
            return [];
        }

        try {
            return static::query()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->pluck('name', 'id')
                ->all();
        } catch (\Throwable) {
            return [];
        }
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

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }
}
