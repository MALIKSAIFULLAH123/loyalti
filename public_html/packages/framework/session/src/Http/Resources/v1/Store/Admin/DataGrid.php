<?php

namespace MetaFox\Session\Http\Resources\v1\Store\Admin;

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
 * @driverType  data-grid
 * @driverName  session.store
 */
class DataGrid extends Grid
{
    protected string $appName      = 'session';
    protected string $resourceName = 'store';

    protected function initialize(): void
    {
        $this->addColumn('id')->asId();

        $this->addColumn('text')
            ->header(__p('core::phrase.description'))
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
