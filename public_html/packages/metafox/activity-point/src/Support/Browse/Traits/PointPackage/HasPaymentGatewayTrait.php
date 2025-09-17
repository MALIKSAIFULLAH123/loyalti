<?php

namespace MetaFox\ActivityPoint\Support\Browse\Traits\PointPackage;

use Illuminate\Support\Arr;

trait HasPaymentGatewayTrait
{
    public function hasPaymentGateway(): bool
    {
        $context      = user();
        $price        = $this->resource->price;
        $userCurrency = app('currency')->getUserCurrencyId($context);

        $gatewayParams = [
            'entity' => $this->resource->entityType(),
            'price'  => Arr::get($price, $userCurrency, 0),
        ];

        $hasPaymentGateway = app('events')->dispatch('payment.has_payment_gateway', [$context, $this->resource, $gatewayParams], true);

        if (null == $hasPaymentGateway) {
            return false;
        }

        return $hasPaymentGateway;
    }
}
