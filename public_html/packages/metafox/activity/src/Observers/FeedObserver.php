<?php

namespace MetaFox\Activity\Observers;

use Illuminate\Support\Arr;
use MetaFox\Activity\Models\ActivityHistory;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Repositories\FeedRepositoryInterface;
use MetaFox\Activity\Repositories\PinRepositoryInterface;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTaggedFriend;
use MetaFox\Platform\MetaFoxConstant;

/**
 * Class FeedObserver.
 */
class FeedObserver
{
    public function __construct(protected FeedRepositoryInterface $repository)
    {
    }

    /**
     * Handle the ActivityFeed "created" event.
     *
     * @param Feed $model
     */
    public function created(Feed $model): void
    {
        ActivityFeed::putToStream($model);

        app('events')->dispatch('search.created', [$model], true);
    }

    /**
     * Handle the ActivityFeed "updated" event.
     *
     * @param Feed $model
     */
    public function updated(Feed $model): void
    {
        $attributes = [];
        $status     = $model->stream()->groupBy('status', 'owner_id')->pluck('status', 'owner_id')->toArray();
        Arr::set($attributes, 'mapping_status_by_owner', $status);

        $modelItem = $model->item;
        $modelUser = $model->user;
        $model->stream()->delete();

        ActivityFeed::putToStream($model, $attributes);

        if ($modelItem instanceof HasTaggedFriend) {
            app('events')->dispatch('friend.re_put_feed_tag_stream', [$modelUser, $modelItem, $attributes], true);
        }

        app('events')->dispatch('search.updated', [$model], true);

        if ($model->status == MetaFoxConstant::ITEM_STATUS_REMOVED) {
            $this->repository->handleRemoveNotification('activity_feed_approved', $model->entityId(), $model->entityType());
        }
    }

    /**
     * Handle the ActivityFeed "deleted" event.
     *
     * @param Feed $model
     */
    public function deleted(Feed $model): void
    {
        $model->stream()->delete();

        $model->tagData()->sync([]);

        app('events')->dispatch('search.deleted', [$model], true);

        //Delete notification types: activity_feed_approved, activity_feed_declined
        app('events')->dispatch(
            'notification.delete_mass_notification_by_item',
            [$model],
            true
        );

        $model->history()->each(function (ActivityHistory $activityHistory) {
            $activityHistory->delete();
        });

        $model->pinned()->delete();

        resolve(PinRepositoryInterface::class)->clearCache();

        $this->disableItemSponsorInFeed($model);
    }

    protected function disableItemSponsorInFeed(Feed $model): void
    {
        if (!$model->is_sponsor) {
            return;
        }

        app('events')->dispatch('advertise.sponsor.delete_by_item', [$model]);

        /*
         * We let feed controlling disable sponsor_in_feed of Item
         */
        if ($model->relationLoaded('item')) {
            $model->load('item');
        }

        if (!$model->item instanceof Content) {
            return;
        }

        app('events')->dispatch('advertise.sponsor.disable_sponsor_feed', [$model->item]);
    }
}
