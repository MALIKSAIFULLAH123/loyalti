<?php

namespace MetaFox\Storage\Http\Resources\v1\Disk\Admin;

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
 * @driverName storage.disk
 */
class DataGrid extends Grid
{
    protected string $appName      = 'storage';
    protected string $resourceName = 'disk';

    protected function initialize(): void
    {
        $this->addColumn('label')
            ->header(__p('storage::phrase.disk_label'))
            ->flex(1);

        $this->addColumn('title')
            ->header(__p('storage::phrase.disk_title'))
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->showWhen(['truthy', 'item.can_edit']);

            $menu->withDelete()
                ->confirm(['message' => __p('core::phrase.are_you_absolutely_sure_this_operation_cannot_be_undone')])
                ->showWhen(['truthy', 'item.can_delete']);
        });
    }
}
