<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison;

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
            ->apiUrl('/subscription-comparison')
            ->apiRules([
                'view' => [
                    'includes',
                    'view',
                    [Helper::VIEW_FILTER],
                ],
            ]);
    }
}
