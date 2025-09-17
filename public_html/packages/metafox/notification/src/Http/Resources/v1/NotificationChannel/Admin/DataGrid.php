<?php

namespace MetaFox\Notification\Http\Resources\v1\NotificationChannel\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

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
    protected string $appName      = 'notification';
    protected string $resourceName = 'channel';

    protected function initialize(): void
    {
        $this->setDataSource('/admincp/notification/channel', ['q' => ':q']);
        $this->setSearchForm(new SearchChannelForm());
        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();

        $this->addColumn('is_ready')
            ->header(__p('notification::phrase.ready'))
            ->asYesNoIcon()
            ->width(200);

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->fieldDisabled('disable')
            ->width(200);

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['toggleActive']);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {});

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {});

    }
}
