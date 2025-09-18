<?php

namespace MetaFox\Subscription\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Subscription\Contracts\SubscriptionCancelReasonContract;

class SubscriptionCancelReason extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SubscriptionCancelReasonContract::class;
    }
}
