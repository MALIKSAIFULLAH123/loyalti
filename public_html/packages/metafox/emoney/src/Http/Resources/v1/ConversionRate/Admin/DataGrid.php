<?php

namespace MetaFox\EMoney\Http\Resources\v1\ConversionRate\Admin;

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
    protected string $appName      = 'emoney';
    protected string $resourceName = 'exchange-rate';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchConversionRateForm());
        $this->addColumn('base')
            ->header(__p('ewallet::admin.base'))
            ->flex();

        $this->addColumn('target')
            ->header(__p('ewallet::admin.target'))
            ->flex();

        $this->addColumn('exchange_rate')
            ->header(__p('ewallet::admin.exchange_rate'))
            ->flex();

        $this->addColumn('auto_synchronized_source')
            ->header(__p('ewallet::admin.source'))
            ->flex()
            ->linkTo('auto_synchronized_source_link');

        $this->addColumn('is_synchronized')
            ->header(__p('ewallet::admin.auto_synchronization'))
            ->asYesNoIcon()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
