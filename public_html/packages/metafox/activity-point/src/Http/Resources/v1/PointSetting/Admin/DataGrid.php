<?php

namespace MetaFox\ActivityPoint\Http\Resources\v1\PointSetting\Admin;

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
 * @driverName activitypoint.setting
 * @driverType data-grid
 */
class DataGrid extends Grid
{
    protected string $appName      = 'activitypoint';
    protected string $resourceName = 'setting';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->setSearchForm(new SearchPointSettingForm());

        $this->setRowsPerPage(20, [10, 20, 50]);

        $this->setDataSource(apiUrl('admin.activitypoint.setting.index'), [
            'module_id' => ':module_id',
            'role_id'   => ':role_id',
        ]);

        $this->addColumn('module_id')
            ->header(__p('core::phrase.package_name'))
            ->minWidth(150);

        $this->addColumn('description')
            ->header(__p('core::phrase.action'))
            ->flex();

        $this->addColumn('role')
            ->header(__p('core::phrase.role'))
            ->width(200);

        $this->addColumn('points')
            ->header(__p('activitypoint::phrase.earn_point'))
            ->asNumber()
            ->width(130);

        $this->addColumn('max_earned')
            ->header(__p('activitypoint::phrase.max_earn_point'))
            ->asNumber()
            ->width(150);

        $this->addColumn('period')
            ->header(__p('activitypoint::phrase.period_in_day'))
            ->asNumber()
            ->width(100);

        $this->addColumn('is_active')
            ->asToggleActive()
            ->header(__p('core::phrase.is_active'));

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'toggleActive', 'destroy']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
