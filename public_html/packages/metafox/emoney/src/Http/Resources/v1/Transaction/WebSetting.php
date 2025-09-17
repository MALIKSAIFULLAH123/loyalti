<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\EMoney\Http\Resources\v1\Transaction;

use MetaFox\EMoney\Support\Support;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('homePage')
            ->pageUrl('emoney/transaction');

        $this->add('viewAll')
            ->apiUrl('emoney/transaction')
            ->apiParams([
                'base_currency' => ':base_currency',
                'status'        => ':status',
                'from_date'     => ':from_date',
                'to_date'       => ':to_date',
                'buyer'         => ':buyer',
                'id'            => ':id',
                'source'        => ':source',
                'type'          => ':type',
            ])
            ->apiRules([
                'to_date'       => ['truthy', 'to_date'],
                'from_date'     => ['truthy', 'from_date'],
                'status'        => ['includes', 'status', [Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED]],
                'base_currency' => ['truthy', 'base_currency'],
                'buyer'         => ['truthy', 'buyer'],
                'id'            => ['truthy', 'id'],
                'source'        => ['truthy', 'source'],
                'type'          => ['truthy', 'type'],
            ]);

        $this->add('getGrid')
            ->apiUrl('core/grid/ewallet.transaction');
    }
}
