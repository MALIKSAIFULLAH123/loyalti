<?php

namespace MetaFox\Schedule\Http\Resources\v1\Job\Admin;

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
    protected string $appName      = 'schedule';
    protected string $resourceName = 'job';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/schedule/job', ['q' => ':q']);

        $this->addColumn('job')
            ->header(__p('schedule::phrase.job'))
            ->flex(1);

        $this->addColumn('schedule')
            ->header(__p('schedule::phrase.schedule'))
            ->tagName('code')
            ->width(200);

        $this->addColumn('next_due')
            ->header(__p('schedule::phrase.next_due'))
            ->flex(1);
    }
}
