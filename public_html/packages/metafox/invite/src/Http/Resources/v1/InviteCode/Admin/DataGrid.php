<?php

namespace MetaFox\Invite\Http\Resources\v1\InviteCode\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Invite\Support\Browse\Scopes\InviteCode\SortScope;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
use MetaFox\Platform\Resource\GridConfig as Grid;
use MetaFox\Platform\Resource\ItemActionMenu;

/**
 * Class DataGrid.
 * @codeCoverageIgnore
 * @ignore
 */
class DataGrid extends Grid
{
    protected string $appName      = 'invite';
    protected string $resourceName = 'invite-code';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);
        $this->setDataSource("/admincp/{$this->appName}/{$this->resourceName}", [
            'q'         => ':q',
            'sort'      => ':sort',
            'sort_type' => ':sort_type',
        ]);

        $this->addColumn('user.display_name')
            ->header(__p('user::phrase.user'))
            ->linkTo('user.url')
            ->target('_blank')
            ->alignCenter()
            ->sortable()
            ->sortableField(SortScope::SORT_FULL_NAME)
            ->flex();

        $this->addColumn('code')
            ->header(__p('user::phrase.invite_code'))
            ->flex();

        $this->addColumn('updated_at')
            ->header(__p('invite::phrase.updated_at'))
            ->asDatetime()
            ->sortable()
            ->sortableField('updated_at')
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getRefreshMenuAction($actions);
            $this->getBatchRefreshMenuAction($actions);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchRefreshMenuAction($menu);
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $menu->addItem('viewInvite')
                ->value(MetaFoxForm::ACTION_ROW_LINK)
                ->label(__p('invite::phrase.view_invites'))
                ->params([
                    'to' => 'invite/invite/browse?user_name=:user_name&status=all',
                ]);

            $this->getRefreshMenuAction($menu);
        });

    }

    protected function getRefreshMenuAction(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('refresh')
                ->reload()
                ->icon('ico-refresh-o')
                ->action('refresh')
                ->label(__p('invite::phrase.refresh_invite_code'))
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM);
        }

        if ($actionMenu instanceof Actions) {
            $actionMenu->add('refresh')
                ->asPatch()
                ->apiUrl('admincp/invite/invite-code/refresh/:id');
        }
    }

    protected function getBatchRefreshMenuAction(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof BatchActionMenu) {
            $actionMenu->addItem('batchRefresh')
                ->action('batchRefresh')
                ->icon('ico-refresh-o')
                ->label(__p('invite::phrase.refresh_invite_code'))
                ->reload()
                ->asBatchEdit();
        }

        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchRefresh')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl('admincp/invite/invite-code/batch-refresh');
        }
    }
}
