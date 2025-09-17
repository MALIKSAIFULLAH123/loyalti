<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction;

use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\SortScope;
use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 * @driverName activitypoint.package_transaction
 * @driverName data-grid
 */
class DataGrid extends Grid
{
    public bool     $isAdminCP = false;
    protected array $apiParams = [
        'status'         => ':status',
        'from'           => ':from',
        'to'             => ':to',
        'transaction_id' => ':transaction_id',
        'sort'           => ':sort',
        'sort_type'      => ':sort_type',
        'page'           => ':page',
        'limit'          => ':limit',
    ];

    protected array $apiRules = [
        'status'         => ['truthy', 'status'],
        'from'           => ['truthy', 'from'],
        'to'             => ['truthy', 'to'],
        'transaction_id' => ['truthy', 'transaction_id'],
        'sort'           => ['truthy', 'sort'],
        'sort_type'      => ['truthy', 'sort_type'],
        'page'           => ['truthy', 'page'],
        'limit'          => ['truthy', 'limit'],
    ];

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource(apiUrl('activitypoint.package-transaction.index'), $this->apiParams, $this->apiRules);

        $this->addColumn('package_name')
            ->header(__p('activitypoint::web.package_name'))
            ->sortable()
            ->sortableField(SortScope::SORT_PACKAGE_NAME)
            ->flex();

        $this->addColumn('package_point')
            ->header(__p('activitypoint::phrase.points'))
            ->sortable()
            ->sortableField(SortScope::SORT_POINT)
            ->flex();

        $this->addColumn('package_price_string')
            ->asPricing()
            ->sortable()
            ->sortableField(SortScope::SORT_PRICE)
            ->header(__p('core::phrase.price'))
            ->flex();

        $this->addColumn('status')
            ->header(__p('activitypoint::phrase.payment_status'))
            ->flex();

        $this->addColumn('transaction_id')
            ->header(__p('payment::phrase.transaction_id'))
            ->flex();

        $this->addColumn('date')
            ->header(__p('activitypoint::phrase.date'))
            ->sortable()
            ->sortableField(Browse::SORT_RECENT)
            ->asDateTime();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete']);
            $actions->add('payNow')
                ->apiUrl('core/form/package_transaction.pay/:id')
                ->asGet();
        });

        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('payNow')
                ->label(__p('activitypoint::phrase.pay_now'))
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_payment'],
                ])
                ->params([
                    'action'      => 'payNow',
                    'dialogProps' => [
                        'maxWidth' => 'xs',
                    ],
                ])
                ->icon('ico-barchart-o')
                ->value(MetaFoxForm::ACTION_ROW_EDIT);
        });
    }
}
