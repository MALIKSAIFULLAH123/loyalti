<?php

namespace MetaFox\Layout\Http\Resources\v1\Snippet\Admin;

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
    protected string $appName = 'layout';
    protected string $resourceName = 'snippet';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/layout/snippet', ['q' => ':q']);

        $this->addColumn('id')
            ->header('ID')
            ->width(80);

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('theme')
            ->header(__p('layout::phrase.theme'))
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        $this->addColumn('created_at')
            ->header(__p('layout::phrase.created_at'))
            ->asDateTime();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy', 'delete', 'toggleActive']);
            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.revision');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()->label(__p('layout::phrase.show_history'));
            $menu->withDelete();
        });
    }
}
