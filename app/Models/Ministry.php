<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Ministry extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'name',
        'slug',
        'short_description',
        'description',
        'featured_image',
        'contact_person',
        'contact_email',
        'meeting_time',
        'sort_order',
        'status',
        'show_in_menu',
        'menu_parent_seed_key',
        'menu_label',
        'menu_sort_order',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'show_in_menu' => 'boolean',
            'menu_sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Ministry $ministry): void {
            if (empty($ministry->slug) && ! empty($ministry->name)) {
                $ministry->slug = static::generateUniqueSlug($ministry->name);
            }
        });

        static::saved(function (Ministry $ministry): void {
            \App\Services\SiteCache::forgetPublicContent();
            \App\Services\NavigationMenuSync::syncMinistry($ministry);
        });

        static::deleted(function (Ministry $ministry): void {
            \App\Services\SiteCache::forgetPublicContent();
            \App\Services\NavigationMenuSync::removeMinistry($ministry);
        });
    }

    public static function generateUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $slug = Str::slug($name);
        $originalSlug = $slug;
        $counter = 1;

        while (static::query()
            ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }
}
