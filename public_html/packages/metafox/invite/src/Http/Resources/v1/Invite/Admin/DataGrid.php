<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Invite\Support\Browse\Scopes\Invite\SortScope;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;
use MetaFox\Platform\Support\Browse\Browse;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'invite';
    protected string $resourceName = 'invite';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->setSearchForm(new SearchInviteForm());
        $this->setDataSource('/admincp/invite/invite', [
            'q'          => ':q',
            'status'     => ':status',
            'user_name'  => ':user_name',
            'owner_name' => ':owner_name',
            'sort'       => ':sort',
            'sort_type'  => ':sort_type',
        ]);

        $this->addColumn('user.display_name')
            ->header(__p('invite::phrase.inviter'))
            ->flex()
            ->linkTo('user.url')
            ->sortable()
            ->sortableField(SortScope::SORT_FULL_NAME_COLUMN)
            ->target('_blank');

        $this->addColumn('address')
            ->header(__p('invite::phrase.invite_email_phone'))
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('invite::phrase.invite_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField(Browse::SORT_RECENT)
            ->flex();

        $this->addColumn('status')
            ->header(__p('core::phrase.status'))
            ->flex();

        $this->addColumn('owner.display_name')
            ->header(__p('invite::phrase.invitee'))
            ->flex()
            ->linkTo('owner.url')
            ->target('_blank');

        $this->addColumn('owner.created_at')
            ->header(__p('invite::phrase.signup_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField(SortScope::SORT_SIGNUP_DATE_COLUMN)
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $actions->addActions(['destroy']);
            $this->getBatchDeleteActionMenu($actions);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchDeleteActionMenu($menu);
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->withDelete()
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('invite::phrase.delete_confirm'),
                ]);
        });

    }

    protected function getBatchDeleteActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchDelete')
                ->asFormDialog(false)
                ->asDelete()
                ->apiUrl("admincp/invite/invite/batch-delete")
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('invite::phrase.delete_confirm'),
                ]);
        }

        if ($actionMenu instanceof BatchActionMenu) {
            $actionMenu->addItem('batchDelete')
                ->action('batchDelete')
                ->icon('ico-trash-o')
                ->label(__p('core::phrase.delete'))
                ->reload()
                ->asBatchEdit();
        }
    }
}
