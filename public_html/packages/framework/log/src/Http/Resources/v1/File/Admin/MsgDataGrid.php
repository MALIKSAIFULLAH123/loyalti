<?php

namespace MetaFox\Log\Http\Resources\v1\File\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\GridConfig as Grid;

/**
 * Class MsgDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class MsgDataGrid extends Grid
{
    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.log.file.msg'), ['q' => ':q']);
        $this->dynamicRowHeight();

        $this->addColumn('env')
            ->header(__p('log::msg.env'))
            ->width(120);

        $this->addColumn('timestamp')
            ->header(__p('log::msg.timestamp'))
            ->asDateTime();

        $this->addColumn('level')
            ->header(__p('log::msg.level'))
            ->width(120);

        $this->addColumn('message')
            ->header(__p('log::msg.message'))
            ->truncateLines()
            ->flex(1);
    }
}
