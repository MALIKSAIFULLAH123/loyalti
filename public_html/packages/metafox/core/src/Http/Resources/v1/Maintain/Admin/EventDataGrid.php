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
 * Class EventDataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class EventDataGrid extends Grid
{
    protected function initialize(): void
    {
        // $this->enableCheckboxSelection(true);
        $this->inlineSearch(['Event']);
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->setDataSource('admincp/core/maintain/events', ['q' => ':q']);

        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('Event')
            ->header('Event')
            ->width(400);

        $this->addColumn('Listener')
            ->header('Listener')
            ->flex();

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
