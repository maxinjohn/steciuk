<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Page extends Model implements HasMedia
{
    use HasAuditFields;
    use HasFactory;
    use InteractsWithMedia;
    use SoftDeletes;

    protected $fillable = [
        'title',
        'slug',
        'hero_title',
        'hero_subtitle',
        'featured_image',
        'content',
        'custom_css',
        'custom_js',
        'seo_title',
        'seo_description',
        'meta_robots',
        'og_image',
        'status',
        'sort_order',
        'template',
        'accent_color',
        'layout_variant',
        'hero_style',
        'show_hero',
        'is_home',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'status' => PublishStatus::class,
            'sort_order' => 'integer',
            'is_home' => 'boolean',
            'show_hero' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (Page $page): void {
            if (empty($page->slug) && ! empty($page->title)) {
                $page->slug = static::generateUniqueSlug($page->title);
            }
        });

        static::updating(function (Page $page): void {
            if ($page->isDirty('title') && ! $page->isDirty('slug')) {
                $page->slug = static::generateUniqueSlug($page->title, $page->id);
            }
        });

        static::saved(function (Page $page): void {
            \App\Services\SiteCache::forgetPageContext($page->slug);
            \App\Services\SiteCache::forgetSitemap();

            if ($page->is_home) {
                \App\Services\HomePageData::forget();
            }
        });

        static::saving(function (Page $page): void {
            if ($page->isDirty('custom_css')) {
                $page->custom_css = \App\Support\CustomAssetSanitizer::css($page->custom_css);
            }

            if ($page->isDirty('custom_js')) {
                $page->custom_js = \App\Support\CustomAssetSanitizer::js($page->custom_js);
            }

            \App\Support\PageTopicArt::syncHeroStyleForTopicArt($page);
        });

        static::deleted(function (Page $page): void {
            \App\Services\SiteCache::forgetPageContext($page->slug);
            \App\Services\SiteCache::forgetSitemap();

            if ($page->is_home) {
                \App\Services\HomePageData::forget();
            }
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
        $originalSlug = $slug;
        $counter = 1;

        while (static::withTrashed()
            ->when($ignoreId, fn (Builder $query) => $query->where('id', '!=', $ignoreId))
            ->where('slug', $slug)
            ->exists()) {
            $slug = $originalSlug.'-'.$counter;
            $counter++;
        }

        return $slug;
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published);
    }

    public function contentBlocks(): HasMany
    {
        return $this->hasMany(ContentBlock::class)->orderBy('sort_order');
    }

    public function hasHeroContentBlock(): bool
    {
        if (! $this->relationLoaded('contentBlocks')) {
            return $this->contentBlocks()
                ->where('is_visible', true)
                ->where('type', 'hero')
                ->exists();
        }

        return $this->contentBlocks
            ->contains(fn ($block) => ($block->type->value ?? $block->type) === 'hero');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function menuItems(): HasMany
    {
        return $this->hasMany(MenuItem::class);
    }

    public function publicUrl(): string
    {
        if ($this->is_home) {
            return url('/');
        }

        return url('/'.$this->slug);
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('featured')->singleFile();
        $this->addMediaCollection('og')->singleFile();
    }
}
