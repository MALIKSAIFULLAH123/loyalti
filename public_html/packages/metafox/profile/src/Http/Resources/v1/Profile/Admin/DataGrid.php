<?php

namespace MetaFox\Profile\Http\Resources\v1\Profile\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'profile';
    protected string $resourceName = 'profile';

    protected function initialize(): void
    {
        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('profile_type')
            ->header(__p('core::phrase.name'))
            ->width(200)
            ->linkTo('links.structure');

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);
            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.editItem');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });
    }
}
