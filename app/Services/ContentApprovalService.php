<?php

namespace App\Services;

use App\Enums\PublishStatus;
use App\Models\Event;
use App\Models\GalleryAlbum;
use App\Models\GalleryPhoto;
use App\Models\News;
use App\Models\Page;
use App\Models\Sermon;
use App\Models\User;
use Illuminate\Support\Collection;

class ContentApprovalService
{
    /**
     * @return Collection<int, array{
     *     key: string,
     *     type: string,
     *     title: string,
     *     status: string,
     *     updated_at: \Illuminate\Support\Carbon,
     *     edit_url: string,
     *     model_class: class-string,
     *     model_id: int,
     * }>
     */
    public function pendingItems(): Collection
    {
        $items = collect();

        $this->collectFrom($items, Page::class, 'Page', fn (Page $page): string => $page->title, 'pages');
        $this->collectFrom($items, News::class, 'News', fn (News $news): string => $news->title, 'news');
        $this->collectFrom($items, Event::class, 'Event', fn (Event $event): string => $event->title, 'events');
        $this->collectFrom($items, Sermon::class, 'Sermon', fn (Sermon $sermon): string => $sermon->title, 'sermons');
        $this->collectFrom($items, GalleryAlbum::class, 'Gallery album', fn (GalleryAlbum $album): string => $album->title, 'gallery-albums');
        $this->collectFrom($items, GalleryPhoto::class, 'Gallery photo', fn (GalleryPhoto $photo): string => $photo->title ?: 'Photo #'.$photo->id, 'gallery-photos');

        return $items->sortByDesc('updated_at')->values();
    }

    public function pendingCount(): int
    {
        return $this->pendingItems()->count();
    }

    public function approve(string $modelClass, int $modelId, User $approver): void
    {
        abort_unless($approver->canPublishContent(), 403);

        $record = $this->findRecord($modelClass, $modelId);
        abort_unless($this->isPendingReview($record), 422, 'This item is not awaiting review.');

        $record->update(['status' => PublishStatus::Published->value]);

        SecurityLogger::audit('content_approved', actor: $approver, subject: $record, context: [
            'content_type' => class_basename($modelClass),
            'content_id' => $modelId,
            'title' => $this->titleFor($record),
        ]);
    }

    public function returnToDraft(string $modelClass, int $modelId, User $approver): void
    {
        abort_unless($approver->canPublishContent(), 403);

        $record = $this->findRecord($modelClass, $modelId);
        abort_unless($this->isPendingReview($record), 422, 'This item is not awaiting review.');

        $record->update(['status' => PublishStatus::Draft->value]);

        SecurityLogger::audit('content_review_returned', actor: $approver, subject: $record, context: [
            'content_type' => class_basename($modelClass),
            'content_id' => $modelId,
            'title' => $this->titleFor($record),
        ]);
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $items
     * @param  class-string  $modelClass
     * @param  callable(object): string  $titleResolver
     */
    private function collectFrom(
        Collection $items,
        string $modelClass,
        string $typeLabel,
        callable $titleResolver,
        string $resourceSlug,
    ): void {
        $modelClass::query()
            ->where('status', PublishStatus::PendingReview->value)
            ->orderByDesc('updated_at')
            ->each(function ($record) use ($items, $modelClass, $typeLabel, $titleResolver, $resourceSlug): void {
                $resourceClass = match ($modelClass) {
                    Page::class => \App\Filament\Resources\Pages\PageResource::class,
                    News::class => \App\Filament\Resources\News\NewsResource::class,
                    Event::class => \App\Filament\Resources\Events\EventResource::class,
                    Sermon::class => \App\Filament\Resources\Sermons\SermonResource::class,
                    GalleryAlbum::class => \App\Filament\Resources\GalleryAlbums\GalleryAlbumResource::class,
                    GalleryPhoto::class => \App\Filament\Resources\GalleryPhotos\GalleryPhotoResource::class,
                    default => null,
                };

                if ($resourceClass === null) {
                    return;
                }

                $items->push([
                    'key' => $modelClass.':'.$record->id,
                    'type' => $typeLabel,
                    'title' => $titleResolver($record),
                    'status' => PublishStatus::PendingReview->label(),
                    'updated_at' => $record->updated_at,
                    'edit_url' => $resourceClass::getUrl('edit', ['record' => $record]),
                    'model_class' => $modelClass,
                    'model_id' => $record->id,
                ]);
            });
    }

    /**
     * @param  class-string  $modelClass
     */
    private function findRecord(string $modelClass, int $modelId): object
    {
        abort_unless(in_array($modelClass, [
            Page::class,
            News::class,
            Event::class,
            Sermon::class,
            GalleryAlbum::class,
            GalleryPhoto::class,
        ], true), 404);

        return $modelClass::query()->findOrFail($modelId);
    }

    private function isPendingReview(object $record): bool
    {
        $status = $record->status ?? null;

        if ($status instanceof PublishStatus) {
            return $status === PublishStatus::PendingReview;
        }

        return (string) $status === PublishStatus::PendingReview->value;
    }

    private function titleFor(object $record): string
    {
        return (string) ($record->title ?? ('#'.$record->id));
    }
}
