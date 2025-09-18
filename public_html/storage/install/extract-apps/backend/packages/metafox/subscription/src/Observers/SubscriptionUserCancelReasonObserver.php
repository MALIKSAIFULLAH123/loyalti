<?php

namespace MetaFox\Subscription\Observers;

use MetaFox\Subscription\Models\SubscriptionUserCancelReason;
use MetaFox\Subscription\Support\Facade\SubscriptionCancelReason;

/**
 * stub: /packages/observers/model_observer.stub.
 */

/**
 * Class SubscriptionCancelReasonObserver.
 */
class SubscriptionUserCancelReasonObserver
{
    public function created(SubscriptionUserCancelReason $userCancelReason)
    {
        if (null !== $userCancelReason->reason) {
            $userCancelReason->reason->incrementAmount('total_canceled');
            SubscriptionCancelReason::clearCaches();
        }
    }

    public function deleted(SubscriptionUserCancelReason $userCancelReason)
    {
        if (null !== $userCancelReason->reason) {
            $userCancelReason->reason->decrementAmount('total_canceled');
            SubscriptionCancelReason::clearCaches();
        }
    }
}
