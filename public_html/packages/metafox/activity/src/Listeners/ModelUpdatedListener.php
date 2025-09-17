<?php

namespace MetaFox\Activity\Listeners;

use Illuminate\Database\Eloquent\Model;
use MetaFox\Activity\Models\Feed;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\HasApprove;
use MetaFox\Platform\Contracts\HasFeedContent;
use MetaFox\Platform\Contracts\HasPrivacy;

/**
 * Class ModelUpdatedListener.
 * @ignore
 */
class ModelUpdatedListener
{
    /**
     * @param mixed $model
     */
    public function handle($model): void
    {
        if (!$model instanceof Model) {
            return;
        }

        if (!$model instanceof ActivityFeedSource) {
            return;
        }

        if (!$model instanceof Content) {
            return;
        }

        $model->loadMissing('activity_feed');

        $feed = Feed::query()->with('item')
            ->where([
                'item_id'   => $model->entityId(),
                'item_type' => $model->entityType(),
            ])->first();

        $feed = $feed?->from_resource == Feed::FROM_APP_RESOURCE ? $model->activity_feed : $feed;
        if ($feed instanceof Feed) {
            if ($model instanceof HasPrivacy && $feed->privacy != $model->privacy) {
                $feed->privacy = $model->privacy;
            }

            if ($model instanceof HasFeedContent) {
                $feed->content = $model->getFeedContent();
            }

            if ($model instanceof HasApprove) {
                if ($model->isApproved() != $feed->isApproved()) {
                    $feed->is_approved = $model->isApproved();
                }
            }

            $feed->save();
        }
    }
}
