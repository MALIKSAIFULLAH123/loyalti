<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Models\LiveVideoText;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class ModelCreatedListener.
 * @ignore
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class ModelUpdatedListener
{
    public const ENTITY_LIKE               = 'like';
    public const ENTITY_COMMENT            = 'comment';

    /**
     * @param $model
     */
    public function handle($model): void
    {
        $this->handleLiveActions($model);
        $this->handleFeedStatus($model);
    }

    protected function getLiveVideoRepository(): LiveVideoRepositoryInterface
    {
        return resolve(LiveVideoRepositoryInterface::class);
    }

    /**
     * @param $model
     * @return void
     */
    protected function handleLiveActions($model): void
    {
        if (!$model instanceof ActionEntity) {
            return;
        }
        $entityType = $model->entityType();
        if (!($entityType == self::ENTITY_LIKE || $entityType == self::ENTITY_COMMENT)) {
            return;
        }

        if ($model->itemType() !== LiveVideo::ENTITY_TYPE &&
            (($model->itemType() !== self::ENTITY_COMMENT) || ($model->item?->itemType() !== LiveVideo::ENTITY_TYPE))) {
            return;
        }

        $liveVideoId = $model->item_id;

        if ($model->itemType() === self::ENTITY_COMMENT) {
            $liveVideoId = $model->item->item_id;
        }

        $liveVideo = $this->getLiveVideoRepository()->find($liveVideoId);

        if (!$liveVideo || !$liveVideo->is_streaming) {
            return;
        }

        if ($entityType == self::ENTITY_LIKE) {
            if ($model->itemType() === self::ENTITY_COMMENT && $model->item instanceof ActionEntity) {
                $this->getLiveVideoRepository()->updateLiveComment($liveVideo, $model->item);

                return;
            }
            $this->getLiveVideoRepository()->addLiveLike($liveVideo, $model);

            return;
        }

        $this->getLiveVideoRepository()->updateLiveComment($liveVideo, $model);
    }

    protected function handleFeedStatus($model): void
    {
        if (!$model instanceof LiveVideoText) {
            return;
        }

        $content = $model->resource;
        if (!$content instanceof LiveVideo) {
            return;
        }

        $content->loadMissing('activity_feed');
        $feed = !empty($content->activity_feed) ? $content->activity_feed : false;
        if (!$feed instanceof Content) {
            return;
        }

        $feed->content = $model->text;
        $feed->save();

        // Update hashtag
        app('events')->dispatch('hashtag.create_hashtag', [$content->user, $feed, $feed->content], true);
    }
}
