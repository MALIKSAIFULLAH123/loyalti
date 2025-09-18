<?php

namespace MetaFox\Marketplace\Http\Resources\v1\InvoiceTransaction;

use MetaFox\Platform\Resource\GridConfig as Grid;

class TransactionDataGrid extends Grid
{
    protected string $appName = 'marketplace';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/marketplace/invoice/:id/transaction', [], []);

        $this->addColumn('creation_date')
            ->header(__p('marketplace::web.transaction_date'))
            ->flex()
            ->asDateTime();

        $this->addColumn('price')
            ->header(__p('marketplace::web.amount'))
            ->width(250);

        $this->addColumn('payment_method')
            ->header(__p('marketplace::phrase.payment_method'))
            ->width(300);

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->width(250);

        $this->addColumn('transaction_id')
            ->header(__p('marketplace::phrase.transaction_id'))
            ->width(350);
    }
}
