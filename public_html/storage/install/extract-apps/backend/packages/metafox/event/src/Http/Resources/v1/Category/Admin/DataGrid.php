<?php

namespace MetaFox\Event\Http\Resources\v1\Category\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Event\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\DataGrid as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'event';
    protected string $resourceName = 'category';

    protected function getToTalItemColumn(): void
    {
        $this->addColumn('total_item')
            ->header(__p('core::phrase.total_app', ['app' => __p('event::phrase.events')]))
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
