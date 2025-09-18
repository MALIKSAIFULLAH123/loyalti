<?php

namespace MetaFox\Marketplace\Support\Browse\Traits\Listing;

use MetaFox\Marketplace\Models\Listing;
use MetaFox\Platform\Contracts\Policy\ResourcePolicyInterface;
use MetaFox\Platform\Facades\PolicyGate;
use MetaFox\Platform\Traits\Http\Resources\HasExtra;
use MetaFox\Platform\Contracts\User;

trait ExtraTrait
{
    use HasExtra {
        getExtra as getMainExtra;
    }

    use HasPaymentGatewayTrait;

    public function getExtra(bool $forDetail = true)
    {
        $policy = PolicyGate::getPolicyFor(Listing::class);

        $extra = $this->getMainExtra();

        if (null === $policy) {
            return $extra;
        }

        $context           = user();

        $extra = array_merge($this->getMainExtra(), [
            'can_invite'              => $policy->invite($context, $this->resource),
            'can_message'             => $policy->message($context, $this->resource),
            'can_reopen'              => $policy->reopen($context, $this->resource),
        ]);

        if ($forDetail) {
            $extra = array_merge($extra, $this->getExtraForPayment($context, $policy));
        }

        return $extra;
    }

    protected function getExtraForPayment(User $context, ResourcePolicyInterface $policy): array
    {
        $canPayment        = $policy->payment($context, $this->resource);

        $hasPaymentGateway = $this->hasPaymentGateway();

        return [
            'can_payment'             => $canPayment,
            'can_show_payment_button' => $canPayment && $hasPaymentGateway,
            'can_show_message'        => $canPayment && !$hasPaymentGateway,
        ];
    }
}
