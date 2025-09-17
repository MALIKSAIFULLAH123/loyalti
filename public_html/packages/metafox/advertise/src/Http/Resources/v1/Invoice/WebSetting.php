<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Advertise\Http\Resources\v1\Invoice;

use MetaFox\Advertise\Support\Facades\Support;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('advertise/invoice')
            ->apiParams([
                'start_date' => ':start_date',
                'end_date'   => ':end_date',
                'status'     => ':status',
            ])
            ->apiRules([
                'start_date' => ['truthy', 'start_date'],
                'end_date'   => ['truthy', 'end_date'],
                'status'     => ['includes', 'status', Support::getInvoiceStatuses()],
            ]);

        $this->add('viewItem')
            ->apiUrl('advertise/invoice/:id')
            ->pageUrl('advertise/invoice/:id');

        $this->add('cancelItem')
            ->apiUrl('advertise/invoice/cancel/:id')
            ->asPatch()
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('advertise::phrase.are_you_sure_you_want_to_cancel_this_invoice'),
            ]);

        $this->add('paymentItem')
            ->apiUrl('core/form/advertise_invoice.payment/:id');

        $this->add('searchForm')
            ->apiUrl('core/form/advertise_invoice.search_form');

        $this->add('getGrid')
            ->apiUrl('core/grid/advertise.invoice');
    }
}
