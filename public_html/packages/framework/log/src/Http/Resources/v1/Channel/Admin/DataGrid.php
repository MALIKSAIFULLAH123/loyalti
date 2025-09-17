<?php

namespace MetaFox\Log\Http\Resources\v1\Channel\Admin;

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
    protected string $appName      = 'log';
    protected string $resourceName = 'channel';

    protected function initialize(): void
    {
        $this->rowHeight(56);

        $this->addColumn('id')
            ->asId();

        $this->addColumn('driver')
            ->header(__p('log::phrase.driver_name'))
            ->flex(1);

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
            $menu->withDelete()
                ->showWhen(['truthy', 'item.can_delete']);
        });
    }
}
