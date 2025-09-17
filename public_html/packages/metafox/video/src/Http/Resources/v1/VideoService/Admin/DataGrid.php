<?php

namespace MetaFox\Video\Http\Resources\v1\VideoService\Admin;

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
    protected string $appName      = 'video';
    protected string $resourceName = 'service';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->linkTo('detail_link')
            ->truncateLines()
            ->flex();

        $this->addColumn('is_default')
            ->header(__p('core::phrase.default'))
            ->asYesNoIcon()
            ->flex();

        $this->addColumn('driver')
            ->flex()
            ->header(__p('video::phrase.video_driver'));
    }
}
