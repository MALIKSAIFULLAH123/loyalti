<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\StorySupport;

class LiveStreamUpdatedAssetListener
{
    public function __construct(protected StoryRepositoryInterface $repository)
    {
    }

    public function handle(Content $model, array $thumbnail, string $playback): ?bool
    {
        try {
            $story = $this->repository->getStoryByItem($model->entityId(), $model->entityType());
            if (!$story) {
                return false;
            }
            $story->extra = array_merge($story->extra, [
                'image' => $thumbnail,
                'video' => $playback,
            ]);
            $story->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
