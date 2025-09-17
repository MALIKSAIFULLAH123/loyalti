<?php

namespace MetaFox\LiveStreaming\Http\Resources\v1\StreamingService\Admin;

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
    protected string $appName      = 'livestreaming';
    protected string $resourceName = 'streaming-service';

    protected function initialize(): void
    {
        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->linkTo('detail_link')
            ->flex();

        $this->addColumn('driver')
            ->flex()
            ->header(__p('livestreaming::phrase.video_driver'));
    }
}
