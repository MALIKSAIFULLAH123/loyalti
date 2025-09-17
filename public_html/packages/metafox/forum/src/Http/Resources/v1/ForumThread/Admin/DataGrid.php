<?php

namespace MetaFox\Forum\Http\Resources\v1\ForumThread\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Forum\Support\Browse\Scopes\ThreadSortScope;
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
    protected string $resourceName = 'thread';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource("/admincp/{$this->appName}/{$this->resourceName}");

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->linkTo('url')
            ->target('_blank')
            ->flex();

        $this->addColumn('short_description')
            ->header(__p('core::phrase.description'))
            ->truncateLines(5)
            ->flex();

        $this->addColumn('user.display_name')
            ->header(__p('core::phrase.posted_by'))
            ->linkTo('user.url')
            ->target('_blank')
            ->alignCenter()
            ->width(200);

        $this->addColumn('owner.display_name')
            ->header(__p('core::phrase.posted_to'))
            ->linkTo('owner.url')
            ->target('_blank')
            ->alignCenter()
            ->width(200);

        $this->addColumn('is_wiki')
            ->header(__p('forum::web.wiki'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('is_closed')
            ->header(__p('core::web.closed'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('is_approved')
            ->header(__p('core::phrase.approved'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('is_sponsored')
            ->header(__p('core::web.sponsored'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('statistic.total_comment')
            ->header(__p('forum::phrase.total_posts'))
            ->asNumber()
            ->sortable()
            ->sortableField(ThreadSortScope::SORT_DISCUSSED)
            ->width(100, 100);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField('recent')
            ->flex();

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getApproveActionMenu($actions);
            $this->getSponsoredActionMenu($actions);
            $this->getSponsoredInFeedActionMenu($actions);
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
            $this->getSponsoredActionMenu($menu);
            $this->getSponsoredInFeedActionMenu($menu);
            $this->getDeleteActionMenu($menu);
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'total_value_threads',
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
                    'message' => __p('core::phrase.delete_confirm'),
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
                    'message' => __p('core::phrase.delete_confirm'),
                ]);
        }
    }

    protected function getSponsoredActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('sponsor')
                ->asPatch()
                ->apiParams(['sponsor' => 1])
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/sponsor/:id");
            $actionMenu->add('unsponsor')
                ->asPatch()
                ->apiParams(['sponsor' => 0])
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/sponsor/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('sponsor')
                ->action('sponsor')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('forum::phrase.sponsor_this_item'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_sponsor'],
                    ['falsy', 'item.extra.can_unsponsor'],
                ]);

            $actionMenu->addItem('unsponsor')
                ->action('unsponsor')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('forum::phrase.unsponsor_this_item'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_unsponsor'],
                ]);
        }
    }

    protected function getSponsoredInFeedActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('sponsorInFeed')
                ->asPatch()
                ->apiParams(['sponsor' => 1])
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/sponsor-in-feed/:id");
            $actionMenu->add('unsponsorInFeed')
                ->asPatch()
                ->apiParams(['sponsor' => 0])
                ->apiUrl("admincp/{$this->appName}/{$this->resourceName}/sponsor-in-feed/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('sponsorInFeed')
                ->action('sponsorInFeed')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('forum::phrase.sponsor_in_feed'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_sponsor_in_feed'],
                    ['falsy', 'item.extra.can_unsponsor'],
                ]);

            $actionMenu->addItem('unsponsorInFeed')
                ->action('unsponsorInFeed')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('forum::phrase.unsponsor_in_feed'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_unsponsor_in_feed'],
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
