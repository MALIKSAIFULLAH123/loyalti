<?php

namespace MetaFox\Photo\Http\Resources\v1\Album\Admin;

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
    protected string $appName      = 'photo';
    protected string $resourceName = 'album';

    protected function initialize(): void
    {
        $this->enableCheckboxSelection();
        $this->dynamicRowHeight();
        $this->setRowsPerPage(20, [20, 50, 100, 200, 500]);

        $this->setDataSource("/admincp/{$this->appName}/{$this->resourceName}");

        $this->addColumn('name')
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

        $this->addColumn('is_sponsored')
            ->header(__p('core::web.sponsored'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('is_featured')
            ->header(__p('core::phrase.featured'))
            ->asYesNoIcon()
            ->width(100, 100);

        $this->addColumn('creation_date')
            ->header(__p('core::phrase.creation_date'))
            ->asDateTime()
            ->sortable()
            ->sortableField('recent')
            ->flex();

        /*
         * Add default actions
         */
        $this->withActions(function (Actions $actions) {
            $this->getFeaturedActionMenu($actions);
            $this->getSponsoredActionMenu($actions);
            $this->getDeleteActionMenu($actions);
            $this->getBatchDeleteActionMenu($actions);
        });

        /*
         * with batch menu actions
         */
        $this->withBatchMenu(function (BatchActionMenu $menu) {
            $this->getBatchDeleteActionMenu($menu);
        });

        /*
         * with item action menus
         */
        $this->withItemMenu(function (ItemActionMenu $menu) {
            $this->getFeaturedActionMenu($menu);
            $this->getSponsoredActionMenu($menu);
            $this->getDeleteActionMenu($menu);
        });

        $this->withExtraData([
            'show_total'        => true,
            'total_item_phrase' => 'total_value_photo_albums',
        ]);
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
                    'message' => __p('photo::phrase.delete_confirm_album'),
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
                ->apiUrl("{$this->appName}-{$this->resourceName}/:id");
        }

        if ($actionMenu instanceof ItemActionMenu) {
            $actionMenu->withDelete()
                ->params(['action' => 'deleteItem'])
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_delete'],
                ])
                ->confirm([
                    'title'   => __p('core::phrase.confirm'),
                    'message' => __p('photo::phrase.delete_confirm_album'),
                ]);
        }
    }

    protected function getFeaturedActionMenu(ItemActionMenu|Actions $actionMenu): void
    {
        if ($actionMenu instanceof Actions) {
            $actionMenu->add('feature')
                ->asPatch()
                ->apiParams(['feature' => 1])
                ->apiUrl("{$this->appName}-{$this->resourceName}/feature/:id");
            $actionMenu->add('unfeature')
                ->asPatch()
                ->apiParams(['feature' => 0])
                ->apiUrl("{$this->appName}-{$this->resourceName}/feature/:id");
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
                ->label(__p('photo::phrase.sponsor_this_item'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_sponsor'],
                    ['falsy', 'item.extra.can_unsponsor'],
                ]);

            $actionMenu->addItem('unsponsor')
                ->action('unsponsor')
                ->value(MetaFoxForm::ACTION_ADMINCP_BATCH_ITEM)
                ->label(__p('photo::phrase.unsponsor_this_item'))
                ->reload()
                ->showWhen([
                    'and',
                    ['truthy', 'item.extra.can_unsponsor'],
                ]);
        }
    }
}
