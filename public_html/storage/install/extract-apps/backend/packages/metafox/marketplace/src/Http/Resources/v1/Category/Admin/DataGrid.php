<?php

namespace MetaFox\Marketplace\Http\Resources\v1\Category\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Marketplace\Repositories\CategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\DataGrid as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'marketplace';
    protected string $resourceName = 'category';

    protected function getToTalItemColumn(): void
    {
        $this->addColumn('total_item')
            ->asNumber()
            ->header(__p('core::phrase.total_app', ['app' => __p('marketplace::phrase.label_menu_s')]))
            ->linkTo('url')
            ->alignCenter()
            ->width(150);
    }

    protected function categoryRepository(): CategoryRepositoryInterface
    {
        return resolve(CategoryRepositoryInterface::class);
    }
}
