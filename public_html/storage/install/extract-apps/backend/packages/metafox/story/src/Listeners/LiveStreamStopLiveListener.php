<?php

namespace MetaFox\Story\Listeners;

use MetaFox\Platform\Contracts\Content;
use MetaFox\Platform\Support\Eloquent\Appends\Contracts\AppendPrivacyList;
use MetaFox\Story\Repositories\StoryRepositoryInterface;
use MetaFox\Story\Support\StorySupport;

class LiveStreamStopLiveListener
{
    public function __construct(protected StoryRepositoryInterface $repository)
    {
    }

    public function handle(Content $model): ?bool
    {
        try {
            $story = $this->repository->getStoryByItem($model->entityId(), $model->entityType());
            if (!$story) {
                return false;
            }
            $story->extra = array_merge($story->extra, [
                'is_streaming' => false,
            ]);
            $story->save();

            return true;
        } catch (\Exception $e) {
            return false;
        }
    }
}
