<?php

namespace App\Filament\Support;

use App\Enums\PublishStatus;
use App\Models\User;
use App\Services\SecurityLogger;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Support\Exceptions\Halt;
use Illuminate\Database\Eloquent\Model;

class PublishWorkflowActions
{
    /**
     * @param  callable(): Model  $recordResolver
     * @param  callable(Model): ?string|null  $previewUrlResolver
     * @return list<Action|DeleteAction>
     */
    public static function headerActions(callable $recordResolver, ?callable $previewUrlResolver = null): array
    {
        return array_filter([
            self::previewAction($recordResolver, $previewUrlResolver),
            self::submitForReviewAction($recordResolver),
            self::approveAndPublishAction($recordResolver),
            self::returnReviewToDraftAction($recordResolver),
            self::publishAction($recordResolver),
            self::unpublishAction($recordResolver),
            self::revertToDraftAction($recordResolver),
            self::deleteAction($recordResolver),
        ]);
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function previewAction(callable $recordResolver, ?callable $previewUrlResolver): ?Action
    {
        if (! $previewUrlResolver) {
            return null;
        }

        return Action::make('previewPublic')
            ->label('Preview on site')
            ->icon('heroicon-o-eye')
            ->color('gray')
            ->url(function () use ($recordResolver, $previewUrlResolver): ?string {
                /** @var Model $record */
                $record = $recordResolver();
                $status = self::status($record);

                if ($status !== PublishStatus::Published) {
                    return null;
                }

                return $previewUrlResolver($record);
            })
            ->openUrlInNewTab()
            ->visible(function () use ($recordResolver, $previewUrlResolver): bool {
                if (! $previewUrlResolver) {
                    return false;
                }

                /** @var Model $record */
                $record = $recordResolver();

                return self::status($record) === PublishStatus::Published
                    && filled($previewUrlResolver($record));
            });
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function submitForReviewAction(callable $recordResolver): Action
    {
        return Action::make('submitForReview')
            ->label('Submit for review')
            ->icon('heroicon-o-paper-airplane')
            ->color('info')
            ->requiresConfirmation()
            ->modalHeading('Submit for parish review?')
            ->modalDescription('An admin or vicar will review this before it goes live on the public site.')
            ->visible(function () use ($recordResolver): bool {
                if (self::canPublish()) {
                    return false;
                }

                /** @var Model $record */
                $record = $recordResolver();
                $status = self::status($record);

                return in_array($status, [PublishStatus::Draft, PublishStatus::Unpublished], true);
            })
            ->action(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $record->update(['status' => PublishStatus::PendingReview->value]);

                SecurityLogger::audit('content_submitted_for_review', actor: auth()->user(), subject: $record, context: [
                    'content_type' => class_basename($record),
                    'content_id' => $record->getKey(),
                    'title' => (string) ($record->title ?? $record->getKey()),
                ]);

                app(\App\Services\ContentApprovalService::class)->forgetPendingCountCache();

                Notification::make()
                    ->title('Submitted for review')
                    ->body('Parish leadership will review this before publishing.')
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function approveAndPublishAction(callable $recordResolver): Action
    {
        return Action::make('approveAndPublish')
            ->label('Approve & publish')
            ->icon('heroicon-o-check-badge')
            ->color('success')
            ->requiresConfirmation()
            ->visible(function () use ($recordResolver): bool {
                if (! self::canPublish()) {
                    return false;
                }

                return self::status($recordResolver()) === PublishStatus::PendingReview;
            })
            ->action(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $approver = auth()->user();

                if ($approver instanceof User) {
                    app(\App\Services\ContentApprovalService::class)->approve($record::class, (int) $record->getKey(), $approver);
                }

                Notification::make()
                    ->title('Approved and published')
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function returnReviewToDraftAction(callable $recordResolver): Action
    {
        return Action::make('returnReviewToDraft')
            ->label('Return to editor')
            ->icon('heroicon-o-arrow-uturn-left')
            ->color('warning')
            ->requiresConfirmation()
            ->modalDescription('The editor can revise this and submit again.')
            ->visible(function () use ($recordResolver): bool {
                if (! self::canPublish()) {
                    return false;
                }

                return self::status($recordResolver()) === PublishStatus::PendingReview;
            })
            ->action(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $approver = auth()->user();

                if ($approver instanceof User) {
                    app(\App\Services\ContentApprovalService::class)->returnToDraft($record::class, (int) $record->getKey(), $approver);
                }

                Notification::make()
                    ->title('Returned to draft')
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function publishAction(callable $recordResolver): Action
    {
        return Action::make('publish')
            ->label('Publish')
            ->icon('heroicon-o-arrow-up-circle')
            ->color('success')
            ->requiresConfirmation()
            ->modalHeading('Publish to the public site?')
            ->modalDescription('Visitors will be able to see this on the website.')
            ->visible(function () use ($recordResolver): bool {
                /** @var Model $record */
                $record = $recordResolver();
                $status = self::status($record);

                return self::canPublish()
                    && in_array($status, [PublishStatus::Draft, PublishStatus::Unpublished], true);
            })
            ->action(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $record->update(['status' => PublishStatus::Published->value]);

                Notification::make()
                    ->title('Published')
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function unpublishAction(callable $recordResolver): Action
    {
        return Action::make('unpublish')
            ->label('Unpublish')
            ->icon('heroicon-o-arrow-down-circle')
            ->color('warning')
            ->requiresConfirmation()
            ->modalHeading('Remove from the public site?')
            ->modalDescription('This stays in admin as unpublished. You must unpublish before deleting (unless you are a super admin).')
            ->visible(function () use ($recordResolver): bool {
                /** @var Model $record */
                $record = $recordResolver();

                return self::canPublish()
                    && self::status($record) === PublishStatus::Published;
            })
            ->action(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $record->update(['status' => PublishStatus::Unpublished->value]);

                Notification::make()
                    ->title('Unpublished')
                    ->success()
                    ->send();
            });
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function revertToDraftAction(callable $recordResolver): Action
    {
        return Action::make('markDraft')
            ->label('Mark as draft')
            ->icon('heroicon-o-document')
            ->color('gray')
            ->requiresConfirmation()
            ->visible(function () use ($recordResolver): bool {
                /** @var Model $record */
                $record = $recordResolver();
                $status = self::status($record);

                return self::canPublish()
                    && $status === PublishStatus::Unpublished;
            })
            ->action(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $record->update(['status' => PublishStatus::Draft->value]);

                Notification::make()
                    ->title('Saved as draft')
                    ->success()
                    ->send();
            });
    }

    public static function haltPublishedDelete(): void
    {
        Notification::make()
            ->title('Unpublish first')
            ->body('Published content must be unpublished before it can be deleted.')
            ->danger()
            ->send();

        throw new Halt;
    }

    /**
     * @param  callable(): Model  $recordResolver
     */
    private static function deleteAction(callable $recordResolver): DeleteAction
    {
        return DeleteAction::make()
            ->visible(fn (): bool => auth()->user()?->can('delete', $recordResolver()) ?? false)
            ->before(function () use ($recordResolver): void {
                /** @var Model $record */
                $record = $recordResolver();
                $status = self::status($record);
                $actor = auth()->user();

                if ($actor instanceof User && $actor->isSuperAdmin()) {
                    return;
                }

                if ($status === PublishStatus::Published) {
                    self::haltPublishedDelete();
                }
            });
    }

    private static function canPublish(): bool
    {
        $user = auth()->user();

        return $user instanceof User && $user->canPublishContent();
    }

    private static function status(Model $record): ?PublishStatus
    {
        $status = $record->getAttribute('status');

        if ($status instanceof PublishStatus) {
            return $status;
        }

        if (is_string($status) && $status !== '') {
            return PublishStatus::tryFrom($status);
        }

        return null;
    }
}
