<?php

namespace MetaFox\Subscription\Support\Browse\Traits\SubscriptionCancelReason;

trait StatisticTrait
{
    public function getStatistics(): array
    {
        return [
            'total_canceled' => (int) $this->resource->total_canceled,
        ];
    }
}
