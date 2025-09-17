<?php

namespace MetaFox\GettingStarted\Http\Resources\v1\TodoList\Admin;

use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends Grid
{
    protected string $appName      = 'getting-started';
    protected string $resourceName = 'todo-list';

    public function initialize(): void
    {
        $this->sortable();

        $this->setSearchForm(new SearchTodoListForm());

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.name'))
            ->truncateLines()
            ->flex();

        $this->addColumn('description')
            ->header(__p('core::phrase.description'))
            ->truncateLines()
            ->flex();

        $this->addColumn('resolution')
            ->header(__p('core::phrase.resolution'))
            ->truncateLines()
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'delete', 'destroy']);
            $actions->addEditPageUrl();

            $actions->add('orderItem')
                ->asPost()
                ->apiUrl('admincp/getting-started/todo-list/order');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit();
            $menu->withDelete();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->addItem('addItem')
                ->icon('ico-plus')
                ->label(__p('getting-started::phrase.add_todo_list'))
                ->disabled(false)
                ->to('getting-started/todo-list/create')
                ->params(['action' => 'addItem']);
        });
    }
}
