<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Listeners;

use MetaFox\Subscription\Models\SubscriptionPackage;
use MetaFox\Platform\Contracts\User;
use MetaFox\Subscription\Repositories\SubscriptionInvoiceRepositoryInterface;

class IapCreateInvoiceListener
{
    public function handle(string $itemType, User $context, array $params): ?array
    {
        if ($itemType != SubscriptionPackage::ENTITY_TYPE) {
            return null;
        }

        return $this->subscriptionInvoiceRepository()->createInvoice($context, $params);
    }

    public function subscriptionInvoiceRepository(): SubscriptionInvoiceRepositoryInterface
    {
        return resolve(SubscriptionInvoiceRepositoryInterface::class);
    }
}
