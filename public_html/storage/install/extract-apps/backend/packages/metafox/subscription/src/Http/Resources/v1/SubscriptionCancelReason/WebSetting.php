<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionCancelReason;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('/subscription-cancel-reason')
            ->apiRules([
                'view' => [
                    'includes',
                    'view',
                    [Helper::VIEW_FILTER],
                ],
            ]);
    }
}
