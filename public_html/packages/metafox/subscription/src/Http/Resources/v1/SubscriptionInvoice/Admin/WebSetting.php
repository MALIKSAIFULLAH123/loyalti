<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionInvoice\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchForm')
            ->apiUrl('admincp/core/form/subscription.subscription_invoice.search_form');

        $this->add('viewDetail')
            ->apiUrl('admincp/subscription/invoice/:id/short-transaction');
    }
}
