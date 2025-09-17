<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Featured\Http\Resources\v1\Invoice;

use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class WebSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('getGrid')
            ->apiUrl('core/grid/featured.invoice');

        $this->add('getSearchForm')
            ->apiUrl('core/form/featured.invoice.search_form');

        $this->add('viewAll')
            ->apiUrl('featured/invoice')
            ->apiParams([
                'item_type'       => ':item_type',
                'package_id'      => ':package_id',
                'status'          => ':status',
                'payment_gateway' => ':payment_gateway',
                'from_date'       => ':from_date',
                'to_date'         => ':to_date',
            ])
            ->apiRules([
                'item_type'       => ['truthy', 'item_type'],
                'package_id'      => ['truthy', 'package_id'],
                'status'          => ['truthy', 'status'],
                'payment_gateway' => ['truthy', 'payment_gateway'],
                'from_date'       => ['truthy', 'from_date'],
                'to_date'         => ['truthy', 'to_date'],
            ]);

        $this->add('searchItem')
            ->placeholder(__p('featured::phrase.search_invoices'))
            ->apiUrl('featured/invoice')
            ->apiParams([
                'q'               => ':q',
                'item_type'       => ':item_type',
                'package_id'      => ':package_id',
                'status'          => ':status',
                'payment_gateway' => ':payment_gateway',
                'from_date'       => ':from_date',
                'to_date'         => ':to_date',
            ])
            ->apiRules([
                'q'               => ['truthy', 'q'],
                'item_type'       => ['truthy', 'item_type'],
                'package_id'      => ['truthy', 'package_id'],
                'status'          => ['truthy', 'status'],
                'payment_gateway' => ['truthy', 'payment_gateway'],
                'from_date'       => ['truthy', 'from_date'],
                'to_date'         => ['truthy', 'to_date'],
            ]);

        $this->add('paymentItem')
            ->apiUrl('core/form/featured.invoice.payment/:id');

        $this->add('cancelItem')
            ->asPatch()
            ->apiUrl('featured/invoice/:id/cancel')
            ->confirm([
                'title'   => __p('core::phrase.confirm'),
                'message' => __p('featured::phrase.are_you_sure_you_want_to_cancel_this_invoice'),
            ]);

        $this->add('viewItem')
            ->apiUrl('featured/invoice/:id');
    }
}
