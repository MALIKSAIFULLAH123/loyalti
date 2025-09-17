<?php

namespace MetaFox\Featured\Http\Resources\v1\Transaction\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName = 'featured';
    protected string $resourceName = 'transaction';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->addColumn('user.display_name')
            ->header(__p('featured::admin.user'))
            ->linkTo('user.url')
            ->truncateLines()
            ->flex();

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

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('payment_gateway.title')
            ->header(__p('payment::admin.payment_gateway'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->truncateLines()
            ->width(150);

        $this->addColumn('transaction_id')
            ->header(__p('featured::phrase.transaction_id'))
            ->truncateLines()
            ->width(200);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->truncateLines()
            ->width(180);
    }
}
