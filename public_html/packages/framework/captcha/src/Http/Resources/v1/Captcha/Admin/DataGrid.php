<?php

namespace MetaFox\Captcha\Http\Resources\v1\Captcha\Admin;

use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends Grid
{
    protected string $appName = 'captcha';

    protected string $resourceName = 'type';

    protected function initialize(): void
    {
        $this->dynamicRowHeight();

        $this->addColumn('id')
            ->asId()
            ->width(160);

        $this->addColumn('description')
            ->header(__p('core::phrase.description'))
            ->truncateLines()
            ->flex(1);

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->add('edit')
                ->asFormDialog(false)
                ->link('links.editItem');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->showWhen(['truthy', 'item.extra.can_edit']);
        });
    }
}
