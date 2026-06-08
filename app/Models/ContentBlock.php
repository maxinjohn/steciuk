<?php

namespace App\Models;

use App\Enums\ContentBlockType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentBlock extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'seed_key',
        'type',
        'title',
        'content',
        'sort_order',
        'is_visible',
    ];

    protected function casts(): array
    {
        return [
            'type' => ContentBlockType::class,
            'content' => 'array',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    public function page(): BelongsTo
    {
        return $this->belongsTo(Page::class);
    }

    protected static function booted(): void
    {
        $forgetHomeCache = function (ContentBlock $block): void {
            $block->loadMissing('page:id,is_home');

            if ($block->page?->is_home) {
                \App\Services\HomePageData::forget();
            }

            if ($block->page?->slug) {
                \App\Services\SiteCache::forgetPageContext($block->page->slug);
            }

            \App\Services\SiteCache::forgetSitemap();
        };

        static::saved($forgetHomeCache);
        static::deleted($forgetHomeCache);
    }
}
