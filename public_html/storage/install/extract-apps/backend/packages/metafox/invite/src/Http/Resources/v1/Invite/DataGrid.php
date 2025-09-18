<?php

namespace MetaFox\Invite\Http\Resources\v1\Invite;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Invite\Policies\InvitePolicy;
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
    protected bool $isAdminCP = false;

    protected array $apiParams = [
        'q'          => ':q',
        'status'     => ':status',
        'start_date' => ':start_date',
        'end_date'   => ':end_date',
    ];

    protected array $apiRules = [
        'q'          => ['truthy', 'q'],
        'status'     => ['truthy', 'status'],
        'end_date'   => ['truthy', 'end_date'],
        'start_date' => ['truthy', 'start_date'],
    ];

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->setDataSource('/invite', $this->apiParams, $this->apiRules);

        $this->addColumn('address')
            ->header(__p('invite::web.email_phone'))
            ->flex();

        $this->addColumn('status_info')
            ->header(__p('core::phrase.status'))
            ->asColoredText()
            ->flex();

        $this->addColumn('created_at')
            ->header(__p('invite::phrase.invite_date'))
            ->asDateTime()
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getResendActionMenu($actions);
            $this->getDeleteActionMenu($actions);
            $this->getBatchResentActionMenu($actions);
            $this->getBatchDeleteActionMenu($actions);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchResentActionMenu($menu);
            $this->getBatchDeleteActionMenu($menu);
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getResendActionMenu($menu);
            $this->getDeleteActionMenu($menu);
        });

    }

    protected function getResendActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('resend')
                ->label(__p('invite::phrase.resend'))
                ->action('resend')
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_create'],
                    ['truthy', 'item.is_pending'],
                ])
                ->asEditRow();
        }

        if ($actionMenu instanceof Actions) {
            $actionMenu->add('resend')
                ->asPut()
                ->asFormDialog(false)
                ->apiUrl('invite/resend/:id');
        }
    }

    protected function getDeleteActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('deleteItem')
                ->asDelete()
                ->apiUrl("invite/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->withDelete()
                ->params(['action' => 'deleteItem'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('invite::phrase.delete_confirm'),
                ]);
        }
    }

    protected function getBatchResentActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if (!policy_check(InvitePolicy::class, 'create', user())) {
            return;
        }

        if ($actionMenu instanceof BatchActionMenu) {
            $actionMenu->addItem('batchResend')
                ->label(__p('invite::phrase.resend'))
                ->action('batchResend')
                ->icon('ico-forward-o')
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_create'],
                ])->asBatchEdit();
        }

        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchResend')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl('invite/batch-resend?id=[:id]');
        }
    }

    protected function getBatchDeleteActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchDelete')
                ->asFormDialog(false)
                ->asDelete()
                ->apiUrl("invite/batch-delete?id=[:id]")
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
