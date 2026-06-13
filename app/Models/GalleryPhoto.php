<?php

namespace App\Models;

use App\Enums\PublishStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GalleryPhoto extends Model
{
    use HasFactory;

    protected $fillable = [
        'gallery_album_id',
        'title',
        'caption',
        'image_path',
        'alt_text',
        'sort_order',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'sort_order' => 'integer',
            'status' => PublishStatus::class,
        ];
    }

    protected static function booted(): void
    {
        static::updating(function (GalleryPhoto $photo): void {
            if ($photo->isDirty('image_path')) {
                $original = $photo->getOriginal('image_path');

                if (filled($original) && $original !== $photo->image_path) {
                    \App\Support\GalleryImageProcessor::deleteStoredImage($original);
                }
            }
        });

        static::saved(function (GalleryPhoto $photo): void {
            if (
                filled($photo->image_path)
                && ($photo->wasRecentlyCreated || $photo->wasChanged('image_path'))
            ) {
                \App\Support\GalleryImageProcessor::processPhoto($photo->image_path);
            }

            \App\Services\SiteCache::forgetPublicContent();
        });

        static::deleted(function (GalleryPhoto $photo): void {
            \App\Support\GalleryImageProcessor::deleteStoredImage($photo->image_path);
            \App\Services\SiteCache::forgetPublicContent();
        });
    }

    public function album(): BelongsTo
    {
        return $this->belongsTo(GalleryAlbum::class, 'gallery_album_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', PublishStatus::Published->value);
    }
}
