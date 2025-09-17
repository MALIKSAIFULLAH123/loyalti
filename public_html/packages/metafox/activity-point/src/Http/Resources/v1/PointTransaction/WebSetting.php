<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */

/**
 * Class WebSetting
 * Inject this class into property $resources.
 * @SuppressWarnings(PHPMD.ExcessiveClassLength)
 * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->pageUrl('activitypoint/history')
            ->apiUrl('/activitypoint/transaction')
            ->apiRules([
                'type' => [
                    'truthy',
                    'type',
                ],
                'from' => [
                    'truthy',
                    'from',
                ],
                'to'   => [
                    'truthy',
                    'to',
                ],
            ]);

        $this->add('getGrid')
            ->apiUrl('core/grid/activitypoint.transaction');
    }
}
