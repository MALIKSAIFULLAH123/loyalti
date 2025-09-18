<?php

namespace MetaFox\TourGuide\Observers;

use MetaFox\TourGuide\Models\Hidden;
use MetaFox\TourGuide\Models\Step;
use MetaFox\TourGuide\Models\TourGuide;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class TourGuideObserver.
 */
class TourGuideObserver
{
    public function deleted(TourGuide $model): void
    {
        $model->steps()->each(fn (Step $step) => $step->delete());
        $model->hidden()->each(fn (Hidden $hidden) => $hidden->delete());
    }
}
