<?php

namespace MetaFox\Menu\Http\Resources\v1\Menu\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 * @SuppressWarnings(PHPMD.UnusedFormalParameter)
 */
class DataGrid extends Grid
{
    protected string $appName      = 'menu';
    protected string $resourceName = 'menu';

    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.menu.menu.index'), [
            'q'          => ':q',
            'package_id' => ':package_id',
            'resolution' => ':resolution',
        ]);

        $this->addColumn('name')
            ->flex(2)
            ->header(__p('menu::phrase.menu_key_name'))
            ->linkTo('url');

        $this->addColumn('title')
            ->flex(2)
            ->header(__p('core::phrase.title'));

        $this->addColumn('resolution')
            ->header(__p('core::phrase.resolution'))
            ->flex();

        $this->addColumn('app_name')
            ->header(__p('core::phrase.package_name'))
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            // does not allow edit or delete menus ?
            if ('local' === config('app.env')) {
                $menu->withDelete();
            }
        });
    }

    public function boot(): void
    {
        $this->bootGridMenu();
    }

    protected function bootGridMenu(): void
    {
        if ('local' !== config('app.env')) {
            return;
        }

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate(__p('menu::phrase.add_new_item'));
        });

        $this->withActions(function (Actions $actions) {
            $actions->add('addItem')->apiUrl(apiUrl('admin.menu.menu.create'));
        });
    }
}
