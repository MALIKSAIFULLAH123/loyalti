<?php

namespace MetaFox\Mobile\Http\Resources\v1\AdMobConfig\Admin;

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
    protected string $appName      = 'mobile';
    protected string $resourceName = 'admob';

    protected function initialize(): void
    {
        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('type_name')
            ->header(__p('core::phrase.type'))
            ->flex();

        $this->addColumn('frequency_capping_title')
            ->header(__p('mobile::phrase.frequency_capping'))
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->flex();

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
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('mobile::phrase.add_ad_config'))
                ->removeAttribute('value')
                ->showWhen([
                    'and',
                    ['truthy', 'settings.mobile.has_available_page'],
                ])
                ->to('mobile/admob/create');
        });
    }
}
