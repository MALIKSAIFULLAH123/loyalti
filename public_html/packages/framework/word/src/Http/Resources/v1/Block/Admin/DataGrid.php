<?php

namespace MetaFox\Word\Http\Resources\v1\Block\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Html\BuiltinAdminSearchForm;
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
    protected string $appName      = 'word';
    protected string $resourceName = 'block';

    protected function initialize(): void
    {
        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->addColumn('word')
            ->header(__p('word::block.word'))
            ->flex();

        $this->addColumn('is_system')
            ->header(__p('core::phrase.system'))
            ->asYesNoIcon();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['delete', 'edit', 'destroy']);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->showWhen(['falsy', 'item.is_system']);
            $menu->withDelete()
                ->showWhen(['falsy', 'item.is_system']);
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('word::phrase.add_word'))
                ->removeAttribute('value')
                ->to('word/block/create');
        });
    }
}
