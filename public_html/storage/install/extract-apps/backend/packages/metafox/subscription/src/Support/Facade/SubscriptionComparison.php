<?php

namespace MetaFox\Subscription\Support\Facade;

use Illuminate\Support\Facades\Facade;
use MetaFox\Subscription\Contracts\SubscriptionComparisonContract;

class SubscriptionComparison extends Facade
{
    protected static function getFacadeAccessor()
    {
        return SubscriptionComparisonContract::class;
    }
}
