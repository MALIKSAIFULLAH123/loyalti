<?php

namespace MetaFox\Translation\Http\Resources\v1\TranslationGateway\Admin;

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends Grid
{
    protected string $appName = 'translation';
    protected string $resourceName = 'gateway';

    protected function initialize(): void
    {
        $this->title(__p('translation:phrase.manage_gateways'));
        $this->inlineSearch(['title']);

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
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
