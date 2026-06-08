<?php

namespace App\Models;

use App\Enums\PublishStatus;
use App\Models\Concerns\HasAuditFields;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Sermon extends Model implements HasMedia
{
    use HasAuditFields;
    use HasFactory;
    use InteractsWithMedia;

    protected $fillable = [
        'title',
        'speaker',
        'preached_at',
        'bible_passage',
        'description',
        'youtube_url',
        'category',
        'status',
        'created_by',
        'updated_by',
    ];

    protected function casts(): array
    {
        return [
            'preached_at' => 'date',
            'status' => PublishStatus::class,
        ];
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', PublishStatus::Published);
    }

    protected static function booted(): void
    {
        static::saved(fn () => \App\Services\SiteCache::forgetPublicContent());
        static::deleted(fn () => \App\Services\SiteCache::forgetPublicContent());
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('audio')->singleFile();
        $this->addMediaCollection('pdf')->singleFile();
    }
}
