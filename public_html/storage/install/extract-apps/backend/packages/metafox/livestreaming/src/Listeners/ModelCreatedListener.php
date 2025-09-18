<?php

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Models\LiveVideoText;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Contracts\Entity;

/**
 * Class ModelCreatedListener.
 * @ignore
 * @codeCoverageIgnore
 */
class ModelCreatedListener
{
    public const ENTITY_LIKE               = 'like';
    public const ENTITY_COMMENT            = 'comment';
    public const ENTITY_COMMENT_ATTACHMENT = 'comment_attachments';
    /**
     * @param $model
     * @return void
     */
    public function handle($model)
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
        if (!$model instanceof Entity) {
            return;
        }
        $entityType = $model->entityType();
        if (!($entityType == self::ENTITY_LIKE || $entityType == self::ENTITY_COMMENT || $entityType == self::ENTITY_COMMENT_ATTACHMENT)) {
            return;
        }

        $isCommentAttachment = false;
        if ($entityType == self::ENTITY_COMMENT_ATTACHMENT) {
            $attachment = $model;
            $comment    = $model?->comment;
            if (!$comment || $comment->item_type != LiveVideo::ENTITY_TYPE) {
                return;
            }
            $isCommentAttachment      = true;
            $model                    = $comment;
            $model->commentAttachment = $attachment;
        }
        if ($model->itemType() !== LiveVideo::ENTITY_TYPE &&
            (($model->itemType() !== self::ENTITY_COMMENT) || ($model?->item?->itemType() !== LiveVideo::ENTITY_TYPE))) {
            return;
        }

        $liveVideoId = $model->item_id;

        if ($model->itemType() === self::ENTITY_COMMENT) {
            $liveVideoId = $model?->item->item_id;
        }

        $liveVideo = $this->getLiveVideoRepository()->find($liveVideoId);

        if (!$liveVideo || !$liveVideo->is_streaming) {
            return;
        }
        if ($isCommentAttachment) {
            $this->getLiveVideoRepository()->updateLiveComment($liveVideo, $model);

            return;
        }
        if ($model->entityType() == self::ENTITY_LIKE) {
            if ($model->itemType() === self::ENTITY_COMMENT) {
                $this->getLiveVideoRepository()->updateLiveComment($liveVideo, $model->item);

                return;
            }
            $this->getLiveVideoRepository()->addLiveLike($liveVideo, $model);

            return;
        }
        $this->getLiveVideoRepository()->addLiveComment($liveVideo, $model);
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
