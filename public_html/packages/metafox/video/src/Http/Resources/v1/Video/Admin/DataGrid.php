<?php

namespace MetaFox\Video\Http\Resources\v1\Video\Admin;

/*
 | --------------------------------------------------------------------------
 | DataGrid Configuration
 | --------------------------------------------------------------------------
 | stub: src/Http/Resources/v1/Admin/DataGrid.stub
 */

use MetaFox\Form\Constants as MetaFoxForm;
use MetaFox\Platform\Resource\Actions;
use MetaFox\Platform\Resource\BatchActionMenu;
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
    protected string $appName      = 'video';
    protected string $resourceName = '';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource("/admincp/{$this->appName}");

        $this->addColumn('image')
            ->header(__p('photo::phrase.photo'))
            ->alignCenter()
            ->asPreviewUrl()
            ->width(100);

        $this->addColumn('title')
            ->header(__p('core::phrase.title'))
            ->linkTo('url')
            ->target('_blank')
            ->flex();

        $this->addColumn('description')
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

        $this->addColumn('status_text')
            ->header(__p('core::phrase.status'))
            ->alignCenter()
            ->asColoredText()
            ->width(200);

        $this->addColumn('is_sponsored')
            ->header(__p('core::web.sponsored'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('is_featured')
            ->header(__p('core::phrase.featured'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('is_valid')
            ->header(__p('video::phrase.valid'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('verified_at')
            ->header(__p('user::phrase.verified'))
            ->asDateTime()
            ->sortable()
            ->sortableField('verified_at')
            ->width(300);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField('recent')
            ->width(300);

        /**
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getApproveActionMenu($actions);
            $this->getFeaturedActionMenu($actions);
            $this->getSponsoredActionMenu($actions);
            $this->getSponsoredInFeedActionMenu($actions);
            $this->getDeleteActionMenu($actions);
            $this->getBatchDeleteActionMenu($actions);
            $this->getBatchApproveActionMenu($actions);
            $this->getVerifyExistenceActionMenu($actions);
            $this->getBatchVerifyExistenceActionMenu($actions);
        });

        /**
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchApproveActionMenu($menu);
            $this->getBatchVerifyExistenceActionMenu($menu);
            $this->getBatchDeleteActionMenu($menu);
        });

        /**
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getVerifyExistenceActionMenu($menu);
            $this->getApproveActionMenu($menu);
            $this->getFeaturedActionMenu($menu);
            $this->getSponsoredActionMenu($menu);
            $this->getSponsoredInFeedActionMenu($menu);
            $this->getDeleteActionMenu($menu);
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'total_value_videos',
        ]);

        $this->withGridMenu(function (GridActionMenu $menu) {
            $menu->addItem('manageProcessChecking')
                ->icon('ico-plus')
                ->label(__p('video::phrase.manage_verify_process'))
                ->disabled(false)
                ->to('video/verify-process/browse');
        });
    }

    protected function getBatchApproveActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchApprove')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl("admincp/{$this->appName}/batch-approve?id=[:id]");
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

    protected function getBatchVerifyExistenceActionMenu(BatchActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('batchVerifyExistence')
                ->asPatch()
                ->asFormDialog(false)
                ->apiUrl("admincp/{$this->appName}/batch-verify-existence?id=[:id]");
        }

        if ($actionMenu instanceof BatchActionMenu) {
            $actionMenu->addItem('batchVerifyExistence')
                ->action('batchVerifyExistence')
                ->icon('ico-check-circle-o')
                ->label(__p('video::phrase.verify_existence'))
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
                ->apiUrl("admincp/{$this->appName}/batch-delete?id=[:id]")
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('video::phrase.delete_confirm'),
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

    protected function getVerifyExistenceActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('verifyExistence')
                ->asPatch()
                ->apiUrl("admincp/{$this->appName}/verify-existence/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('verifyExistence')
                ->action('verifyExistence')
                ->label(__p('video::phrase.verify_existence'))
                ->reload()
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM);
        }
    }

    protected function getDeleteActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('deleteItem')
                ->asDelete()
                ->apiUrl("admincp/{$this->appName}/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->withDelete()
                ->params(['action' => 'deleteItem'])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('video::phrase.delete_confirm'),
                ]);
        }
    }

    protected function getFeaturedActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('feature')
                ->asPatch()
                ->apiParams(['feature' => 1])
                ->apiUrl("{$this->appName}/feature/:id");
            $actionMenu->add('unfeature')
                ->asPatch()
                ->apiParams(['feature' => 0])
                ->apiUrl("{$this->appName}/feature/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('feature')
                ->action('feature')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.feature'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_feature'],
                    ['falsy', 'item.extra.can_unfeature'],
                    ['truthy', 'item.is_success'],
                ]);

            $actionMenu->addItem('unfeature')
                ->action('unfeature')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('core::phrase.un_feature'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_unfeature'],
                ]);
        }
    }

    protected function getSponsoredActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('sponsor')
                ->asPatch()
                ->apiParams(['sponsor' => 1])
                ->apiUrl("admincp/{$this->appName}/sponsor/:id");
            $actionMenu->add('unsponsor')
                ->asPatch()
                ->apiParams(['sponsor' => 0])
                ->apiUrl("admincp/{$this->appName}/sponsor/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('sponsor')
                ->action('sponsor')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('video::phrase.sponsor_this_item'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_sponsor'],
                    ['falsy', 'item.extra.can_unsponsor'],
                    ['truthy', 'item.is_success'],
                ]);

            $actionMenu->addItem('unsponsor')
                ->action('unsponsor')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('video::phrase.unsponsor_this_item'))
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
                ->apiUrl("admincp/{$this->appName}/sponsor-in-feed/:id")
                ->apiParams(['sponsor' => 1])
                ->asPatch();

            $actionMenu->add('unsponsorInFeed')
                ->apiUrl("admincp/{$this->appName}/sponsor-in-feed/:id")
                ->apiParams(['sponsor' => 0])
                ->asPatch();
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->addItem('sponsorInFeed')
                ->action('sponsorInFeed')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('video::phrase.sponsor_in_feed'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_sponsor_in_feed'],
                    ['falsy', 'item.extra.can_unsponsor_in_feed'],
                    ['truthy', 'item.is_success'],
                ]);

            $actionMenu->addItem('unsponsorInFeed')
                ->action('unsponsorInFeed')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('video::phrase.un_sponsor_in_feed'))
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
                ->apiUrl("{$this->appName}/approve/:id");
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
                    ['truthy', 'item.is_success'],
                ]);
        }
    }
}
