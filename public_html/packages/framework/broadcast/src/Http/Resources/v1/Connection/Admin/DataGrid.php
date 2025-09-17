<?php

namespace MetaFox\Broadcast\Http\Resources\v1\Connection\Admin;

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
    protected string $appName = 'broadcast';
    protected string $resourceName = 'driver';

    protected function initialize(): void
    {
        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('title')
            ->header(__p('broadcast::phrase.driver'))
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.editUrl');

        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
