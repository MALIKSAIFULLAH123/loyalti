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
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('searchItem')
            ->apiUrl('emoney/transaction')
            ->apiParams([
                'q'             => ':q',
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
                'q'             => ['truthy', 'q'],
                'to_date'       => ['truthy', 'to_date'],
                'from_date'     => ['truthy', 'from_date'],
                'status'        => ['includes', 'status', [Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED]],
                'base_currency' => ['truthy', 'base_currency'],
                'buyer'         => ['truthy', 'buyer'],
                'id'            => ['truthy', 'id'],
                'source'        => ['truthy', 'source'],
                'type'          => ['truthy', 'type'],
            ]);

        $this->add('viewAll')
            ->apiUrl('emoney/transaction')
            ->apiParams([
                'q'             => ':q',
                'base_currency' => ':base_currency',
                'status'        => ':status',
                'from_date'     => ':from_date',
                'to_date'       => ':to_date',
                'buyer'         => ':buyer',
                'id'            => ':id',
            ])
            ->apiRules([
                'q'             => ['truthy', 'q'],
                'to_date'       => ['truthy', 'to_date'],
                'from_date'     => ['truthy', 'from_date'],
                'status'        => ['includes', 'status', [Support::TRANSACTION_STATUS_PENDING, Support::TRANSACTION_STATUS_APPROVED]],
                'base_currency' => ['truthy', 'base_currency'],
                'buyer'         => ['truthy', 'buyer'],
                'id'            => ['truthy', 'id'],
            ]);

        $this->add('viewItem')
            ->apiUrl('emoney/transaction/:id');
    }
}
