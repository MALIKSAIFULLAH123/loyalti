<?php

namespace MetaFox\ActivityPoint\Observers;

use MetaFox\ActivityPoint\Models\ConversionRequest;
use MetaFox\ActivityPoint\Models\ConversionStatistic;

/**
 * stub: /packages/observers/model_observer.stub
 */

/**
 * Class ConversionRequestObserver
 *
 */
class ConversionRequestObserver
{
    public function created(ConversionRequest $request): void
    {
        if (!$request->is_pending || !$request->statistic instanceof ConversionStatistic) {
            return;
        }

        $request->statistic->incrementAmount('total_pending', $request->points);
    }

    public function updated(ConversionRequest $request): void
    {
        if (!$request->isDirty('status') || !$request->statistic instanceof ConversionStatistic) {
            return;
        }

        $request->statistic->decrementAmount('total_pending', $request->points);

        if (!$request->is_approved) {
            return;
        }

        $request->statistic->incrementAmount('total_converted', $request->points);
    }
}

// end stub
