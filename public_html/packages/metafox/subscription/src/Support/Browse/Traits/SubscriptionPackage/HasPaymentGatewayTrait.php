<?php

namespace MetaFox\Subscription\Support\Browse\Traits\SubscriptionPackage;

use Illuminate\Support\Arr;

trait HasPaymentGatewayTrait
{
    public function hasPaymentGateway(): bool
    {
        if ($this->resource->is_free === true) {
            return true;
        }

        $context      = user();
        $price        = json_decode($this->resource->price, true);
        $userCurrency = app('currency')->getUserCurrencyId($context);

        $gatewayParams = [
            'entity' => $this->resource?->entityType(),
            'price'  => Arr::get($price, $userCurrency, 0),
        ];

        if ($this->resource->is_recurring) {
            Arr::set($gatewayParams, 'ignore_verify_default_payment_card', true);
        }

        $hasPaymentGateway = app('events')->dispatch('payment.has_payment_gateway', [$context, $this->resource, $gatewayParams], true);

        if (null == $hasPaymentGateway) {
            return false;
        }

        return $hasPaymentGateway;
    }
}
