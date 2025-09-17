<?php

/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction;

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
        $this->add('viewAll')
            ->pageUrl('activitypoint/transaction-history')
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

        $this->add('searchItem')
            ->apiUrl('activitypoint/transaction')
            ->placeholder(__p('activitypoint::phrase.search_transactions'))
            ->apiParams([
                'from' => ':from',
                'to'   => ':to',
                'type' => ':type',
            ])
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

        $this->add('getSearchForm')
            ->apiUrl('core/mobile/form/activitypoint_transaction.search');
    }
}
