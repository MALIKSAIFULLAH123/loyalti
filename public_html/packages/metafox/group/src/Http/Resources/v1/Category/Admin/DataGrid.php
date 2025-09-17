<?php

namespace MetaFox\Group\Http\Resources\v1\Category\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Group\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\DataGrid as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'group';
    protected string $resourceName = 'category';

    protected function getToTalItemColumn(): void
    {
        $this->addColumn('total_item')
            ->header(__p('core::phrase.total_app', ['app' => __p('group::phrase.groups')]))
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
