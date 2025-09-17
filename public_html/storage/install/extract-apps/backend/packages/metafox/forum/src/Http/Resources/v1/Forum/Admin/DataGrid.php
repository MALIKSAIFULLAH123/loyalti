<?php

namespace MetaFox\Forum\Http\Resources\v1\Forum\Admin;

use MetaFox\Form\Constants;
use MetaFox\Form\Html\BuiltinAdminSearchForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\GridActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

class DataGrid extends Grid
{
    protected string $appName      = 'forum';
    protected string $resourceName = 'forum';

    protected function initialize(): void
    {
        $this->sortable();

        $this->setSearchForm(new BuiltinAdminSearchForm());

        $this->dynamicRowHeight();

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->truncateLines()
            ->flex();

        $this->addColumn('is_closed')
            ->header(__p('core::web.closed'))
            ->width(120)
            ->asToggleActive();

        $this->addColumn('statistic.total_sub_forum')
            ->header(__p('forum::phrase.total_subs'))
            ->asNumber()
            ->width(150)
            ->alignCenter()
            ->linkTo('sub_link');

        $this->addColumn('statistic.total_thread')
            ->header(__p('forum::phrase.total_threads'))
            ->asNumber()
            ->width(150)
            ->alignCenter()
            ->linkTo('url');

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['edit', 'toggleActive']);

            $actions->add('getDeleteForm')
                ->apiUrl('admincp/core/form/forum.delete/:id');

            $actions->add('orderItem')
                ->apiUrl('admincp/forum/forum/order')
                ->asPost();

            $actions->add('getModeratorForm')
                ->apiUrl('admincp/core/form/forum.setup_moderator/:id');

            $actions->add('getPermissionForm')
                ->apiUrl('admincp/core/form/forum.setup_permissions/:id');
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withEdit()
                ->reload();

            $menu->addItem('setup_moderator_form')
                ->icon('ico-user')
                ->value(Constants::ACTION_ROW_EDIT)
                ->label(__p('forum::phrase.manage_moderators'))
                ->action('getModeratorForm')
                ->reload();

            $menu->addItem('setup_permission_form')
                ->icon('ico-user')
                ->value(Constants::ACTION_ROW_EDIT)
                ->label(__p('forum::phrase.manage_permissions'))
                ->action('getPermissionForm')
                ->reload();

            $menu->addItem('delete_form')
                ->icon('ico-trash-o')
                ->value(Constants::ACTION_ROW_EDIT)
                ->label(__p('core::phrase.delete'))
                ->action('getDeleteForm')
                ->reload();
        });

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->withCreate()
                ->label(__p('forum::phrase.create_new_forum'))
                ->removeAttribute('value')
                ->to('forum/forum/create');
        });
    }
}
