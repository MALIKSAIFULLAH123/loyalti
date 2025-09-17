<?php

namespace MetaFox\Cache\Http\Resources\v1\Store\Admin;

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
    protected string $appName = 'cache';

    protected string $resourceName = 'store';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('id')
            ->asId()
            ->width(200);

        $this->addColumn('text')
            ->header(__p('core::phrase.description'))
            ->flex(1)
            ->truncateLines();
        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.editItem');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->showWhen(['truthy', 'item.can_edit']);
        });
    }
}
