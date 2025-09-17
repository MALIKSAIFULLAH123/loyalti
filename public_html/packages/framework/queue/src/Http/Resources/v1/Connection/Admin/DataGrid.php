<?php

namespace MetaFox\Queue\Http\Resources\v1\Connection\Admin;

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
    protected string $appName      = 'queue';
    protected string $resourceName = 'connection';

    protected function initialize(): void
    {
        $this->rowHeight(56);

        $this->addColumn('id')
            ->asId()
            ->width(200);

        $this->addColumn('text')
            ->header(__p('localize::phrase.text_value'))
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
        });
    }
}
