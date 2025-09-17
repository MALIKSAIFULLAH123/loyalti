<?php

namespace MetaFox\Payment\Http\Resources\v1\Order\Admin;

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
    protected string $appName = 'payment';
    protected string $resourceName = 'order';

    protected function initialize(): void
    {
        $this->addColumn('buyer.display_name')
            ->header(__p('payment::admin.buyer'))
            ->linkTo('buyer.url')
            ->flex()
            ->truncateLines()
            ->target('_blank');

        $this->addColumn('item.title')
            ->header(__p('payment::admin.item'))
            ->linkTo('item.url')
            ->flex()
            ->truncateLines()
            ->target('_blank');

        $this->addColumn('seller.display_name')
            ->header(__p('payment::admin.seller'))
            ->linkTo('seller.url')
            ->flex()
            ->truncateLines()
            ->target('_blank');

        $this->addColumn('app')
            ->header(__p('core::phrase.app'))
            ->width(200)
            ->truncateLines();

        $this->addColumn('gateway')
            ->header(__p('payment::admin.payment_gateway'))
            ->width(200)
            ->truncateLines();

        $this->addColumn('type')
            ->header(__p('payment::admin.type'))
            ->width(200);

        $this->addColumn('total')
            ->header(__p('core::phrase.total'))
            ->width(300);

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->width(200);

        $this->addColumn('recurring_status')
            ->header(__p('payment::admin.subscription_status'))
            ->width(250);

        $this->addColumn('gateway_order_id')
            ->header(__p('payment::admin.order_id'))
            ->width(300)
            ->truncateLines();

        $this->addColumn('gateway_subscription_id')
            ->header(__p('payment::admin.subscription_id'))
            ->width(300)
            ->truncateLines();

        $this->addColumn('created_at')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(300);
    }
}
