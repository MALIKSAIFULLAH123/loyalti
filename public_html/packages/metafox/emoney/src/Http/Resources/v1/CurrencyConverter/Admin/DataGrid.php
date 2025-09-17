<?php

namespace MetaFox\EMoney\Http\Resources\v1\CurrencyConverter\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
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
    protected string $appName      = 'emoney';
    protected string $resourceName = 'conversion-provider';

    protected function initialize(): void
    {
        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex();

        $this->addColumn('link')
            ->header(__p('ewallet::admin.link'))
            ->flex()
            ->linkTo('link')
            ->target('_blank');

        $this->addColumn('is_default')
            ->header(__p('core::web.default_ucfirst'))
            ->asToggleDefault()
            ->width(120);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'toggleDefault']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
        });
    }
}
