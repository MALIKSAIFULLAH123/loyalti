<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\EMoney\Http\Resources\v1\WithdrawRequest;

use MetaFox\EMoney\Facades\Emoney;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('addItem')
            ->apiUrl('core/form/ewallet.request.store')
            ->asFormDialog(true);

        $this->add('homePage')
            ->pageUrl('ewallet/request');

        $this->add('cancelItem')
            ->asPatch()
            ->apiUrl('emoney/request/cancel/:id')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('ewallet::phrase.are_you_sure_you_want_to_cancel_this_request'),
            ]);

        $this->add('viewAll')
            ->apiUrl('emoney/request')
            ->apiParams([
                'from_date' => ':from_date',
                'to_date'   => ':to_date',
                'status'    => ':status',
                'id'        => ':id',
            ])
            ->apiRules([
                'from_date' => ['truthy', 'from_date'],
                'to_date'   => ['truthy', 'to_date'],
                'status'    => ['includes', 'status', Emoney::getRequestStatuses()],
                'id'        => ['truthy', 'id'],
            ]);
    }
}
