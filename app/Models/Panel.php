<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

class Panel extends Model
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
            return Schema::hasTable('panels');
        } catch (\Throwable) {
            return false;
        }
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['sort_order', 'notes'])
            ->withTimestamps()
            ->orderByPivot('sort_order')
            ->orderBy('users.last_name')
            ->orderBy('users.first_name');
    }

    public function isNameLocked(): bool
    {
        return (bool) $this->is_system;
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

    public static function slugFromName(string $name): string
    {
        return Str::slug($name);
    }
}
