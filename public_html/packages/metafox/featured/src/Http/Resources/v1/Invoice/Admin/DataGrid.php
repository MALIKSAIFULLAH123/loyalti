<?php
namespace MetaFox\Featured\Http\Resources\v1\Invoice\Admin;

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends GridConfig
{
    protected string $appName = 'featured';
    protected string $resourceName = 'invoice';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->addColumn('user.display_name')
            ->header(__p('featured::admin.user'))
            ->linkTo('user.url')
            ->truncateLines()
            ->flex()
            ->target('_blank');

        $this->addColumn('item_title')
            ->header(__p('core::web.item'))
            ->truncateLines()
            ->linkTo('item_link')
            ->flex()
            ->target('_blank');

        $this->addColumn('item_type_label')
            ->header(__p('core::phrase.item_type'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('package.title')
            ->header(__p('featured::phrase.package'))
            ->truncateLines()
            ->width(200);

        $this->addColumn('status')
            ->asColoredText()
            ->header(__p('core::phrase.status'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('payment_gateway.title')
            ->header(__p('payment::admin.payment_gateway'))
            ->truncateLines()
            ->width(200);

        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->truncateLines()
            ->width(200);

        $this->addColumn('transaction_id')
            ->header(__p('featured::phrase.transaction_id'))
            ->truncateLines()
            ->width(250);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->truncateLines()
            ->asDateTime()
            ->width(220);

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('cancel')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.cancel'))
                ->params(['action' => 'cancelItem'])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_cancel'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('featured::phrase.are_you_sure_you_want_to_cancel_this_invoice'),
                ])
                ->reload();

            $menu->addItem('mark_as_paid')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('featured::admin.mark_as_paid'))
                ->params(['action' => 'markItemAsPaid'])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_mark_as_paid'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('featured::admin.are_you_sure_you_want_to_this_invoice_as_paid'),
                ])
                ->reload();
        });

        $this->withActions(function (Actions $actions) {
            $actions->add('markItemAsPaid')
                ->asPatch()
                ->apiUrl('admincp/featured/invoice/:id/mark-as-paid');

            $actions->add('cancelItem')
                ->asPatch()
                ->apiUrl('admincp/featured/invoice/:id/cancel');
        });
    }
}
