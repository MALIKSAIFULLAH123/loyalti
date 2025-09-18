<?php

namespace MetaFox\Page\Http\Resources\v1\PageCategory\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Page\Repositories\PageCategoryRepositoryInterface;
use MetaFox\Platform\Http\Resources\v1\Category\Admin\DataGrid as Grid;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'page';
    protected string $resourceName = 'category';

    protected function getToTalItemColumn(): void
    {
        $this->addColumn('total_item')
            ->header(__p('core::phrase.total_app', ['app' => __p('page::phrase.pages')]))
            ->linkTo('url')
            ->alignCenter()
            ->asNumber()
            ->width(150);
    }

    protected function categoryRepository(): PageCategoryRepositoryInterface
    {
        return resolve(PageCategoryRepositoryInterface::class);
    }
}
