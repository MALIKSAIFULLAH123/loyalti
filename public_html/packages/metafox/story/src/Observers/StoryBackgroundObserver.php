<?php

namespace MetaFox\Story\Observers;

use MetaFox\Story\Models\StoryBackground;
use MetaFox\Story\Repositories\BackgroundSetRepositoryInterface;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class StoryBackgroundObserver.
 */
class StoryBackgroundObserver
{
    public function created(StoryBackground $background): void
    {
        $backgroundSet = $background->backgroundSet;
        if (method_exists($backgroundSet, 'incrementAmount')) {
            $backgroundSet->incrementAmount('total_background');
        }

        if ($backgroundSet->main_background_id == 0) {
            $this->bgSetRepository()->updateMainBackground(
                $backgroundSet,
                $background->entityId()
            );
        }
    }

    public function updated(StoryBackground $background): void
    {
        if ($background->wasChanged(['is_deleted'])) {
            $backgroundSet = $background->backgroundSet;
            if (method_exists($backgroundSet, 'decrementAmount')) {
                $backgroundSet->decrementAmount('total_background');
            }

            if ($backgroundSet->main_background_id == $background->entityId()) {
                $this->bgSetRepository()->updateMainBackground($backgroundSet);
            }
        }
    }

    protected function bgSetRepository(): BackgroundSetRepositoryInterface
    {
        return resolve(BackgroundSetRepositoryInterface::class);
    }
}

// end stub
