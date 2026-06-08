<?php

namespace App\Models;

use App\Enums\MenuLocation;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MenuItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'url',
        'page_id',
        'parent_id',
        'menu_location',
        'seed_key',
        'target',
        'sort_order',
        'is_visible',
        'is_external',
    ];

    protected function casts(): array
    {
        return [
            'menu_location' => MenuLocation::class,
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
            'is_external' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saved(static fn () => \App\Services\MenuCache::forgetAll());
        static::deleted(static fn () => \App\Services\MenuCache::forgetAll());
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(MenuItem::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(MenuItem::class, 'parent_id')->orderBy('sort_order');
    }
}
