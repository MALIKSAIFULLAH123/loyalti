<?php
/**
 * @author  developer@phpfox.com
 * @license phpfox.com
 */

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Marketplace\Support\Facade\Listing as ListingFacade;
use MetaFox\Platform\Resource\WebSetting as ResourceSetting;

/**
 * stub: /packages/resources/resource_setting.stub
 * Add this class name to resources config gateway.
 */
class MobileSetting extends ResourceSetting
{
    protected function initialize(): void
    {
        $this->add('viewAll')
            ->apiUrl('marketplace-invoice')
            ->apiParams([
                'view'       => ':view',
                'listing_id' => ':listing_id',
                'from'       => ':from',
                'to'         => ':to',
                'status'     => ':status',
            ])
            ->apiRules([
                'view'       => ['includes', 'view', ViewScope::getAllowView()],
                'listing_id' => ['truthy', 'listing_id'],
                'from'       => ['truthy', 'from'],
                'to'         => ['truthy', 'to'],
                'status'     => ['includes', 'status', ListingFacade::getPaymentStatus()],
            ])
            ->asGet();

        $this->add('viewItem')
            ->apiUrl('marketplace-invoice/:id')
            ->pageUrl('marketplace/invoice/:id');

        $this->add('changeItem')
            ->apiUrl('marketplace-invoice/change')
            ->asPost()
            ->apiParams([
                'id' => ':id',
            ])
            ->confirm([
                'title'   => __p('marketplace::phrase.change_invoice'),
                'message' => __p('marketplace::phrase.change_invoice_description'),
            ]);

        $this->add('getRepaymentForm')
            ->apiUrl('core/mobile/form/marketplace_invoice.payment/:id')
            ->asGet();

        $this->add('getBoughtSearchForm')
            ->apiParams([
                'listing_id' => ':listing_id',
            ])
            ->apiUrl('core/mobile/form/marketplace_invoice.bought_search');

        $this->add('getSoldSearchForm')
            ->apiParams([
                'listing_id' => ':listing_id',
            ])
            ->apiUrl('core/mobile/form/marketplace_invoice.sold_search');

        $this->add('searchBoughtItem')
            ->apiParams([
                'listing_id' => ':listing_id',
                'from'       => ':from',
                'to'         => ':to',
                'status'     => ':status',
                'view'       => ViewScope::VIEW_BOUGHT,
            ])
            ->apiUrl('/marketplace-invoice');

        $this->add('searchSoldItem')
            ->apiParams([
                'listing_id' => ':listing_id',
                'from'       => ':from',
                'to'         => ':to',
                'status'     => ':status',
                'view'       => ViewScope::VIEW_SOLD,
            ])
            ->apiUrl('/marketplace-invoice');

        $this->add('viewBoughtInvoices')
            ->apiUrl('marketplace-invoice')
            ->apiParams(['view' => ViewScope::VIEW_BOUGHT]);

        $this->add('viewSoldInvoices')
            ->apiUrl('marketplace-invoice')
            ->apiParams(['view' => ViewScope::VIEW_SOLD]);
    }
}
