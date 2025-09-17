<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Support\Facades\ActivityFeed;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasTotalFeed;
use MetaFox\Platform\Support\FeedAction;
use MetaFox\Platform\Contracts\User;

class ModelApprovedListener
{
    /**
     * @param  User|null $context
     * @param  Model     $model
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function handle(?User $context, Model $model): void
    {
        if (!$model instanceof Content) {
            return;
        }

        if ($model instanceof Feed) {
            $owner = $model->owner;
            if ($owner instanceof HasTotalFeed) {
                $owner->incrementAmount('total_feed');
            }
        }

        if (!$model instanceof ActivityFeedSource) {
            return;
        }

        $feed = Feed::query()->with('item')
            ->where([
                'item_id'   => $model->entityId(),
                'item_type' => $model->entityType(),
            ])->first();

        $feed = $feed?->from_resource == Feed::FROM_APP_RESOURCE ? $model->activity_feed : $feed;

        if (!$feed instanceof Feed) {
            $this->handleFeed($model);

            return;
        }

        if ($feed?->is_pending) {
            $feed->is_approved = true;
            $feed->save();
        }
    }

    protected function handleFeed(Model $model): void
    {
        if (!$model instanceof ActivityFeedSource) {
            return;
        }

        $model->loadMissing('activity_feed');
        $activityFeed = $model->activity_feed;
        if ($activityFeed instanceof Feed) {
            return;
        }

        $feedAction = $model->toActivityFeed();

        if (!$feedAction instanceof FeedAction) {
            return;
        }

        ActivityFeed::createActivityFeed($feedAction);
    }
}
