<?php

namespace MetaFox\Activity\Listeners;

use MetaFox\Activity\Contracts\TypeManager;
use MetaFox\Activity\Models\Feed;
use MetaFox\Activity\Models\Type;
use MetaFox\Platform\Contracts\ActivityFeedSource;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\HasTotalComment;
use MetaFox\Platform\Contracts\HasTotalCommentWithReply;
use MetaFox\Platform\Contracts\HasTotalLike;
use MetaFox\Platform\Contracts\HasTotalShare;
use MetaFox\Platform\Contracts\HasTotalView;

/**
 * Class FeedRedundantListener.
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 * @ignore
 */
class FeedRedundantListener
{
    /**
     * @param mixed $model
     */
    public function handle($model): void
    {
        if (!$model instanceof Entity) {
            return;
        }

        // If
        if ($model instanceof Feed) {
            return;
        }

        if (!$model instanceof ActivityFeedSource) {
            return;
        }

        $feed = $model->activity_feed;

        // If this feed already deleted.
        if (!$feed instanceof Feed) {
            return;
        }

        // If all actions is on feed, no need to redundant data from content resource to feed.
        if ($this->getTypeManager()->hasFeature($feed->type_id, Type::ACTION_ON_FEED_TYPE)) {
            return;
        }

        if ($model instanceof HasTotalLike) {
            $feed->total_like = $model->total_like;
        }

        if ($model instanceof HasTotalComment) {
            $feed->total_comment = $model->total_comment;
            if ($model instanceof HasTotalCommentWithReply) {
                $feed->total_reply = $model->total_reply;
            }
        }

        if ($model instanceof HasTotalShare) {
            $feed->total_share = $model->total_share;
        }

        if ($model instanceof HasTotalView) {
            $feed->total_view = $model->total_view;
        }

        /*
         *  perf/p5 optimize.
         */
        $feed->timestamps = false;
        $feed->saveQuietly();
    }

    protected function getTypeManager(): TypeManager
    {
        return resolve(TypeManager::class);
    }
}
