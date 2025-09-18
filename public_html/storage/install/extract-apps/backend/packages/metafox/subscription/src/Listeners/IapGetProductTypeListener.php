<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Listeners;

use MetaFox\Subscription\Models\SubscriptionInvoice;
use MetaFox\Subscription\Models\SubscriptionPackage;

class IapGetProductTypeListener
{
    public function handle()
    {
        return [
            [
                'package_id' => 'metafox/subscription',
                'value'      => SubscriptionPackage::ENTITY_TYPE,
                'url'        => '/subscription/package/browse',
                'label'      => __p('subscription::phrase.subscription_package'),
            ],
            [
                'package_id' => 'metafox/subscription',
                'value'      => SubscriptionInvoice::ENTITY_TYPE,
                'url'        => '/subscription/invoice/browse',
                'label'      => __p('subscription::phrase.subscription'),
                'hidden'     => true,
            ],
        ];
    }
}
