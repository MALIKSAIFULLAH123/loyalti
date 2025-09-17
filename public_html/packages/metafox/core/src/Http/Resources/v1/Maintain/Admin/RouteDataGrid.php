<?php

namespace MetaFox\Core\Http\Resources\v1\Maintain\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class RouteDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class RouteDataGrid extends Grid
{
    protected function initialize(): void
    {
        $this->inlineSearch(['uri']);
        $this->setSearchForm(new BuiltinAdminSearchForm());
        $this->dynamicRowHeight();
        $this->setDataSource('admincp/core/maintain/routes', ['q' => ':q']);

        $this->addColumn('id')
            ->header(__p('core::phrase.id'))
            ->width(70);

        $this->addColumn('uri')
            ->header(__p('core::phrase.url'))
            ->width(300);

        $this->addColumn('method')
            ->header(__p('core::phrase.method'))
            ->alignCenter()
            ->width(200);

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->width(300);

        $this->addColumn('action')
            ->header('Action')
            ->flex(1, 500);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            // $actions->addDefaults(['edit','delete']);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            // $menu->asIconLabel();
            // $menu->withDelete();
            // $menu->withCreate(__p('core::phrase.add_new_phrase'));
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
