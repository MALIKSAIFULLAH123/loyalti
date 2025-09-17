<?php

namespace MetaFox\Profile\Http\Resources\v1\Profile\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class StructureDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class StructureDataGrid extends Grid
{
    protected string $appName      = 'profile';
    protected string $resourceName = 'structure';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/profile/structure', ['q' => ':q']);

        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('key')
            ->header(__p('core::phrase.name'))
            ->width(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->asButton();
            // $menu->withDelete();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            // $menu->withEdit();
            // $menu->withDelete();
        });
    }
}
