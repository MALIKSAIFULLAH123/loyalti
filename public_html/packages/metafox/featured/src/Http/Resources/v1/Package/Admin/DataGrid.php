<?php

namespace MetaFox\Featured\Http\Resources\v1\Package\Admin;

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
 */
class DataGrid extends Grid
{
    protected string $appName      = 'featured';
    protected string $resourceName = 'package';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex()
            ->truncateLines();

        $this->addColumn('duration_text')
            ->header(__p('featured::admin.duration'))
            ->width(200)
            ->truncateLines();

        $this->addColumn('is_free')
            ->header(__p('core::web.free'))
            ->width(120)
            ->asYesNoIcon();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->width(120)
            ->asToggleActive();

        $this->addColumn('statistic.total_active')
            ->header(__p('featured::admin.running_items'))
            ->width(150)
            ->asNumber();

        $this->addColumn('statistic.total_end')
            ->header(__p('featured::admin.ended_items'))
            ->width(150)
            ->asNumber();

        $this->addColumn('statistic.total_cancelled')
            ->header(__p('featured::admin.cancelled_items'))
            ->width(150)
            ->asNumber();

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->width(150);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'toggleActive']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();

            $menu->withDelete()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('featured::admin.delete_package_confirmation'),
                ]);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('featured::admin.create_new_package'))
                ->removeAttribute('value')
                ->to('featured/package/create');
        });
    }
}
