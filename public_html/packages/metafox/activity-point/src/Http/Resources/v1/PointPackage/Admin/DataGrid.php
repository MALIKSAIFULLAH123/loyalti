<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointPackage\Admin;

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
 * @driverName activitypoint.package
 * @driverType data-grid
 */
class DataGrid extends Grid
{
    protected string $appName = 'activitypoint';

    protected string $resourceName = 'package';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchPointPackageForm());

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('amount')
            ->header(__p('activitypoint::phrase.points'))
            ->asNumber()
            ->width(200);

        $this->addColumn('price_string')
            ->header(__p('core::phrase.price'))
            ->asPricing();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['toggleActive', 'edit', 'delete', 'destroy']);
            $actions->addEditPageUrl();
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('activitypoint::phrase.add_new_package'))
                ->removeAttribute('value')
                ->to('activitypoint/package/create');
        });
    }
}
