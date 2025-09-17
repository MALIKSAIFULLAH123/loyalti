<?php

namespace MetaFox\ActivityPoint\Support\Browse\Traits\PointPackage;

use MetaFox\ActivityPoint\Models\PointPackage;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;

trait ExtraTrait
{
    use HasExtra {
        getExtra as getMainExtra;
    }
    use HasPaymentGatewayTrait;

    public function getExtra()
    {
        $policy  = PolicyGate::getPolicyFor(PointPackage::class);
        $context = user();

        $canPayment        = $policy->purchase($context, $this->resource);
        $hasPaymentGateway = $this->hasPaymentGateway();

        return array_merge($this->getMainExtra(), [
            'can_show_payment_button'             => $canPayment && $hasPaymentGateway,
            'can_show_no_payment_gateway_message' => $canPayment && !$hasPaymentGateway,
        ]);
    }
}
