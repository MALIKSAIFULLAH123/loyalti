<?php

namespace Foxexpert\Sevent\Http\Resources\v1\Category\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
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
    protected string $appName      = 'sevent';
    protected string $resourceName = 'category';

    protected function initialize(): void
    {
        $this->sortable();

        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->dynamicRowHeight();

        $this->addColumn('name')
            ->header(__p('core::phrase.name'))
            ->truncateLines()
            ->flex()
            ->linkTo('total_sub_link');

        $this->addColumn('parent.name')
            ->header(__p('core::phrase.parent_category'))
            ->truncateLines()
            ->flex();

        $this->addColumn('is_active')
            ->header(__p('core::phrase.is_active'))
            ->asToggleActive()
            ->width(120);

        $this->addColumn('is_default')
            ->header(__p('core::phrase.default'))
            ->asYesNoIcon()
            ->reload(true)
            ->width(120);

        $this->addColumn('total_sub')
            ->header(__p('core::phrase.sub_categories'))
            ->asNumber()
            ->width(150)
            ->alignCenter()
            ->linkTo('total_sub_link');

        $this->addColumn('total_item')
            ->alignCenter()
            ->header(__p('core::phrase.total_app', ['app' => __p('sevent::phrase.sevents')]))
            ->linkTo('url')
            ->asNumber()
            ->width(150);
        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['delete', 'destroy', 'toggleActive']);

            $actions->add('editItem')
                ->apiUrl('admincp/core/form/sevent.sevent_category.update/:id');

            $actions->add('default')
                ->asPost()
                ->apiUrl(apiUrl('admin.sevent.category.default', ['id' => ':id']));

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/sevent/category/order');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('default')
                ->value(MetaFoxForm::ACTION_ROW_ACTIVE)
                ->params(['action' => 'default'])
                ->reload(true)
                ->showWhen([
                    'and',
                    ['falsy', 'item.is_default'],
                    ['neq', 'item.is_active', 0],
                ])
                ->label(__p('core::phrase.mark_as_default'));

            $menu->withEdit()
                ->params(['action' => 'editItem']);

            $menu->withDeleteForm()
                ->showWhen([
                    'and',
                    ['neq', 'item.is_active', null],
                    ['falsy', 'item.is_default'],
                ]);
        });
    }
}
