<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Invoice;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Marketplace\Support\Browse\Scopes\Invoice\ViewScope;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected bool   $isAdminCP = false;
    protected string $view      = ViewScope::VIEW_BOUGHT;

    public function boot()
    {
        $this->view = request()->get('view', $this->view);
    }

    protected array $apiParams = [
        'view'       => ':view',
        'listing_id' => ':listing_id',
        'from'       => ':from',
        'to'         => ':to',
        'status'     => ':status',
    ];

    protected array $apiRules = [
        'view'       => ['truthy', 'view'],
        'listing_id' => ['truthy', 'listing_id'],
        'from'       => ['truthy', 'from'],
        'to'         => ['truthy', 'to'],
        'status'     => ['truthy', 'status'],
    ];

    protected function initialize(): void
    {
        $this->dynamicRowHeight();
        $this->setDataSource('marketplace-invoice', $this->apiParams, $this->apiRules);

        $this->addColumn('listing.title')
            ->header(__p('core::phrase.title'))
            ->truncateLines()
            ->linkTo('url')
            ->flex(1);

        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->width(150);

        $this->addColumn('payment_date')
            ->header(__p('marketplace::web.transaction_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('status_info')
            ->header(__p('core::web.status'))
            ->asColoredText()
            ->width(150);

        match ($this->view) {
            ViewScope::VIEW_SOLD   => $this->addColumn('buyer.display_name')
                ->header(__p('marketplace::web.buyer'))
                ->width(200)
                ->truncateLines()
                ->linkTo('buyer.url')
                ->target('_blank'),
            ViewScope::VIEW_BOUGHT => $this->addColumn('seller.display_name')
                ->header(__p('marketplace::web.seller'))
                ->width(200)
                ->truncateLines()
                ->linkTo('seller.url')
                ->target('_blank')
        };

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('repayment')
                ->apiUrl('core/form/marketplace_invoice.payment/:id')
                ->asGet();
            $actions->add('confirmCancel')
                ->apiUrl('core/form/marketplace_invoice.cancel/:id')
                ->asGet();
            $actions->add('changeItem')
                ->apiUrl('marketplace-invoice/change')
                ->asPost()
                ->apiParams([
                    'id' => ':id',
                ])
                ->confirm([
                    'title'   => __p('marketplace::phrase.change_invoice'),
                    'message' => __p('marketplace::phrase.change_invoice_description'),
                ]);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {});

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('repayment')
                ->params([
                    'action'      => 'repayment',
                    'dialogProps' => [
                        'maxWidth' => 'xs',
                    ],
                ])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_repayment'],
                    ['falsy', 'item.extra.can_change'],
                    ['falsy', 'item.extra.can_cancel_on_expired_listing'],
                ])
                ->label(__p('marketplace::phrase.pay_now'))
                ->icon('ico-barchart-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT);

            $menu->addItem('changeItem')
                ->params([
                    'action'      => 'changeItem',
                    'dialogProps' => [
                        'maxWidth' => 'xs',
                    ],
                ])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_repayment'],
                    ['truthy', 'item.extra.can_change'],
                    ['falsy', 'item.extra.can_cancel_on_expired_listing'],
                ])->reload()
                ->label(__p('marketplace::phrase.pay_now'))
                ->icon('ico-barchart-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT);

            $menu->addItem('confirmCancel')
                ->params([
                    'action'      => 'confirmCancel',
                    'dialogProps' => [
                        'maxWidth' => 'xs',
                    ],
                ])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_cancel_on_expired_listing'],
                ])
                ->label(__p('marketplace::phrase.pay_now'))
                ->icon('ico-barchart-o')
                ->reload()
                ->value(MetaFoxForm::ACTION_ROW_EDIT);
        });
    }
}
