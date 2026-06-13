<?php

namespace App\Models;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class GalleryAlbum extends Model implements HasMedia
{
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'slug',
        'description',
        'cover_image',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (GalleryAlbum $album): void {
            if (empty($album->slug) && ! empty($album->title)) {
                $album->slug = static::generateUniqueSlug($album->title);
            }
        });

        static::updating(function (GalleryAlbum $album): void {
            if ($album->isDirty('cover_image')) {
                $original = $album->getOriginal('cover_image');

                if (filled($original) && $original !== $album->cover_image) {
                    \App\Support\GalleryImageProcessor::deleteStoredImage($original);
                }
            }
        });

        static::deleting(function (GalleryAlbum $album): void {
            $album->photos()->each(function (GalleryPhoto $photo): void {
                \App\Support\GalleryImageProcessor::deleteStoredImage($photo->image_path);
            });
        });

        static::saved(function (GalleryAlbum $album): void {
            if (
                filled($album->cover_image)
                && ($album->wasRecentlyCreated || $album->wasChanged('cover_image'))
            ) {
                \App\Support\GalleryImageProcessor::processCover($album->cover_image);
            }

            \App\Services\SiteCache::forgetPublicContent();
        });

        static::deleted(function (GalleryAlbum $album): void {
            \App\Support\GalleryImageProcessor::deleteStoredImage($album->cover_image);
            \App\Services\SiteCache::forgetPublicContent();
        });
    }

    public static function generateUniqueSlug(string $title, ?int $ignoreId = null): string
    {
        $slug = Str::slug($title);
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

    public function photos(): HasMany
    {
        return $this->hasMany(GalleryPhoto::class)->orderBy('sort_order');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            PublishStatus::Published->value,
            'active',
        ]);
    }

    public function scopePublished($query)
    {
        return $this->scopeActive($query);
    }

    public function resolvedCoverPath(): ?string
    {
        if (filled($this->cover_image)) {
            return $this->cover_image;
        }

        if ($this->relationLoaded('photos')) {
            $photo = $this->photos->first();

            return $photo?->image_path;
        }

        $path = $this->photos()
            ->published()
            ->orderBy('sort_order')
            ->value('image_path');

        return is_string($path) && $path !== '' ? $path : null;
    }
}
