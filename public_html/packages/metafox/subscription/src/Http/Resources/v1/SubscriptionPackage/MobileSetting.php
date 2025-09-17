<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionPackage;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Platform\Support\Browse\Browse;
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
            ->apiUrl('/subscription-package')
            ->apiRules([
                'view' => [
                    'includes',
                    'view',
                    [Helper::VIEW_FILTER, Browse::VIEW_SEARCH],
                ],
                'q' => [
                    'truthy',
                    'q',
                ],
            ]);

        $this->add('searchItem')
            ->pageUrl('subscription/search')
            ->placeholder(__p('subscription::admin.search_packages'));

        $this->add('getPaymentPackageForm')
            ->apiUrl('/subscription-package/payment-form/:id');
    }
}
