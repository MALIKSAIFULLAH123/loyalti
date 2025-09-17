<?php

namespace MetaFox\Story\Observers;

use MetaFox\Story\Models\StoryView;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class StoryViewObserver.
 */
class StoryViewObserver
{
    public function created(StoryView $view): void
    {
        if ($view->userId() == $view->story->userId()) {
            return;
        }
        
        $view->story->incrementTotalView();
    }
}

// end stub
