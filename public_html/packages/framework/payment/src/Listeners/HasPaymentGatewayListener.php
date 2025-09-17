<?php

namespace MetaFox\Payment\Listeners;

use Illuminate\Support\Arr;
use MetaFox\Form\AbstractField;
use MetaFox\Payment\Contracts\GatewayManagerInterface;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;

class HasPaymentGatewayListener
{
    public function handle(User $context, ?Entity $resource, array $extraGatewayParams = []): bool
    {
        $gatewayParams = [];

        if ($resource instanceof Entity) {
            $gatewayParams = [
                'entity_type' => $resource?->entityType(),
                'entity_id'   => $resource?->entityId(),
            ];
        }

        $gatewayParams = array_merge($gatewayParams, $extraGatewayParams);

        $gateways = resolve(GatewayManagerInterface::class)->getGatewaysForForm($context, $gatewayParams, $resource);

        if (count($gateways)) {
            return true;
        }

        return false;
    }
}
