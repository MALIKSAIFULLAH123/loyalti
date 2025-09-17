<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumPost\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
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
    protected string $appName      = 'forum';
    protected string $resourceName = 'post';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource("/admincp/{$this->appName}/{$this->resourceName}");

        $this->addColumn('short_content')
            ->header(__p('core::phrase.content_label'))
            ->linkTo('url')
            ->target('_blank')
            ->truncateLines(5)
            ->flex();

        $this->addColumn('user.display_name')
            ->header(__p('core::phrase.posted_by'))
            ->linkTo('user.url')
            ->target('_blank')
            ->alignCenter()
            ->width(200);

        $this->addColumn('thread.title')
            ->header(__p('forum::phrase.thread'))
            ->alignCenter()
            ->truncateLines(5)
            ->width(200);

        $this->addColumn('is_approved')
            ->header(__p('core::phrase.approved'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField('recent')
            ->width(250);

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getApproveActionMenu($actions);
            $this->getBatchApproveActionMenu($actions);
            $this->getBatchDeleteActionMenu($actions);
            $this->getDeleteActionMenu($actions);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchApproveActionMenu($menu);
            $this->getBatchDeleteActionMenu($menu);
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getApproveActionMenu($menu);
            $this->getDeleteActionMenu($menu);
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'total_value_forum_posts',
        ]);
    }

    protected function getBatchApproveActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchApprove')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/batch-approve?id=[:id]");
        }

        if ($actionMenu instanceof BatchActionMenu) {
            $actionMenu->addItem('batchApprove')
                ->action('batchApprove')
                ->icon('ico-check-circle-o')
                ->label(__p('core::phrase.approve'))
                ->reload()
                ->asBatchEdit();
        }
    }

    protected function getBatchDeleteActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchDelete')
                ->asDelete()
                ->asFormDialog(false)
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/batch-delete?id=[:id]")
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('forum::phrase.are_you_sure_you_want_to_delete_this_post_permanently'),
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

    protected function getDeleteActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('deleteItem')
                ->asDelete()
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->withDelete()
                ->params(['action' => 'deleteItem'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('forum::phrase.are_you_sure_you_want_to_delete_this_post_permanently'),
                ]);
        }
    }

    protected function getApproveActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('approve')
                ->asPatch()
                ->apiUrl("{$this->appName}-{$this->resourceName}/approve/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('approve')
                ->action('approve')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.approve'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_approve'],
                ]);
        }
    }
}
