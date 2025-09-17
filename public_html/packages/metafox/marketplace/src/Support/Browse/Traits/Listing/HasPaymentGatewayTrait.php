<?php

namespace MetaFox\Marketplace\Support\Browse\Traits\Listing;

use MetaFox\Marketplace\Support\Facade\Listing as Facade;

trait HasPaymentGatewayTrait
{
    public function hasPaymentGateway(): bool
    {
        $context       = user();

        $gatewayParams = Facade::getPaymentGatewayParams($context, $this->resource);

        $hasPaymentGateway = app('events')->dispatch('payment.has_payment_gateway', [$context, $this->resource, $gatewayParams], true);

        if (null == $hasPaymentGateway) {
            return false;
        }

        return $hasPaymentGateway;
    }
}
