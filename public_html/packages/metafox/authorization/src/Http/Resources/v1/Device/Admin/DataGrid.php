<?php

namespace MetaFox\Authorization\Http\Resources\v1\Device\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
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
    protected string $appName      = 'authorization';
    protected string $resourceName = 'device';

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());
        $this->dynamicRowHeight();

        $this->addColumn('device_id')
            ->header(__p('authorization::phrase.device_id'))
            ->truncateLines()
            ->flex();

        $this->addColumn('device_uid')
            ->header(__p('authorization::phrase.device_uid'))
            ->truncateLines()
            ->flex();

        $this->addColumn('platform')
            ->header(__p('authorization::phrase.device_platform'))
            ->truncateLines()
            ->flex();

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.created_at'))
            ->asDateTime()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete(__p('authorization::phrase.remove_device'));
        });
    }
}
