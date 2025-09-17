<?php

namespace MetaFox\Group\Http\Resources\v1\ExampleRule\Admin;

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
    protected string $appName      = 'group';
    protected string $resourceName = 'example-rule';

    protected function initialize(): void
    {
        $this->sortable();

        $this->inlineSearch(['id', 'title']);

        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->linkTo('total_sub_link')
            ->flex();

        $this->addColumn('description')
            ->header(__p('core::phrase.description'))
            ->flex()
            ->linkTo('total_sub_link');

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(120);
        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'destroy', 'toggleActive']);

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl(apiUrl('admin.group.example-rule.order'));
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
                ->label(__p('group::phrase.create_example_group_rule'))
                ->removeAttribute('value')
                ->to('group/example-rule/create');
        });
    }
}
