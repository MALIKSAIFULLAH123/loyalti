<?php

namespace MetaFox\Video\Http\Resources\v1\Category\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Http\Resources\v1\Category\Admin\DataGrid as Grid;
use MetaFox\Video\Repositories\CategoryRepositoryInterface;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'video';
    protected string $resourceName = 'category';

    protected function getToTalItemColumn(): void
    {
        $this->addColumn('total_item')
            ->header(__p('core::phrase.total_app', ['app' => __p('video::phrase.videos')]))
            ->linkTo('url')
            ->alignCenter()
            ->asNumber()
            ->width(150);
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
