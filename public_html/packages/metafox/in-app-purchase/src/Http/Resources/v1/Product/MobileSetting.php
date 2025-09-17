<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\InAppPurchase\Http\Resources\v1\Product;

use MetaFox\InAppPurchase\Support\Constants;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewProduct')
            ->apiUrl('in-app-purchase/detail/:resource_name/:id');
        $this->add('validateReceipt')
            ->apiUrl('in-app-purchase/validate-receipt')
            ->asPost()
            ->apiParams([
                'transaction_id'  => ':transaction_id',
                'subscription_id' => ':subscription_id',
                'purchase_token'  => ':purchase_token',
                'platform'        => ':platform',
                'gateway_token'   => ':gateway_token',
            ])
            ->apiRules([
                'platform' => ['includes', 'platform', [Constants::IOS, Constants::ANDROID]],
            ]);
    }
}
