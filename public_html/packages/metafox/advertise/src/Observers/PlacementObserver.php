<?php

namespace MetaFox\Advertise\Observers;

use MetaFox\Advertise\Models\Placement;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class PlacementObserver.
 */
class PlacementObserver
{
    public function forceDeleted(Placement $placement)
    {
        $placement->placementText()->delete();
    }
}

// end stub
