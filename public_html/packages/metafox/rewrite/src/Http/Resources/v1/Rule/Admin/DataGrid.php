<?php

namespace MetaFox\Rewrite\Http\Resources\v1\Rule\Admin;

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
    protected string $appName      = 'rewrite';
    protected string $resourceName = 'rule';

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('from_path')
            ->header(__p('rewrite::phrase.from_path'))
            ->flex();

        $this->addColumn('to_path')
            ->header(__p('rewrite::phrase.to_path'))
            ->flex();

        $this->addColumn('to_mobile_path')
            ->header(__p('rewrite::phrase.to_mobile_path'))
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });
    }
}
