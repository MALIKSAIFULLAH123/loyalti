<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('/subscription-invoice')
            ->apiRules([]);

        $this->add('viewItem')
            ->apiUrl('/subscription-invoice/:id');

        $this->add('getCancelSubscriptionForm')
            ->apiUrl('core/mobile/form/subscription.subscription_invoice.cancel/:id');

        $this->add('getUpgradeSubscriptionForm')
            ->apiUrl('/subscription-invoice/payment-form/:id')
            ->apiParams([
                'action_type' => Helper::UPGRADE_FORM_ACTION,
            ]);

        $this->add('getPayNowSubscriptionForm')
            ->apiUrl('/subscription-invoice/payment-form/:id')
            ->apiParams([
                'action_type' => Helper::PAY_NOW_FORM_ACTION,
            ]);

        $this->add('getRenewSubscriptionForm')
            ->apiUrl('/subscription-invoice/renew-form/:id');

        $this->add('changeInvoice')
            ->apiUrl('/subscription-invoice/change-invoice/:id')
            ->asPost()
            ->confirm([
                'title'   => __p('subscription::phrase.change_invoice'),
                'message' => __p('subscription::phrase.change_invoice_description'),
            ]);
    }
}
