<?php

namespace MetaFox\Payment\Http\Resources\v1\Transaction\Admin;

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
    protected string $resourceName = 'transaction';

    protected function initialize(): void
    {
        $this->addColumn('id')
            ->header(__p('core::phrase.id'))
            ->linkTo('link')
            ->target('_blank')
            ->width(120);

        $this->addColumn('user.display_name')
            ->header(__p('user::phrase.user'))
            ->linkTo('user.url')
            ->target('_blank')
            ->flex()
            ->truncateLines();

        $this->addColumn('gateway')
            ->header(__p('payment::admin.payment_gateway'))
            ->width(200)
            ->truncateLines();

        $this->addColumn('total')
            ->header(__p('core::phrase.total'))
            ->width(300);

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->width(200);

        $this->addColumn('gateway_transaction_id')
            ->header(__p('payment::phrase.transaction_id'))
            ->width(400);

        $this->addColumn('created_at')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(200);
    }
}
