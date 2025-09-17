<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Subscription\Http\Resources\v1\SubscriptionComparison\Admin;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;
use MetaFox\Subscription\Support\Helper;

/**
 * stub: /packages/resources/resource_admin_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('/admincp/subscription-comparison')
            ->apiRules([
                'view' => [
                    'includes',
                    'view',
                    [Helper::VIEW_ADMINCP],
                ],
            ]);
    }
}
