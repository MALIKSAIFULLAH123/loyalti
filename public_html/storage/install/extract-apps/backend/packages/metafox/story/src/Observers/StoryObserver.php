<?php

namespace MetaFox\Story\Observers;

use MetaFox\Story\Models\Story;
use MetaFox\Story\Models\StoryReaction;

/**
 * stub: /packages/observers/model_observer.stub
 */

/**
 * Class StoryObserver
 *
 */
class StoryObserver
{
    public function created(Story $story): void
    {
        $story->storyBackground?->incrementTotalItem();
    }

    public function deleted(Story $story): void
    {
        $story->viewers()?->delete();

        $story->storyBackground?->decrementTotalItem();
        $storySet = $story->storySet;

        if ($storySet->expired_at == $story->expired_at) {
            $storyLasted = $storySet->stories()->orderByDesc('created_at')->first();

            if ($storyLasted instanceof Story) {
                $storySet->update([
                    'expired_at' => $storyLasted->expired_at,
                    'updated_at' => $storyLasted->updated_at,
                ]);
            }
        }
    }

    public function deleting(Story $story): void
    {
        $story->reactions()?->each(function (StoryReaction $reaction) {
            $reaction->delete();
        });
    }
}

// end stub
