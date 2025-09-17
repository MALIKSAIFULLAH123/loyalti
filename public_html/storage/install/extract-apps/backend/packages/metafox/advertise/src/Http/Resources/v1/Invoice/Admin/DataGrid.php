<?php

namespace MetaFox\Advertise\Http\Resources\v1\Invoice\Admin;

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
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'advertise';
    protected string $resourceName = 'invoice';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('id')
            ->header(__p('core::phrase.id'))
            ->truncateLines()
            ->width(100)
            ->linkTo('detail');

        $this->addColumn('item_title')
            ->header(__p('core::phrase.title'))
            ->truncateLines()
            ->flex(1);

        $this->addColumn('user.display_name')
            ->header(__p('advertise::phrase.user'))
            ->width(200)
            ->truncateLines()
            ->linkTo('user.url')
            ->target('_blank');

        $this->addColumn('transaction_id')
            ->header(__p('advertise::web.transaction_id'))
            ->width(300);

        $this->addColumn('paid_at')
            ->header(__p('advertise::web.start_date'))
            ->asDateTime()
            ->width(200);

        $this->addColumn('payment_status')
            ->header(__p('core::web.status'))
            ->width(150);

        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->width(100);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('deleteItem')
                ->apiUrl('admincp/advertise/invoice/:id')
                ->asDelete();

            $actions->add('cancelItem')
                ->apiUrl('advertise/invoice/cancel/:id')
                ->asPatch();
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
            $menu->addItem('cancel')
                ->icon('ico-close-circle-o')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.cancel'))
                ->showWhen(['truthy', 'item.extra.can_cancel'])
                ->action('cancelItem')
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_cancel_this_invoice'),
                ])
                ->reload();

            $menu->withDelete()
                ->action('deleteItem')
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('advertise::phrase.are_you_sure_you_want_to_delete_this_invoice_permanently'),
                ])
                ->reload();

            $menu->addItem('viewTransaction')
                ->label(__p('advertise::phrase.view_transactions'))
                ->icon('ico-barchart-o')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->params([
                    'to' => '/advertise/invoice/detail/:id',
                ]);

        });
    }
}
