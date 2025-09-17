<?php

namespace MetaFox\Log\Http\Resources\v1\LogMessage\Admin;

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
    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.log.db.msg'));
        $this->dynamicRowHeight();

        $this->addColumn('env')
            ->header(__p('log::msg.env'))
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
