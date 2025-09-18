<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\StorySupport;

class LiveStreamDeleteLiveListener
{
    public function __construct(protected StoryRepositoryInterface $repository)
    {
    }

    public function handle(Content $model): ?bool
    {
        // Handle delete live video
        try {
            $story = $this->repository->getStoryByItem($model->entityId(), $model->entityType());
            if (!$story) {
                return false;
            }
            // Force delete
            $this->repository->delete($story->entityId());

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
