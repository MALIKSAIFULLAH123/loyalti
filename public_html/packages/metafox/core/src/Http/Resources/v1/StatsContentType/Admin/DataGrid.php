<?php

namespace MetaFox\Core\Http\Resources\v1\StatsContentType\Admin;

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'statistic';
    protected string $resourceName = 'type';

    protected function initialize(): void
    {
        $this->setDataSource(apiUrl('admin.statistic.type.index'));
        $this->sortable();

        $this->addColumn('label')
            ->header(__p('core::phrase.label'))
            ->flex();

        $this->addColumn('icon')
            ->header(__p('app::phrase.icon'))
            ->asIcon()
            ->alignCenter()
            ->setAttribute('sx', ['fontSize' => '24px'])
            ->width(400);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit']);

            $actions->add('orderItem')
                    ->asPost()
                    ->apiUrl(apiUrl('admin.statistic.type.order'));
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
            $menu->withEdit();
            // $menu->withDelete();
        });
    }
}
