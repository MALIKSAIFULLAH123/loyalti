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
class WebSetting extends ResourceSetting
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
            ->apiUrl('core/form/marketplace_invoice.payment/:id')
            ->asGet();

        $this->add('cancelOnExpiredListing')
            ->apiUrl('core/form/marketplace_invoice.cancel/:id')
            ->asGet();

        $this->add('getBoughtSearchForm')
            ->apiParams([
                'listing_id' => ':listing_id',
            ])
            ->apiUrl('core/form/marketplace_invoice.bought_search');

        $this->add('getSoldSearchForm')
            ->apiParams([
                'listing_id' => ':listing_id',
            ])
            ->apiUrl('core/form/marketplace_invoice.sold_search');

        $this->add('getSoldGrid')
            ->apiParams([
                'view' => ViewScope::VIEW_SOLD,
            ])
            ->apiUrl('core/grid/marketplace.invoice');

        $this->add('getBoughtGird')
            ->apiParams([
                'view' => ViewScope::VIEW_BOUGHT,
            ])
            ->apiUrl('core/grid/marketplace.invoice');
    }
}
