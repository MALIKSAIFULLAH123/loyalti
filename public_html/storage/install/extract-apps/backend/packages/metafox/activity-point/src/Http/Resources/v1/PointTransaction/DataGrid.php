<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointTransaction;

use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class DataGrid.
 * @ignore
 * @driverName activitypoint.transaction
 * @driverName data-grid
 */
class DataGrid extends Grid
{

    public bool $isAdminCP = false;

    /**
     * @var array|string[]
     */
    protected array $apiParams = [
        'q'         => ':q',
        'type'      => ':type',
        'from'      => ':from',
        'to'        => ':to',
        'sort'      => ':sort',
        'sort_type' => ':sort_type',
        'page'      => ':page',
        'limit'     => ':limit',
    ];

    /**
     * @var array|array[]
     */
    protected array $apiRules = [
        'q'         => ['truthy', 'q'],
        'type'      => ['truthy', 'type'],
        'from'      => ['truthy', 'from'],
        'to'        => ['truthy', 'to'],
        'sort'      => ['truthy', 'sort'],
        'sort_type' => ['truthy', 'sort_type'],
        'page'      => ['truthy', 'page'],
        'limit'     => ['truthy', 'limit'],
    ];

    protected function initialize(): void
    {
        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource(apiUrl('activitypoint.transaction.index'), $this->apiParams, $this->apiRules);

        $this->addColumn('package_name')
            ->header(__p('core::phrase.package_name'))
            ->flex();

        $this->addColumn('points')
            ->header(__p('activitypoint::phrase.points'))
            ->flex();

        $this->addColumn('action_type')
            ->header(__p('activitypoint::web.action'))
            ->tooltip('action_tooltip')
            ->flex();

        $this->addColumn('type_name')
            ->header(__p('activitypoint::web.type'))
            ->width(200);

        $this->addColumn('id')
            ->header(__p('activitypoint::web.id'))
            ->width(150);

        $this->addColumn('creation_date')
            ->header(__p('activitypoint::phrase.date'))
            ->asDateTime()
            ->flex();
    }
}
