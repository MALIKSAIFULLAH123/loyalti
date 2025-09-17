<?php

namespace MetaFox\Advertise\Observers;

use MetaFox\Advertise\Models\Advertise;
use MetaFox\Advertise\Repositories\AdvertiseRepositoryInterface;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class AdvertiseObserver.
 */
class AdvertiseObserver
{
    public function deleted(Advertise $advertise)
    {
        resolve(AdvertiseRepositoryInterface::class)->deleteData($advertise);
    }
}

// end stub
