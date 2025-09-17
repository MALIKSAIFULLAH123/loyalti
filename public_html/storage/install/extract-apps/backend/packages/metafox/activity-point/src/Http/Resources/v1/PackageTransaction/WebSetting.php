<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction;

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
            ->pageUrl(apiUrl('activitypoint.package-transaction.index'))
            ->apiUrl(apiUrl('activitypoint.package-transaction.index'))
            ->apiRules([
                'q'      => [
                    'truthy',
                    'q',
                ],
                'status' => [
                    'truthy',
                    'status',
                ],
                'from'   => [
                    'truthy',
                    'from',
                ],
                'to'     => [
                    'truthy',
                    'to',
                ],
                'transaction_id'     => [
                    'truthy',
                    'to',
                ],
            ]);
        $this->add('getGrid')
            ->apiUrl('core/grid/activitypoint.package_transaction');
    }
}
