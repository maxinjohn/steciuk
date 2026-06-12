<?php

namespace App\Enums;

use App\Models\User;

enum PublishStatus: string
{
    case Draft = 'draft';
    case PendingReview = 'pending_review';
    case Published = 'published';
    case Unpublished = 'unpublished';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::PendingReview => 'Pending review',
            self::Published => 'Published',
            self::Unpublished => 'Unpublished',
            self::Cancelled => 'Cancelled',
        };
    }

    /**
     * @return list<self>
     */
    public static function editorSelectable(): array
    {
        return [self::Draft, self::PendingReview];
    }

    /**
     * @return array<string, string>
     */
    public static function optionsFor(?User $user = null): array
    {
        $user ??= auth()->user();

        $cases = $user instanceof User && $user->canPublishContent()
            ? self::cases()
            : self::editorSelectable();

        return collect($cases)
            ->mapWithKeys(fn (self $status): array => [$status->value => $status->label()])
            ->all();
    }
}
