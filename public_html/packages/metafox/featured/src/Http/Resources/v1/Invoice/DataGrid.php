<?php

namespace MetaFox\Featured\Http\Resources\v1\Invoice;

use Illuminate\Auth\Access\AuthorizationException;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends GridConfig
{
    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'item_type'       => ':item_type',
        'package_id'      => ':package_id',
        'status'          => ':status',
        'payment_gateway' => ':payment_gateway',
        'from_date'       => ':from_date',
        'to_date'         => ':to_date',
        'id'              => ':id',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'item_type'       => ['truthy', 'item_type'],
        'package_id'      => ['truthy', 'package_id'],
        'status'          => ['truthy', 'status'],
        'payment_gateway' => ['truthy', 'payment_gateway'],
        'from_date'       => ['truthy', 'from_date'],
        'to_date'         => ['truthy', 'to_date'],
        'id'              => ['truthy', 'id'],
    ];

    public function boot()
    {
        if (user()->isGuest()) {
            throw new AuthorizationException();
        }
    }

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(10, [10, 20, 50]);

        $this->setDataSource(apiUrl('featured.invoice.index'), $this->apiParams, $this->apiRules);

        $this->addColumn('item_title')
            ->header(__p('core::web.item'))
            ->truncateLines()
            ->linkTo('item_link')
            ->flex()
            ->target('_blank');

        $this->addColumn('item_type_label')
            ->header(__p('core::phrase.item_type'))
            ->truncateLines()
            ->width(180);

        $this->addColumn('package.title')
            ->header(__p('featured::phrase.package'))
            ->truncateLines()
            ->flex();

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->asColoredText()
            ->truncateLines()
            ->width(120);

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
            ->width(300);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->truncateLines()
            ->asDateTime()
            ->width(220);

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('payment')
                ->icon('ico-credit-card-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT)
                ->label(__p('featured::phrase.pay_now'))
                ->params([
                    'action'      => 'paymentItem',
                    'dialogProps' => [
                        'fullWidth' => false,
                    ],
                ])
                ->showWhen([
                    'truthy', 'item.extra.can_payment',
                ]);

            $menu->addItem('cancel')
                ->value(MetaFoxForm::ACTION_BATCH_ITEM)
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
        });

        $this->withActions(function (Actions $actions) {
            $actions->add('paymentItem')
                ->apiUrl('core/form/featured.invoice.payment/:id');

            $actions->add('cancelItem')
                ->asPatch()
                ->apiUrl('featured/invoice/:id/cancel');
        });
    }
}
