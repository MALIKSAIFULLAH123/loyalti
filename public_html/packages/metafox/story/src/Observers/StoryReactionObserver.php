<?php

namespace MetaFox\Story\Observers;

use MetaFox\Story\Models\StoryReaction;
use MetaFox\Story\Models\StoryReactionData;
use MetaFox\Story\Repositories\StoryReactionRepositoryInterface;

/**
 * stub: /packages/observers/model_observer.stub
 */

/**
 * Class StoryReactionObserver
 *
 */
class StoryReactionObserver
{
    public function created(StoryReaction $reaction): void
    {
        $reaction->story->incrementAmount('total_like');
    }

    public function deleting(StoryReaction $reaction): void
    {
        resolve(StoryReactionRepositoryInterface::class)->deleteNotification($reaction);
    }

    public function deleted(StoryReaction $reaction): void
    {
        $reaction->reactionData?->each(function (StoryReactionData $reactionData) {
            $reactionData->delete();
        });
    }
}

// end stub
