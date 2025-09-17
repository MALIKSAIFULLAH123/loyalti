<?php

namespace MetaFox\Subscription\Support\Browse\Traits\SubscriptionPackage;

trait StatisticTrait
{
    public function getStatistics(): array
    {
        return [
            'total_success' => $this->resource->total_success,
            'total_pending' => $this->resource->total_pending,
            'total_canceled' => $this->resource->total_canceled,
            'total_expired' => $this->resource->total_expired,
        ];
    }
}
