<?php

namespace MetaFox\Layout\Http\Resources\v1\Variant\Admin;

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
    protected string $appName      = 'layout';
    protected string $resourceName = 'variant';

    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.layout.variant.index', []), ['theme_id' => ':theme_id']);
        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->flex(1);

        $this->addColumn('preview')
            ->header(__p('core::phrase.thumbnail'))
            ->asPreviewUrl()
            ->alignCenter()
            ->width(200);

//        $this->addColumn('theme_title')
//            ->header(__p('layout::phrase.theme'))
//            ->linkTo('links.viewTheme')
//            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        $this->addColumn('is_default')
            ->header(__p('core::phrase.default'))
            ->asToggleDefault()
            ->reload(true);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy', 'toggleActive', 'edit', 'update', 'toggleDefault']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete()->showWhen(['truthy', 'item.can_delete']);
        });
    }
}
