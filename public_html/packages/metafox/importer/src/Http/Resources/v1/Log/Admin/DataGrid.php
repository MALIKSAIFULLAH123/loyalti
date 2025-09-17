<?php

namespace MetaFox\Importer\Http\Resources\v1\Log\Admin;

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
    protected string $appName      = 'importer';
    protected string $resourceName = 'history';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setSearchForm(new SearchLogForm());

        $this->addColumn('env')
            ->header(__p('importer::phrase.env'))
            ->width(120);

        $this->addColumn('timestamp')
            ->header(__p('log::msg.timestamp'))
            ->width(200);

        $this->addColumn('level')
            ->header(__p('log::msg.level'))
            ->width(120);

        $this->addColumn('message')
            ->header(__p('log::msg.message'))
            ->truncateLines()
            ->flex(1);
    }
}
