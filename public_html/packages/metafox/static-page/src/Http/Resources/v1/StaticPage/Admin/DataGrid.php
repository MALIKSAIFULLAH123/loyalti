<?php

namespace MetaFox\StaticPage\Http\Resources\v1\StaticPage\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'static-page';
    protected string $resourceName = 'page';

    protected function initialize(): void
    {
        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();

        $this->addColumn('slug')
            ->header(__p('static-page::phrase.slug'))
            ->linkTo('url')
            ->target('_blank')
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy']);

            $actions->add('edit')
                ->asFormDialog(false)
                ->pageUrl('static-page/page/edit/:id');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('static-page::phrase.create_page'))
                ->removeAttribute('value')
                ->to('static-page/page/create');
        });
    }
}
