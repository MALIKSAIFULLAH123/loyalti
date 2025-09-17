<?php

namespace MetaFox\EMoney\Http\Resources\v1\Transaction;

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
    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'id'            => ':id',
        'from_date'     => ':from_date',
        'to_date'       => ':to_date',
        'status'        => ':status',
        'buyer'         => ':buyer',
        'base_currency' => ':base_currency',
        'source'        => ':source',
        'type'          => ':type',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'id'            => ['truthy', 'id'],
        'from_date'     => ['truthy', 'from_date'],
        'to_date'       => ['truthy', 'to_date'],
        'status'        => ['truthy', 'status'],
        'buyer'         => ['truthy', 'buyer'],
        'base_currency' => ['truthy', 'base_currency'],
        'source'        => ['truthy', 'source'],
        'type'          => ['truthy', 'type'],
    ];

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->setDataSource(apiUrl('emoney.transaction.index'), $this->apiParams, $this->apiRules);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(150);

        $this->addColumn('source')
            ->header(__p('ewallet::web.source'))
            ->width(200);

        $this->addColumn('buyer.full_name')
            ->header(__p('ewallet::web.ewallet_user'))
            ->linkTo('buyer.url')
            ->width(200)
            ->truncateLines();

        $this->addColumn('type')
            ->header(__p('ewallet::web.action'))
            ->width(250);

        $this->addColumn('reference')
            ->header(__p('ewallet::web.reference'))
            ->width(200);

        $this->addColumn('status.label')
            ->header(__p('core::web.status'))
            ->width(200);

        $this->addColumn('gross')
            ->header(__p('ewallet::web.gross'))
            ->width(200);

        $this->addColumn('fee')
            ->header(__p('ewallet::web.fee'))
            ->width(200);

        $this->addColumn('net')
            ->header(__p('ewallet::web.net'))
            ->width(200);

        $this->addColumn('balance.value')
            ->header(__p('ewallet::web.currency_conversion'))
            ->width(200);
    }
}
