<?php

namespace MetaFox\InAppPurchase\Http\Resources\v1\Product\Admin;

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
    protected string $appName      = 'in-app-purchase';
    protected string $resourceName = 'product';

    protected function initialize(): void
    {
        $this->setSearchForm(new SearchProductForm());

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->flex()
            ->truncateLines();
        $this->addColumn('price')
            ->header(__p('core::phrase.price'))
            ->asPricing();
        $this->addColumn('type')
            ->header(__p('core::phrase.type'))
            ->linkTo('url')
            ->width(200);
        $this->addColumn('is_recurring')
            ->header(__p('in-app-purchase::phrase.recurring'))
            ->asYesNoIcon()
            ->width(80);
        $this->addColumn('ios_product_id')
            ->header(__p('in-app-purchase::phrase.ios_product_id'))
            ->flex();
        $this->addColumn('android_product_id')
            ->header(__p('in-app-purchase::phrase.android_product_id'))
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
