<?php

namespace MetaFox\LiveStreaming\Listeners;

use MetaFox\LiveStreaming\Models\LiveVideo;
use MetaFox\LiveStreaming\Repositories\LiveVideoRepositoryInterface;
use MetaFox\Platform\Contracts\ActionEntity;
use MetaFox\Platform\Contracts\Entity;

class ModelDeletedListener
{
    public const ENTITY_LIKE               = 'like';
    public const ENTITY_COMMENT            = 'comment';
    /**
     * @param $model
     * @return void
     */
    public function handle($model): void
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
            $liveVideoId = $model->item?->item_id;
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
            $this->getLiveVideoRepository()->removeLiveLike($liveVideo, $model);

            return;
        }
        $this->getLiveVideoRepository()->removeLiveComment($liveVideo, $model);
    }

    protected function getLiveVideoRepository(): LiveVideoRepositoryInterface
    {
        return resolve(LiveVideoRepositoryInterface::class);
    }
}
