<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 *
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'start_date' => ':start_date',
        'end_date'   => ':end_date',
        'status'     => ':status',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'status'     => ['truthy', 'status'],
        'start_date' => ['truthy', 'start_date'],
        'end_date'   => ['truthy', 'end_date'],
    ];

    protected function initialize(): void
    {
        $this->setRowsPerPage(20, [10, 20, 50]);
        $this->setDataSource(apiUrl('invoice.index'), $this->apiParams, $this->apiRules);
        $this->dynamicRowHeight();

        $this->addColumn('item_title')
            ->header(__p('core::phrase.title'))
            ->truncateLines()
            ->flex(1);

        $this->addColumn('transaction_id')
            ->header(__p('advertise::web.transaction_id'))
            ->setAttribute('emptyText', __p('advertise::web.n_a'))
            ->width(300);

        $this->addColumn('paid_at')
            ->header(__p('advertise::web.start_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('payment_status')
            ->asColoredText()
            ->header(__p('core::web.status'))
            ->width(150);

        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->width(100);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('paymentItem')
                ->apiUrl('core/form/advertise_invoice.payment/:id');

            $actions->add('cancelItem')
                ->apiUrl('advertise/invoice/cancel/:id')
                ->asPatch()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_cancel_this_invoice'),
                ]);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('payment')
                ->icon('ico-credit-card-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('advertise::web.pay_now'))
                ->params([
                    'action'      => 'paymentItem',
                    'as'          => [
                        'price' => 'price',
                    ],
                    'dialogProps' => [
                        'fullWidth' => false,
                    ],
                ])
                ->as('menuItem.dataGird.as.labelIcu')
                ->showWhen([
                    'truthy', 'item.extra.can_payment',
                ]);

            $menu->addItem('cancel')
                ->icon('ico-close-circle-o')
                ->value(MetaFoxForm::ACTION_BATCH_ITEM)
                ->label(__p('core::phrase.cancel'))
                ->showWhen(['truthy', 'item.extra.can_cancel'])
                ->params([
                    'action' => 'cancelItem',
                ])
                ->reload();
        });
    }
}
