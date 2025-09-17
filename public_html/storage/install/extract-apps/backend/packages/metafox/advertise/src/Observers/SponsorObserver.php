<?php

namespace MetaFox\Advertise\Observers;

use MetaFox\Advertise\Models\Sponsor;
use MetaFox\Advertise\Repositories\SponsorRepositoryInterface;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class SponsorObserver.
 */
class SponsorObserver
{
    public function deleted(Sponsor $sponsor)
    {
        resolve(SponsorRepositoryInterface::class)->deleteData($sponsor);
    }
}

// end stub
