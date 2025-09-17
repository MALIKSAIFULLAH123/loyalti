<?php

namespace MetaFox\Payment\Http\Resources\v1\Gateway\Admin;

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
 * @driverName payment.gateway
 */
class DataGrid extends Grid
{
    protected string $appName      = 'payment';
    protected string $resourceName = 'gateway';

    protected function initialize(): void
    {
        $this->title(__p('payment:phrase.manage_gateways'));
        $this->inlineSearch(['title']);

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->minWidth(200);

        $this->addColumn('is_test')
            ->header(__p('payment::phrase.is_test'))
            ->asYesNoIcon()
            ->minWidth(200);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy', 'toggleActive']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
