<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Http\Resources\v1\ActivityPoint;

use MetaFox\Platform\Resource\MobileSetting as ResourceSetting;

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
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getStatistic')
            ->apiUrl('/activitypoint/statistic/:id')
            ->apiParams([
                'purchase_id' => ':purchase_id',
            ])
            ->asGet();

        $this->add('getGiftForm')
            ->apiUrl('/core/mobile/form/activitypoint.gift/:id');
    }
}
