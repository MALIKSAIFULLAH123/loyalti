<?php

namespace MetaFox\Advertise\Http\Resources\v1\InvoiceTransaction;

use MetaFox\Platform\Resource\GridConfig as Grid;

class TransactionDataGrid extends Grid
{
    protected string $appName = 'advertise';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/advertise/invoice/:id/transaction', [], []);

        $this->addColumn('created_at')
            ->header(__p('advertise::phrase.transaction_date'))
            ->flex()
            ->asDateTime();

        $this->addColumn('amount')
            ->header(__p('advertise::phrase.amount'))
            ->width(250);

        $this->addColumn('payment_method')
            ->header(__p('advertise::phrase.payment_method'))
            ->width(300);

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->width(250);

        $this->addColumn('transaction_id')
            ->header(__p('advertise::phrase.transaction_id'))
            ->width(350);
    }
}
