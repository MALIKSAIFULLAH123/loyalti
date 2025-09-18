<?php

namespace MetaFox\InAppPurchase\Listeners;

use MetaFox\Payment\Support\Facades\Payment;
use MetaFox\Platform\Contracts\Entity;
use MetaFox\Platform\Contracts\User;
use MetaFox\Platform\MetaFox;

/**
 * Class CanPaySubscriptionListener.
 * @ignore
 * @codeCoverageIgnore
 */
class CanUpgradeSubscriptionListener
{
    public function handle(User $user, ?Entity $resource): ?bool
    {
        if (!MetaFox::isMobile()) {
            return null;
        }

        if (!$resource instanceof Entity) {
            return null;
        }

        $service = Payment::getManager()->getGatewayServiceByName('in-app-purchase');

        if (!$service->hasAccess($user, [
            'entity_type' => $resource->entityType(),
            'entity_id'   => $resource->entityId(),
        ])) {
            return false;
        }

        return null;
    }
}
