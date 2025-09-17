<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PackageTransaction\Admin;

use MetaFox\ActivityPoint\Support\Browse\Scopes\PackagePurchase\SortScope;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
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
    protected string $appName      = 'activitypoint';
    protected string $resourceName = 'package-transaction';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchPackageTransactionForm());

        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource(apiUrl('admin.activitypoint.package-transaction.index'), [
            'full_name'      => ':full_name',
            'package_id'     => ':package_id',
            'transaction_id' => ':transaction_id',
            'status'         => ':status',
            'from'           => ':from',
            'to'             => ':to',
            'sort'           => ':sort',
            'sort_type'      => ':sort_type',
            'page'           => ':page',
            'limit'          => ':limit',
        ]);

        $this->addColumn('user.display_name')
            ->header(__p('activitypoint::phrase.member_name'))
            ->truncateLines()
            ->linkTo('user_link')
            ->target('_blank')
            ->flex();

        $this->addColumn('package_name')
            ->header(__p('activitypoint::web.package_name'))
            ->sortable()
            ->sortableField(SortScope::SORT_PACKAGE_NAME)
            ->flex();

        $this->addColumn('package_price_string')
            ->asPricing()
            ->header(__p('core::phrase.price'))
            ->sortable()
            ->sortableField(SortScope::SORT_PRICE)
            ->flex();

        $this->addColumn('package_point')
            ->header(__p('activitypoint::phrase.points'))
            ->sortable()
            ->sortableField(SortScope::SORT_POINT)
            ->flex();

        $this->addColumn('status')
            ->header(__p('activitypoint::phrase.payment_status'))
            ->flex();

        $this->addColumn('gateway')
            ->header(__p('payment::admin.payment_gateway'))
            ->width(200)
            ->truncateLines();

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
        });
    }
}
